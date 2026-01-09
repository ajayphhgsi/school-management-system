<?php
/**
 * Admin Expense Controller
 */

class ExpenseController extends Controller {

    public function __construct() {
        parent::__construct();
        $this->middleware('auth');
        $this->middleware('admin');
    }

    private function getCurrentAcademicYearId() {
        return $_SESSION['academic_year_id'] ?? null;
    }

    public function expenses() {
        $academicYearId = $this->getCurrentAcademicYearId();
        $where = "";
        $params = [];

        // Build WHERE clause with filters
        $conditions = [];
        if ($academicYearId) {
            $conditions[] = "academic_year_id = ?";
            $params[] = $academicYearId;
        }

        // Date range filter
        if (!empty($_GET['start_date']) && !empty($_GET['end_date'])) {
            $conditions[] = "expense_date BETWEEN ? AND ?";
            $params[] = $_GET['start_date'];
            $params[] = $_GET['end_date'];
        } elseif (!empty($_GET['start_date'])) {
            $conditions[] = "expense_date >= ?";
            $params[] = $_GET['start_date'];
        } elseif (!empty($_GET['end_date'])) {
            $conditions[] = "expense_date <= ?";
            $params[] = $_GET['end_date'];
        }

        // Month filter
        if (!empty($_GET['month'])) {
            $conditions[] = "DATE_FORMAT(expense_date, '%Y-%m') = ?";
            $params[] = $_GET['month'];
        }

        // Year filter
        if (!empty($_GET['year'])) {
            $conditions[] = "DATE_FORMAT(expense_date, '%Y') = ?";
            $params[] = $_GET['year'];
        }

        // Category filter
        if (!empty($_GET['category'])) {
            $conditions[] = "category = ?";
            $params[] = $_GET['category'];
        }

        // Search filter
        if (!empty($_GET['search'])) {
            $searchTerm = '%' . $_GET['search'] . '%';
            $conditions[] = "reason LIKE ?";
            $params[] = $searchTerm;
        }

        if (!empty($conditions)) {
            $where = "WHERE " . implode(" AND ", $conditions);
        }

        $expenses = $this->db->select("SELECT e.*, u.first_name || ' ' || u.last_name as recorded_by_name FROM expenses e LEFT JOIN users u ON e.recorded_by = u.id $where ORDER BY e.expense_date DESC", $params);

        // Calculate statistics with filters applied
        $statsConditions = [];
        $statsParams = [];
        if ($academicYearId) {
            $statsConditions[] = "academic_year_id = ?";
            $statsParams[] = $academicYearId;
        }

        // Apply date filters to statistics
        if (!empty($_GET['start_date']) && !empty($_GET['end_date'])) {
            $statsConditions[] = "expense_date BETWEEN ? AND ?";
            $statsParams[] = $_GET['start_date'];
            $statsParams[] = $_GET['end_date'];
        } elseif (!empty($_GET['start_date'])) {
            $statsConditions[] = "expense_date >= ?";
            $statsParams[] = $_GET['start_date'];
        } elseif (!empty($_GET['end_date'])) {
            $statsConditions[] = "expense_date <= ?";
            $statsParams[] = $_GET['end_date'];
        }

        if (!empty($_GET['month'])) {
            $statsConditions[] = "DATE_FORMAT(expense_date, '%Y-%m') = ?";
            $statsParams[] = $_GET['month'];
        }

        if (!empty($_GET['year'])) {
            $statsConditions[] = "DATE_FORMAT(expense_date, '%Y') = ?";
            $statsParams[] = $_GET['year'];
        }

        if (!empty($_GET['category'])) {
            $statsConditions[] = "category = ?";
            $statsParams[] = $_GET['category'];
        }

        $statsWhere = !empty($statsConditions) ? " WHERE " . implode(" AND ", $statsConditions) : "";

        $stats = [
            'monthly_total' => $this->db->selectOne("SELECT SUM(amount) as total FROM expenses" . $statsWhere . " AND DATE_FORMAT(expense_date, '%Y-%m') = DATE_FORMAT(NOW(), '%Y-%m')", $statsParams)['total'] ?? 0,
            'diesel_total' => $this->db->selectOne("SELECT SUM(amount) as total FROM expenses" . $statsWhere . " AND category = 'diesel'", $statsParams)['total'] ?? 0,
            'staff_total' => $this->db->selectOne("SELECT SUM(amount) as total FROM expenses" . $statsWhere . " AND category = 'staff'", $statsParams)['total'] ?? 0,
            'maintenance_total' => $this->db->selectOne("SELECT SUM(amount) as total FROM expenses" . $statsWhere . " AND category = 'maintenance'", $statsParams)['total'] ?? 0
        ];

        // Category totals for chart
        $category_totals = [];
        $categories = ['diesel', 'staff', 'bus', 'maintenance', 'misc', 'custom'];
        foreach ($categories as $category) {
            $category_totals[ucfirst($category)] = $this->db->selectOne("SELECT SUM(amount) as total FROM expenses" . $statsWhere . " AND category = ?", array_merge($statsParams, [$category]))['total'] ?? 0;
        }

        $this->render('admin/expenses/index', [
            'expenses' => $expenses,
            'stats' => $stats,
            'category_totals' => $category_totals,
            'total_pages' => 1, // Simplified pagination
            'current_page' => 1
        ]);
    }

    public function createExpense() {
        $csrfToken = $this->csrfToken();
        $this->render('admin/expenses/create', ['csrf_token' => $csrfToken]);
    }

    public function storeExpense() {
        $data = $_POST;
        $csrfToken = $data['csrf_token'] ?? '';

        if (!$this->checkCsrfToken($csrfToken)) {
            $this->session->setFlash('error', 'Invalid CSRF token');
            $this->redirect('/admin/expenses/create');
        }

        // Validate required fields
        $required = ['expense_date', 'category', 'amount', 'reason'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                $this->session->setFlash('error', ucfirst(str_replace('_', ' ', $field)) . ' is required');
                $this->session->setFlash('old', $data);
                $this->redirect('/admin/expenses/create');
            }
        }

        // Start transaction
        $this->db->beginTransaction();

        try {
            $academicYearId = $this->getCurrentAcademicYearId();

            // Create expense record
            $expenseData = [
                'expense_date' => $data['expense_date'],
                'category' => $data['category'],
                'amount' => $data['amount'],
                'reason' => $data['reason'],
                'payment_mode' => $data['payment_mode'] ?? 'cash',
                'recorded_by' => $_SESSION['user']['id'] ?? 1
            ];
            if ($academicYearId) {
                $expenseData['academic_year_id'] = $academicYearId;
            }

            $expenseId = $this->db->insert('expenses', $expenseData);

            $this->db->commit();

            $this->session->setFlash('success', 'Expense recorded successfully');
            $this->redirect('/admin/expenses');

        } catch (Exception $e) {
            $this->db->rollback();
            $this->session->setFlash('error', 'Failed to record expense: ' . $e->getMessage());
            $this->redirect('/admin/expenses/create');
        }
    }

    public function exportExpenses() {
        $academicYearId = $this->getCurrentAcademicYearId();
        $where = "";
        $params = [];

        // Build WHERE clause with same filters as expenses() method
        $conditions = [];
        if ($academicYearId) {
            $conditions[] = "academic_year_id = ?";
            $params[] = $academicYearId;
        }

        // Date range filter
        if (!empty($_GET['start_date']) && !empty($_GET['end_date'])) {
            $conditions[] = "expense_date BETWEEN ? AND ?";
            $params[] = $_GET['start_date'];
            $params[] = $_GET['end_date'];
        } elseif (!empty($_GET['start_date'])) {
            $conditions[] = "expense_date >= ?";
            $params[] = $_GET['start_date'];
        } elseif (!empty($_GET['end_date'])) {
            $conditions[] = "expense_date <= ?";
            $params[] = $_GET['end_date'];
        }

        // Month filter
        if (!empty($_GET['month'])) {
            $conditions[] = "DATE_FORMAT(expense_date, '%Y-%m') = ?";
            $params[] = $_GET['month'];
        }

        // Year filter
        if (!empty($_GET['year'])) {
            $conditions[] = "DATE_FORMAT(expense_date, '%Y') = ?";
            $params[] = $_GET['year'];
        }

        // Category filter
        if (!empty($_GET['category'])) {
            $conditions[] = "category = ?";
            $params[] = $_GET['category'];
        }

        // Search filter
        if (!empty($_GET['search'])) {
            $searchTerm = '%' . $_GET['search'] . '%';
            $conditions[] = "reason LIKE ?";
            $params[] = $searchTerm;
        }

        if (!empty($conditions)) {
            $where = "WHERE " . implode(" AND ", $conditions);
        }

        $expenses = $this->db->select("SELECT e.*, u.first_name || ' ' || u.last_name as recorded_by_name FROM expenses e LEFT JOIN users u ON e.recorded_by = u.id $where ORDER BY e.expense_date DESC", $params);

        // Generate CSV
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="expenses_' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['Date', 'Reason', 'Category', 'Amount', 'Payment Mode', 'Recorded By']);

        foreach ($expenses as $expense) {
            fputcsv($output, [
                date('M d, Y', strtotime($expense['expense_date'])),
                $expense['reason'],
                ucfirst($expense['category']),
                number_format($expense['amount'], 2),
                ucfirst($expense['payment_mode']),
                $expense['recorded_by_name'] ?? 'System'
            ]);
        }

        fclose($output);
        exit;
    }
}