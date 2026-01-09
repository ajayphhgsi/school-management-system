<?php
/**
 * Admin Fee Controller
 */

class FeeController extends Controller {

    public function __construct() {
        parent::__construct();
        $this->middleware('auth');
        $this->middleware('admin');
    }

    private function getCurrentAcademicYearId() {
        return $_SESSION['academic_year_id'] ?? null;
    }

    public function fees() {
        $academicYearId = $this->getCurrentAcademicYearId();
        $where = "";
        $params = [];

        // Build WHERE clause with filters
        $conditions = [];
        if ($academicYearId) {
            $conditions[] = "f.academic_year_id = ?";
            $params[] = $academicYearId;
        }

        // Date range filter
        if (!empty($_GET['start_date']) && !empty($_GET['end_date'])) {
            $conditions[] = "f.due_date BETWEEN ? AND ?";
            $params[] = $_GET['start_date'];
            $params[] = $_GET['end_date'];
        } elseif (!empty($_GET['start_date'])) {
            $conditions[] = "f.due_date >= ?";
            $params[] = $_GET['start_date'];
        } elseif (!empty($_GET['end_date'])) {
            $conditions[] = "f.due_date <= ?";
            $params[] = $_GET['end_date'];
        }

        // Month filter
        if (!empty($_GET['month'])) {
            $conditions[] = "DATE_FORMAT(f.due_date, '%Y-%m') = ?";
            $params[] = $_GET['month'];
        }

        // Year filter
        if (!empty($_GET['year'])) {
            $conditions[] = "DATE_FORMAT(f.due_date, '%Y') = ?";
            $params[] = $_GET['year'];
        }

        // Search filter
        if (!empty($_GET['search'])) {
            $searchTerm = '%' . $_GET['search'] . '%';
            $conditions[] = "(s.first_name LIKE ? OR s.last_name LIKE ? OR s.scholar_number LIKE ?)";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        if (!empty($conditions)) {
            $where = "WHERE " . implode(" AND ", $conditions);
        }

        $fees = $this->db->select("SELECT f.*, s.first_name, s.last_name, s.scholar_number FROM fees f LEFT JOIN students s ON f.student_id = s.id $where ORDER BY f.due_date DESC", $params);

        // Calculate statistics (with filters applied)
        $statsConditions = [];
        $statsParams = [];
        if ($academicYearId) {
            $statsConditions[] = "f.academic_year_id = ?";
            $statsParams[] = $academicYearId;
        }

        // Apply date filters to statistics as well
        if (!empty($_GET['start_date']) && !empty($_GET['end_date'])) {
            $statsConditions[] = "f.due_date BETWEEN ? AND ?";
            $statsParams[] = $_GET['start_date'];
            $statsParams[] = $_GET['end_date'];
        } elseif (!empty($_GET['start_date'])) {
            $statsConditions[] = "f.due_date >= ?";
            $statsParams[] = $_GET['start_date'];
        } elseif (!empty($_GET['end_date'])) {
            $statsConditions[] = "f.due_date <= ?";
            $statsParams[] = $_GET['end_date'];
        }

        if (!empty($_GET['month'])) {
            $statsConditions[] = "DATE_FORMAT(f.due_date, '%Y-%m') = ?";
            $statsParams[] = $_GET['month'];
        }

        if (!empty($_GET['year'])) {
            $statsConditions[] = "DATE_FORMAT(f.due_date, '%Y') = ?";
            $statsParams[] = $_GET['year'];
        }

        $statsWhere = !empty($statsConditions) ? " AND " . implode(" AND ", $statsConditions) : "";

        $stats = [
            'total_collected' => $this->db->selectOne("SELECT SUM(fp.amount_paid) as total FROM fee_payments fp LEFT JOIN fees f ON fp.fee_id = f.id WHERE DATE_FORMAT(fp.payment_date, '%Y-%m') = DATE_FORMAT(NOW(), '%Y-%m')" . $statsWhere, $statsParams)['total'] ?? 0,
            'total_pending' => $this->db->selectOne("SELECT SUM(amount) as total FROM fees f WHERE is_paid = 0" . $statsWhere, $statsParams)['total'] ?? 0,
            'monthly_target' => 10000, // This could be configurable
            'overdue_amount' => $this->db->selectOne("SELECT SUM(amount) as total FROM fees f WHERE is_paid = 0 AND due_date < CURDATE()" . $statsWhere, $statsParams)['total'] ?? 0
        ];

        $this->render('admin/fees/index', ['fees' => $fees, 'stats' => $stats]);
    }

    public function createFee() {
        $academicYearId = $this->getCurrentAcademicYearId();
        $where = "WHERE is_active = 1";
        $params = [];
        if ($academicYearId) {
            $where .= " AND academic_year_id = ?";
            $params = [$academicYearId];
        }
        $classes = $this->db->select("SELECT * FROM classes $where ORDER BY class_name", $params);
        $csrfToken = $this->csrfToken();
        $this->render('admin/fees/create', ['classes' => $classes, 'csrf_token' => $csrfToken]);
    }

    public function getStudentsForFees() {
        $classId = $_GET['class_id'] ?? '';
        $village = $_GET['village'] ?? '';

        if (!$classId) {
            $this->json(['error' => 'Class ID is required'], 400);
        }

        $academicYearId = $this->getCurrentAcademicYearId();

        $query = "SELECT s.*, c.class_name, c.section FROM students s LEFT JOIN classes c ON s.class_id = c.id WHERE s.class_id = ? AND s.is_active = 1";
        $params = [$classId];

        if ($academicYearId) {
            $query .= " AND c.academic_year_id = ?";
            $params[] = $academicYearId;
        }

        if (!empty($village)) {
            $query .= " AND s.village LIKE ?";
            $params[] = '%' . $village . '%';
        }

        $query .= " ORDER BY s.first_name, s.last_name";

        $students = $this->db->select($query, $params);
        $this->json(['students' => $students]);
    }

    public function storeFee() {
        $data = $_POST;
        $csrfToken = $data['csrf_token'] ?? '';

        if (!$this->checkCsrfToken($csrfToken)) {
            $this->session->setFlash('error', 'Invalid CSRF token');
            $this->redirect('/admin/fees/create');
        }

        // Validate required fields
        $required = ['student_id', 'fee_type', 'total_fee', 'net_amount', 'receipt_number', 'payment_mode', 'payment_date'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                $this->session->setFlash('error', ucfirst(str_replace('_', ' ', $field)) . ' is required');
                $this->session->setFlash('old', $data);
                $this->redirect('/admin/fees/create');
            }
        }

        // Validate student exists
        $student = $this->db->selectOne("SELECT * FROM students WHERE id = ?", [$data['student_id']]);
        if (!$student) {
            $this->session->setFlash('error', 'Selected student not found');
            $this->redirect('/admin/fees/create');
        }

        // Start transaction
        $this->db->beginTransaction();

        try {
            $academicYearId = $this->getCurrentAcademicYearId();

            // Create fee record
            $feeData = [
                'student_id' => $data['student_id'],
                'fee_type' => $data['fee_type'],
                'amount' => $data['total_fee'],
                'due_date' => date('Y-m-d', strtotime('+30 days')), // Default 30 days
                'academic_year' => date('Y'), // Current year
                'is_paid' => 1
            ];
            if ($academicYearId) {
                $feeData['academic_year_id'] = $academicYearId;
            }

            $feeId = $this->db->insert('fees', $feeData);

            // Create payment record
            $paymentData = [
                'fee_id' => $feeId,
                'amount_paid' => $data['net_amount'],
                'payment_date' => $data['payment_date'],
                'payment_mode' => $data['payment_mode'],
                'transaction_id' => $data['transaction_id'] ?? null,
                'cheque_number' => ($data['payment_mode'] === 'cheque') ? $data['transaction_id'] : null,
                'remarks' => $data['remarks'] ?? '',
                'collected_by' => $_SESSION['user']['id'] ?? 1
            ];

            $this->db->insert('fee_payments', $paymentData);

            $this->db->commit();

            // Generate receipt (this would be implemented with PDF generation)
            $this->session->setFlash('success', 'Fee payment recorded successfully. Receipt generated.');

            // Redirect to receipt view or back to fees
            $this->redirect('/admin/fees');

        } catch (Exception $e) {
            $this->db->rollback();
            $this->session->setFlash('error', 'Failed to record fee payment: ' . $e->getMessage());
            $this->redirect('/admin/fees/create');
        }
    }

    public function exportFees() {
        $academicYearId = $this->getCurrentAcademicYearId();
        $where = "";
        $params = [];

        // Build WHERE clause with same filters as fees() method
        $conditions = [];
        if ($academicYearId) {
            $conditions[] = "f.academic_year_id = ?";
            $params[] = $academicYearId;
        }

        // Date range filter
        if (!empty($_GET['start_date']) && !empty($_GET['end_date'])) {
            $conditions[] = "f.due_date BETWEEN ? AND ?";
            $params[] = $_GET['start_date'];
            $params[] = $_GET['end_date'];
        } elseif (!empty($_GET['start_date'])) {
            $conditions[] = "f.due_date >= ?";
            $params[] = $_GET['start_date'];
        } elseif (!empty($_GET['end_date'])) {
            $conditions[] = "f.due_date <= ?";
            $params[] = $_GET['end_date'];
        }

        // Month filter
        if (!empty($_GET['month'])) {
            $conditions[] = "DATE_FORMAT(f.due_date, '%Y-%m') = ?";
            $params[] = $_GET['month'];
        }

        // Year filter
        if (!empty($_GET['year'])) {
            $conditions[] = "DATE_FORMAT(f.due_date, '%Y') = ?";
            $params[] = $_GET['year'];
        }

        // Search filter
        if (!empty($_GET['search'])) {
            $searchTerm = '%' . $_GET['search'] . '%';
            $conditions[] = "(s.first_name LIKE ? OR s.last_name LIKE ? OR s.scholar_number LIKE ?)";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        if (!empty($conditions)) {
            $where = "WHERE " . implode(" AND ", $conditions);
        }

        $fees = $this->db->select("SELECT f.*, s.first_name, s.last_name, s.scholar_number FROM fees f LEFT JOIN students s ON f.student_id = s.id $where ORDER BY f.due_date DESC", $params);

        // Generate CSV
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="fees_' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['Date', 'Student Name', 'Scholar Number', 'Fee Type', 'Amount', 'Status', 'Due Date']);

        foreach ($fees as $fee) {
            fputcsv($output, [
                date('M d, Y', strtotime($fee['created_at'])),
                $fee['first_name'] . ' ' . $fee['last_name'],
                $fee['scholar_number'],
                $fee['fee_type'] ?? 'Tuition',
                number_format($fee['amount'], 2),
                $fee['is_paid'] ? 'Paid' : 'Pending',
                date('M d, Y', strtotime($fee['due_date']))
            ]);
        }

        fclose($output);
        exit;
    }

    public function bulkAssignFees() {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!$data || !isset($data['fee_type']) || !isset($data['amount']) || !isset($data['assignment_type'])) {
            $this->json(['success' => false, 'message' => 'Invalid data'], 400);
        }

        $feeType = $data['fee_type'];
        $amount = $data['amount'];
        $assignmentType = $data['assignment_type'];
        $dueDate = $data['due_date'] ?? date('Y-m-d', strtotime('+30 days'));
        $academicYear = $data['academic_year'] ?? date('Y');

        $academicYearId = $this->getCurrentAcademicYearId();

        $this->db->beginTransaction();

        try {
            $assignedCount = 0;

            if ($assignmentType === 'class' && isset($data['class_id'])) {
                // Assign to entire class
                $classId = $data['class_id'];
                $students = $this->db->select("SELECT id FROM students WHERE class_id = ? AND is_active = 1", [$classId]);

                foreach ($students as $student) {
                    $feeData = [
                        'student_id' => $student['id'],
                        'fee_type' => $feeType,
                        'amount' => $amount,
                        'due_date' => $dueDate,
                        'academic_year' => $academicYear,
                        'is_paid' => 0
                    ];
                    if ($academicYearId) {
                        $feeData['academic_year_id'] = $academicYearId;
                    }

                    $this->db->insert('fees', $feeData);
                    $assignedCount++;
                }

            } elseif ($assignmentType === 'selected' && isset($data['student_ids'])) {
                // Assign to selected students
                $studentIds = $data['student_ids'];

                foreach ($studentIds as $studentId) {
                    $feeData = [
                        'student_id' => $studentId,
                        'fee_type' => $feeType,
                        'amount' => $amount,
                        'due_date' => $dueDate,
                        'academic_year' => $academicYear,
                        'is_paid' => 0
                    ];
                    if ($academicYearId) {
                        $feeData['academic_year_id'] = $academicYearId;
                    }

                    $this->db->insert('fees', $feeData);
                    $assignedCount++;
                }
            } else {
                $this->json(['success' => false, 'message' => 'Invalid assignment type or missing required data'], 400);
            }

            $this->db->commit();
            $this->json(['success' => true, 'message' => "Successfully assigned fees to $assignedCount students"]);

        } catch (Exception $e) {
            $this->db->rollback();
            $this->json(['success' => false, 'message' => 'Failed to assign fees: ' . $e->getMessage()], 500);
        }
    }

    public function initiatePayment() {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!$data || !isset($data['fee_id']) || !isset($data['amount'])) {
            $this->json(['success' => false, 'message' => 'Invalid data'], 400);
        }

        $feeId = $data['fee_id'];
        $amount = $data['amount'];

        // Validate fee exists and is unpaid
        $fee = $this->db->selectOne("SELECT * FROM fees WHERE id = ? AND is_paid = 0", [$feeId]);
        if (!$fee) {
            $this->json(['success' => false, 'message' => 'Fee not found or already paid'], 404);
        }

        // Load payment config
        $paymentConfig = require CONFIG_PATH . 'payment.php';
        if (!$paymentConfig['enabled']) {
            $this->json(['success' => false, 'message' => 'Payment processing is disabled'], 400);
        }

        $gateway = $paymentConfig['default_gateway'];

        try {
            if ($gateway === 'razorpay') {
                // Check if Razorpay SDK is available
                if (!class_exists('Razorpay\Api\Api')) {
                    throw new Exception('Razorpay SDK not installed');
                }

                $api = new \Razorpay\Api\Api($paymentConfig['razorpay']['key_id'], $paymentConfig['razorpay']['key_secret']);

                $orderData = [
                    'receipt' => 'fee_' . $feeId,
                    'amount' => $amount * 100, // Amount in paisa
                    'currency' => 'INR',
                    'payment_capture' => 1
                ];

                $razorpayOrder = $api->order->create($orderData);

                // Insert payment record
                $paymentId = $this->db->insert('fee_payments', [
                    'fee_id' => $feeId,
                    'amount_paid' => $amount,
                    'payment_date' => date('Y-m-d'),
                    'payment_mode' => 'online',
                    'payment_gateway' => 'razorpay',
                    'payment_status' => 'pending',
                    'transaction_id' => $razorpayOrder->id,
                    'collected_by' => $_SESSION['user']['id'] ?? 1
                ]);

                $this->json([
                    'success' => true,
                    'order_id' => $razorpayOrder->id,
                    'amount' => $amount,
                    'currency' => 'INR',
                    'key' => $paymentConfig['razorpay']['key_id']
                ]);

            } elseif ($gateway === 'stripe') {
                // Check if Stripe SDK is available
                if (!class_exists('Stripe\Stripe')) {
                    throw new Exception('Stripe SDK not installed');
                }

                \Stripe\Stripe::setApiKey($paymentConfig['stripe']['secret_key']);

                $intent = \Stripe\PaymentIntent::create([
                    'amount' => $amount * 100, // Amount in cents
                    'currency' => 'usd',
                    'metadata' => ['fee_id' => $feeId]
                ]);

                // Insert payment record
                $paymentId = $this->db->insert('fee_payments', [
                    'fee_id' => $feeId,
                    'amount_paid' => $amount,
                    'payment_date' => date('Y-m-d'),
                    'payment_mode' => 'online',
                    'payment_gateway' => 'stripe',
                    'payment_status' => 'pending',
                    'transaction_id' => $intent->id,
                    'collected_by' => $_SESSION['user']['id'] ?? 1
                ]);

                $this->json([
                    'success' => true,
                    'client_secret' => $intent->client_secret,
                    'amount' => $amount,
                    'currency' => 'usd'
                ]);

            } else {
                $this->json(['success' => false, 'message' => 'Unsupported payment gateway'], 400);
            }

        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => 'Payment initiation failed: ' . $e->getMessage()], 500);
        }
    }

    public function processPayment() {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!$data || !isset($data['payment_id']) || !isset($data['status'])) {
            $this->json(['success' => false, 'message' => 'Invalid data'], 400);
        }

        $paymentId = $data['payment_id'];
        $status = $data['status'];
        $transactionId = $data['transaction_id'] ?? null;

        // Get payment record
        $payment = $this->db->selectOne("SELECT * FROM fee_payments WHERE id = ?", [$paymentId]);
        if (!$payment) {
            $this->json(['success' => false, 'message' => 'Payment record not found'], 404);
        }

        // Load payment config
        $paymentConfig = require CONFIG_PATH . 'payment.php';

        try {
            if ($status === 'completed') {
                // Update payment status
                $this->db->update('fee_payments', [
                    'payment_status' => 'completed',
                    'transaction_id' => $transactionId ?: $payment['transaction_id']
                ], 'id = ?', [$paymentId]);

                // Mark fee as paid
                $this->db->update('fees', ['is_paid' => 1], 'id = ?', [$payment['fee_id']]);

                // Log the transaction
                $this->db->insert('audit_logs', [
                    'user_id' => $_SESSION['user']['id'] ?? 1,
                    'action' => 'payment_completed',
                    'table_name' => 'fee_payments',
                    'record_id' => $paymentId,
                    'new_values' => json_encode(['status' => 'completed'])
                ]);

                $this->json(['success' => true, 'message' => 'Payment processed successfully']);

            } elseif ($status === 'failed') {
                // Update payment status
                $this->db->update('fee_payments', [
                    'payment_status' => 'failed'
                ], 'id = ?', [$paymentId]);

                $this->json(['success' => true, 'message' => 'Payment marked as failed']);

            } else {
                $this->json(['success' => false, 'message' => 'Invalid payment status'], 400);
            }

        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => 'Payment processing failed: ' . $e->getMessage()], 500);
        }
    }

    public function refundPayment() {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!$data || !isset($data['payment_id']) || !isset($data['refund_amount'])) {
            $this->json(['success' => false, 'message' => 'Invalid data'], 400);
        }

        $paymentId = $data['payment_id'];
        $refundAmount = $data['refund_amount'];
        $reason = $data['reason'] ?? 'Customer request';

        // Get payment record
        $payment = $this->db->selectOne("SELECT * FROM fee_payments WHERE id = ? AND payment_status = 'completed'", [$paymentId]);
        if (!$payment) {
            $this->json(['success' => false, 'message' => 'Payment not found or not eligible for refund'], 404);
        }

        if ($refundAmount > $payment['amount_paid']) {
            $this->json(['success' => false, 'message' => 'Refund amount cannot exceed payment amount'], 400);
        }

        // Load payment config
        $paymentConfig = require CONFIG_PATH . 'payment.php';

        try {
            if ($payment['payment_gateway'] === 'razorpay') {
                // Check if Razorpay SDK is available
                if (!class_exists('Razorpay\Api\Api')) {
                    throw new Exception('Razorpay SDK not installed');
                }

                $api = new \Razorpay\Api\Api($paymentConfig['razorpay']['key_id'], $paymentConfig['razorpay']['key_secret']);

                $refund = $api->payment->fetch($payment['transaction_id'])->refund([
                    'amount' => $refundAmount * 100,
                    'notes' => ['reason' => $reason]
                ]);

                // Update payment record
                $this->db->update('fee_payments', [
                    'payment_status' => 'refunded',
                    'refund_amount' => $refundAmount
                ], 'id = ?', [$paymentId]);

                // Mark fee as unpaid if full refund
                if ($refundAmount >= $payment['amount_paid']) {
                    $this->db->update('fees', ['is_paid' => 0], 'id = ?', [$payment['fee_id']]);
                }

                // Log the refund
                $this->db->insert('audit_logs', [
                    'user_id' => $_SESSION['user']['id'] ?? 1,
                    'action' => 'payment_refunded',
                    'table_name' => 'fee_payments',
                    'record_id' => $paymentId,
                    'old_values' => json_encode(['status' => 'completed']),
                    'new_values' => json_encode(['status' => 'refunded', 'refund_amount' => $refundAmount])
                ]);

                $this->json(['success' => true, 'message' => 'Refund processed successfully', 'refund_id' => $refund->id]);

            } elseif ($payment['payment_gateway'] === 'stripe') {
                // Check if Stripe SDK is available
                if (!class_exists('Stripe\Stripe')) {
                    throw new Exception('Stripe SDK not installed');
                }

                \Stripe\Stripe::setApiKey($paymentConfig['stripe']['secret_key']);

                $refund = \Stripe\Refund::create([
                    'payment_intent' => $payment['transaction_id'],
                    'amount' => $refundAmount * 100,
                    'reason' => 'requested_by_customer'
                ]);

                // Update payment record
                $this->db->update('fee_payments', [
                    'payment_status' => 'refunded',
                    'refund_amount' => $refundAmount
                ], 'id = ?', [$paymentId]);

                // Mark fee as unpaid if full refund
                if ($refundAmount >= $payment['amount_paid']) {
                    $this->db->update('fees', ['is_paid' => 0], 'id = ?', [$payment['fee_id']]);
                }

                // Log the refund
                $this->db->insert('audit_logs', [
                    'user_id' => $_SESSION['user']['id'] ?? 1,
                    'action' => 'payment_refunded',
                    'table_name' => 'fee_payments',
                    'record_id' => $paymentId,
                    'old_values' => json_encode(['status' => 'completed']),
                    'new_values' => json_encode(['status' => 'refunded', 'refund_amount' => $refundAmount])
                ]);

                $this->json(['success' => true, 'message' => 'Refund processed successfully', 'refund_id' => $refund->id]);

            } else {
                $this->json(['success' => false, 'message' => 'Refund not supported for this gateway'], 400);
            }

        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => 'Refund processing failed: ' . $e->getMessage()], 500);
        }
    }

    public function getPaymentStatus() {
        $paymentId = $_GET['payment_id'] ?? '';

        if (!$paymentId) {
            $this->json(['success' => false, 'message' => 'Payment ID is required'], 400);
        }

        // Get payment record
        $payment = $this->db->selectOne("SELECT fp.*, f.fee_type, s.first_name, s.last_name, s.scholar_number FROM fee_payments fp LEFT JOIN fees f ON fp.fee_id = f.id LEFT JOIN students s ON f.student_id = s.id WHERE fp.id = ?", [$paymentId]);
        if (!$payment) {
            $this->json(['success' => false, 'message' => 'Payment not found'], 404);
        }

        // Load payment config
        $paymentConfig = require CONFIG_PATH . 'payment.php';

        try {
            $gatewayStatus = null;

            if ($payment['payment_gateway'] === 'razorpay' && $payment['transaction_id']) {
                // Check if Razorpay SDK is available
                if (class_exists('Razorpay\Api\Api')) {
                    $api = new \Razorpay\Api\Api($paymentConfig['razorpay']['key_id'], $paymentConfig['razorpay']['key_secret']);
                    $razorpayPayment = $api->payment->fetch($payment['transaction_id']);
                    $gatewayStatus = $razorpayPayment->status;
                }
            } elseif ($payment['payment_gateway'] === 'stripe' && $payment['transaction_id']) {
                // Check if Stripe SDK is available
                if (class_exists('Stripe\Stripe')) {
                    \Stripe\Stripe::setApiKey($paymentConfig['stripe']['secret_key']);
                    $intent = \Stripe\PaymentIntent::retrieve($payment['transaction_id']);
                    $gatewayStatus = $intent->status;
                }
            }

            $this->json([
                'success' => true,
                'payment' => [
                    'id' => $payment['id'],
                    'amount' => $payment['amount_paid'],
                    'status' => $payment['payment_status'],
                    'gateway' => $payment['payment_gateway'],
                    'transaction_id' => $payment['transaction_id'],
                    'gateway_status' => $gatewayStatus,
                    'student' => $payment['first_name'] . ' ' . $payment['last_name'] . ' (' . $payment['scholar_number'] . ')',
                    'fee_type' => $payment['fee_type'],
                    'payment_date' => $payment['payment_date']
                ]
            ]);

        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => 'Failed to retrieve payment status: ' . $e->getMessage()], 500);
        }
    }
}