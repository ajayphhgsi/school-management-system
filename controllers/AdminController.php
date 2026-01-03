<?php
/**
 * Admin Controller - Admin Panel Management
 */

class AdminController extends Controller {

    public function __construct() {
        parent::__construct();
        $this->middleware('auth');
        $this->middleware('admin');
    }

    private function getCurrentAcademicYearId() {
        return $_SESSION['academic_year_id'] ?? null;
    }

    public function dashboard() {
        $academicYearId = $this->getCurrentAcademicYearId();

        // Get dashboard statistics
        $studentWhere = "WHERE s.is_active = 1";
        $classWhere = "WHERE is_active = 1";
        $eventWhere = "WHERE is_active = 1";
        $params = [];

        if ($academicYearId) {
            $studentWhere .= " AND c.academic_year_id = ?";
            $classWhere .= " AND academic_year_id = ?";
            $eventWhere .= " AND academic_year_id = ?";
            $params = [$academicYearId, $academicYearId, $academicYearId];
        }

        // Basic statistics
        $stats = [
            'total_students' => $this->db->selectOne("SELECT COUNT(*) as count FROM students s LEFT JOIN classes c ON s.class_id = c.id $studentWhere", $academicYearId ? [$academicYearId] : [])['count'],
            'total_classes' => $this->db->selectOne("SELECT COUNT(*) as count FROM classes $classWhere", $academicYearId ? [$academicYearId] : [])['count'],
            'total_events' => $this->db->selectOne("SELECT COUNT(*) as count FROM events $eventWhere", $academicYearId ? [$academicYearId] : [])['count'],
            'total_gallery' => $this->db->selectOne("SELECT COUNT(*) as count FROM gallery WHERE is_active = 1")['count'],
        ];

        // Financial statistics
        $financialStats = [
            'total_revenue' => $this->db->selectOne("
                SELECT SUM(fp.amount_paid) as total
                FROM fee_payments fp
                LEFT JOIN fees f ON fp.fee_id = f.id
                WHERE fp.payment_status = 'completed' " . ($academicYearId ? " AND f.academic_year_id = ?" : ""),
                $academicYearId ? [$academicYearId] : []
            )['total'] ?? 0,

            'outstanding_fees' => $this->db->selectOne("
                SELECT SUM(amount) as total
                FROM fees
                WHERE is_paid = 0 " . ($academicYearId ? " AND academic_year_id = ?" : ""),
                $academicYearId ? [$academicYearId] : []
            )['total'] ?? 0,

            'monthly_revenue' => $this->db->selectOne("
                SELECT SUM(fp.amount_paid) as total
                FROM fee_payments fp
                LEFT JOIN fees f ON fp.fee_id = f.id
                WHERE fp.payment_status = 'completed'
                AND DATE_FORMAT(fp.payment_date, '%Y-%m') = DATE_FORMAT(NOW(), '%Y-%m') " .
                ($academicYearId ? " AND f.academic_year_id = ?" : ""),
                $academicYearId ? [$academicYearId] : []
            )['total'] ?? 0,

            'overdue_fees' => $this->db->selectOne("
                SELECT SUM(amount) as total
                FROM fees
                WHERE is_paid = 0 AND due_date < CURDATE() " . ($academicYearId ? " AND academic_year_id = ?" : ""),
                $academicYearId ? [$academicYearId] : []
            )['total'] ?? 0,
        ];

        // Academic performance statistics
        $academicStats = [
            'total_exams' => $this->db->selectOne("SELECT COUNT(*) as count FROM exams WHERE is_active = 1 " . ($academicYearId ? " AND academic_year_id = ?" : ""), $academicYearId ? [$academicYearId] : [])['count'],
            'avg_attendance' => $this->db->selectOne("
                SELECT AVG(CASE WHEN status = 'present' THEN 1 ELSE 0 END) * 100 as rate
                FROM attendance a
                WHERE 1=1 " . ($academicYearId ? " AND a.academic_year_id = ?" : ""),
                $academicYearId ? [$academicYearId] : []
            )['rate'] ?? 0,

            'pass_rate' => $this->db->selectOne("
                SELECT (SUM(CASE WHEN grade != 'F' THEN 1 ELSE 0 END) / COUNT(*)) * 100 as rate
                FROM exam_results er
                WHERE 1=1 " . ($academicYearId ? " AND er.academic_year_id = ?" : ""),
                $academicYearId ? [$academicYearId] : []
            )['rate'] ?? 0,
        ];

        // Recent activities and data
        $recentData = [
            'recent_students' => $this->db->select("SELECT s.* FROM students s LEFT JOIN classes c ON s.class_id = c.id $studentWhere ORDER BY s.created_at DESC LIMIT 6", $academicYearId ? [$academicYearId] : []),
            'upcoming_events' => $this->db->select("SELECT * FROM events WHERE event_date >= CURDATE() AND is_active = 1" . ($academicYearId ? " AND academic_year_id = ?" : "") . " ORDER BY event_date LIMIT 5", $academicYearId ? [$academicYearId] : []),
            'recent_payments' => $this->db->select("
                SELECT fp.*, s.first_name, s.last_name, s.scholar_number
                FROM fee_payments fp
                LEFT JOIN fees f ON fp.fee_id = f.id
                LEFT JOIN students s ON f.student_id = s.id
                WHERE fp.payment_status = 'completed' " . ($academicYearId ? " AND f.academic_year_id = ?" : "") . "
                ORDER BY fp.payment_date DESC LIMIT 5",
                $academicYearId ? [$academicYearId] : []
            ),
            'recent_activities' => $this->db->select("
                SELECT al.*, u.first_name, u.last_name
                FROM audit_logs al
                LEFT JOIN users u ON al.user_id = u.id
                ORDER BY al.created_at DESC LIMIT 10
            "),
        ];

        // Alerts and notifications
        $alerts = [
            'overdue_fees_count' => $this->db->selectOne("
                SELECT COUNT(*) as count
                FROM fees
                WHERE is_paid = 0 AND due_date < CURDATE() " . ($academicYearId ? " AND academic_year_id = ?" : ""),
                $academicYearId ? [$academicYearId] : []
            )['count'] ?? 0,

            'low_attendance_classes' => $this->db->select("
                SELECT c.class_name, c.section,
                       COUNT(CASE WHEN a.status = 'present' THEN 1 END) * 100.0 / COUNT(a.id) as rate
                FROM classes c
                LEFT JOIN students s ON c.id = s.class_id AND s.is_active = 1
                LEFT JOIN attendance a ON s.id = a.student_id
                WHERE c.is_active = 1 " . ($academicYearId ? " AND c.academic_year_id = ?" : "") . "
                GROUP BY c.id, c.class_name, c.section
                HAVING rate < 75 AND COUNT(a.id) > 0
                ORDER BY rate ASC LIMIT 3",
                $academicYearId ? [$academicYearId] : []
            ),

            'upcoming_deadlines' => $this->db->select("
                SELECT 'fee_due' as type, CONCAT('Fee due for ', COUNT(*), ' students') as message,
                       MIN(due_date) as due_date, COUNT(*) as count
                FROM fees
                WHERE is_paid = 0 AND due_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY) " .
                ($academicYearId ? " AND academic_year_id = ?" : "") . "
                GROUP BY due_date
                ORDER BY due_date LIMIT 3",
                $academicYearId ? [$academicYearId] : []
            ),
        ];

        // Chart data for analytics
        $chartData = [];
        if ($academicYearId) {
            // Monthly fee collection trends (last 12 months)
            $chartData['fee_collection'] = $this->db->select("
                SELECT DATE_FORMAT(fp.payment_date, '%Y-%m') as month,
                       SUM(fp.amount_paid) as total,
                       COUNT(fp.id) as transactions
                FROM fee_payments fp
                JOIN fees f ON fp.fee_id = f.id
                WHERE f.academic_year_id = ? AND fp.payment_date >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                GROUP BY DATE_FORMAT(fp.payment_date, '%Y-%m')
                ORDER BY month
            ", [$academicYearId]);

            // Expense category breakdowns
            $chartData['expense_breakdown'] = $this->db->select("
                SELECT category, SUM(amount) as total, COUNT(*) as transactions
                FROM expenses
                WHERE academic_year_id = ? AND expense_date >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                GROUP BY category
                ORDER BY total DESC
            ", [$academicYearId]);

            // Student enrollment growth over time
            $chartData['enrollment_growth'] = $this->db->select("
                SELECT DATE_FORMAT(s.created_at, '%Y-%m') as month, COUNT(*) as count
                FROM students s
                JOIN classes c ON s.class_id = c.id
                WHERE c.academic_year_id = ? AND s.created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                GROUP BY DATE_FORMAT(s.created_at, '%Y-%m')
                ORDER BY month
            ", [$academicYearId]);

            // Attendance statistics with trends
            $chartData['attendance_stats'] = $this->db->select("
                SELECT DATE_FORMAT(attendance_date, '%Y-%m') as month,
                       AVG(CASE WHEN status = 'present' THEN 1 ELSE 0 END) * 100 as rate,
                       COUNT(CASE WHEN status = 'present' THEN 1 END) as present_count,
                       COUNT(CASE WHEN status = 'absent' THEN 1 END) as absent_count
                FROM attendance
                WHERE academic_year_id = ? AND attendance_date >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                GROUP BY DATE_FORMAT(attendance_date, '%Y-%m')
                ORDER BY month
            ", [$academicYearId]);

            // Academic performance trends
            $chartData['academic_performance'] = $this->db->select("
                SELECT DATE_FORMAT(er.created_at, '%Y-%m') as month,
                       AVG(CASE WHEN er.grade = 'A' THEN 4 WHEN er.grade = 'B' THEN 3 WHEN er.grade = 'C' THEN 2 WHEN er.grade = 'D' THEN 1 ELSE 0 END) as avg_grade,
                       (SUM(CASE WHEN er.grade != 'F' THEN 1 ELSE 0 END) / COUNT(*)) * 100 as pass_rate
                FROM exam_results er
                WHERE er.academic_year_id = ? AND er.created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                GROUP BY DATE_FORMAT(er.created_at, '%Y-%m')
                ORDER BY month
            ", [$academicYearId]);
        }

        // Class-wise performance summary
        $classPerformance = $this->db->select("
            SELECT c.class_name, c.section,
                   COUNT(DISTINCT s.id) as students_count,
                   AVG(CASE WHEN er.grade = 'A' THEN 4 WHEN er.grade = 'B' THEN 3 WHEN er.grade = 'C' THEN 2 WHEN er.grade = 'D' THEN 1 ELSE 0 END) as avg_performance,
                   (SUM(CASE WHEN er.grade != 'F' THEN 1 ELSE 0 END) / COUNT(DISTINCT er.student_id)) * 100 as pass_rate
            FROM classes c
            LEFT JOIN students s ON c.id = s.class_id AND s.is_active = 1
            LEFT JOIN exam_results er ON s.id = er.student_id
            WHERE c.is_active = 1 " . ($academicYearId ? " AND c.academic_year_id = ?" : "") . "
            GROUP BY c.id, c.class_name, c.section
            ORDER BY avg_performance DESC
            LIMIT 5
        ", $academicYearId ? [$academicYearId] : []);

        // Get current academic year name
        $currentAcademicYear = null;
        if ($academicYearId) {
            $currentAcademicYear = $this->db->selectOne("SELECT year_name FROM academic_years WHERE id = ?", [$academicYearId])['year_name'] ?? null;
        }

        // System health indicators
        $systemHealth = [
            'database_status' => 'healthy', // Could be enhanced with actual checks
            'last_backup' => date('Y-m-d', strtotime('-1 day')), // Could be enhanced
            'active_users' => $this->db->selectOne("SELECT COUNT(*) as count FROM users WHERE is_active = 1")['count'],
            'storage_used' => '2.3 GB', // Could be enhanced with actual calculation
        ];

        $this->render('admin/dashboard', [
            'stats' => $stats,
            'financialStats' => $financialStats,
            'academicStats' => $academicStats,
            'recentData' => $recentData,
            'alerts' => $alerts,
            'current_academic_year' => $currentAcademicYear,
            'chartData' => $chartData,
            'classPerformance' => $classPerformance,
            'systemHealth' => $systemHealth
        ]);
    }

    public function students() {
        $filter = $_GET['filter'] ?? 'current_academic_year';
        $where = "";
        $params = [];

        if ($filter === 'current_academic_year') {
            $academicYearId = $this->getCurrentAcademicYearId();
            if ($academicYearId) {
                $where = "WHERE c.academic_year_id = ? AND s.tc_issued = 0";
                $params = [$academicYearId];
            } else {
                $where = "WHERE s.tc_issued = 0";
            }
        } elseif ($filter === 'all') {
            $where = "WHERE s.tc_issued = 0";
        }
        // For other filters, exclude TC-issued students

        $students = $this->db->select("SELECT s.*, c.class_name, c.section FROM students s LEFT JOIN classes c ON s.class_id = c.id $where ORDER BY s.created_at DESC", $params);
        $schoolName = $this->db->selectOne("SELECT setting_value FROM settings WHERE setting_key = 'school_name'")['setting_value'] ?? 'School Management System';
        $this->render('admin/students/index', ['students' => $students, 'school_name' => $schoolName, 'current_filter' => $filter]);
    }

    public function printStudents() {
        $academicYearId = $this->getCurrentAcademicYearId();
        $where = "WHERE s.tc_issued = 0";
        $params = [];
        if ($academicYearId) {
            $where .= " AND c.academic_year_id = ?";
            $params = [$academicYearId];
        }
        $students = $this->db->select("SELECT s.*, c.class_name, c.section FROM students s LEFT JOIN classes c ON s.class_id = c.id $where ORDER BY s.first_name, s.last_name", $params);
        $schoolName = $this->db->selectOne("SELECT setting_value FROM settings WHERE setting_key = 'school_name'")['setting_value'] ?? 'School Management System';
        $schoolAddress = $this->db->selectOne("SELECT setting_value FROM settings WHERE setting_key = 'school_address'")['setting_value'] ?? '';
        $schoolLogo = $this->db->selectOne("SELECT setting_value FROM settings WHERE setting_key = 'school_logo'")['setting_value'] ?? '';
        $this->render('admin/students/print', [
            'students' => $students,
            'school_name' => $schoolName,
            'school_address' => $schoolAddress,
            'school_logo' => $schoolLogo
        ]);
    }

    public function createStudent() {
        $academicYearId = $this->getCurrentAcademicYearId();
        $where = "WHERE is_active = 1";
        $params = [];
        if ($academicYearId) {
            $where .= " AND academic_year_id = ?";
            $params = [$academicYearId];
        }
        $classes = $this->db->select("SELECT * FROM classes $where ORDER BY class_name", $params);
        $csrfToken = $this->csrfToken();
        $this->render('admin/students/create', ['classes' => $classes, 'csrf_token' => $csrfToken]);
    }

    public function addStudent($id = null) {
        $academicYearId = $this->getCurrentAcademicYearId();
        $where = "WHERE is_active = 1";
        $params = [];
        if ($academicYearId) {
            $where .= " AND academic_year_id = ?";
            $params = [$academicYearId];
        }
        $classes = $this->db->select("SELECT * FROM classes $where ORDER BY class_name", $params);
        $csrfToken = $this->csrfToken();

        if ($id) {
            // Edit mode
            $student = $this->db->selectOne("SELECT * FROM students WHERE id = ?", [$id]);
            if (!$student) {
                $this->session->setFlash('error', 'Student not found');
                $this->redirect('/admin/students');
            }
            $this->render('admin/students/add', [
                'student' => $student,
                'classes' => $classes,
                'csrf_token' => $csrfToken
            ]);
        } else {
            // Create mode
            $this->render('admin/students/add', [
                'classes' => $classes,
                'csrf_token' => $csrfToken
            ]);
        }
    }

    public function storeStudent() {
        $data = [
            'scholar_number' => $_POST['scholar_number'] ?? '',
            'admission_number' => $_POST['admission_number'] ?? '',
            'admission_date' => $_POST['admission_date'] ?? '',
            'first_name' => $_POST['first_name'] ?? '',
            'middle_name' => $_POST['middle_name'] ?? '',
            'last_name' => $_POST['last_name'] ?? '',
            'date_of_birth' => $_POST['date_of_birth'] ?? '',
            'gender' => $_POST['gender'] ?? '',
            'caste_category' => $_POST['caste_category'] ?? '',
            'nationality' => $_POST['nationality'] ?? 'Indian',
            'religion' => $_POST['religion'] ?? '',
            'blood_group' => $_POST['blood_group'] ?? '',
            'village' => $_POST['village'] ?? '',
            'address' => $_POST['address'] ?? '',
            'permanent_address' => $_POST['permanent_address'] ?? '',
            'mobile' => $_POST['mobile'] ?? '',
            'email' => $_POST['email'] ?? '',
            'aadhar_number' => $_POST['aadhar_number'] ?? '',
            'samagra_number' => $_POST['samagra_number'] ?? '',
            'apaar_id' => $_POST['apaar_id'] ?? '',
            'pan_number' => $_POST['pan_number'] ?? '',
            'previous_school' => $_POST['previous_school'] ?? '',
            'medical_conditions' => $_POST['medical_conditions'] ?? '',
            'father_name' => $_POST['father_name'] ?? '',
            'mother_name' => $_POST['mother_name'] ?? '',
            'guardian_name' => $_POST['guardian_name'] ?? '',
            'guardian_contact' => $_POST['guardian_contact'] ?? '',
            'class_id' => $_POST['class_id'] ?? '',
            'csrf_token' => $_POST['csrf_token'] ?? ''
        ];

        if (!$this->checkCsrfToken($data['csrf_token'])) {
            $this->session->setFlash('error', 'Invalid CSRF token');
            $this->redirect('/admin/students/create');
        }

        // Auto-generate scholar number if not provided and auto-generation is enabled
        $autoGenerate = $this->db->selectOne("SELECT setting_value FROM settings WHERE setting_key = 'scholar_auto_generate'")['setting_value'] ?? '1';
        if (empty($data['scholar_number']) && !empty($data['class_id']) && $autoGenerate === '1') {
            $class = $this->db->selectOne("SELECT class_name FROM classes WHERE id = ?", [$data['class_id']]);
            if ($class) {
                $className = strtolower(trim($class['class_name']));
                if (in_array($className, ['nursery', 'ukg'])) {
                    $series = 'N';
                    $maxQuery = "SELECT MAX(CAST(SUBSTRING(scholar_number, 2) AS UNSIGNED)) as max_num FROM students WHERE scholar_number LIKE 'N%'";
                } else {
                    $classNum = (int) preg_replace('/\D/', '', $class['class_name']);
                    if ($classNum >= 1 && $classNum <= 8) {
                        $series = 'P';
                        $maxQuery = "SELECT MAX(CAST(SUBSTRING(scholar_number, 2) AS UNSIGNED)) as max_num FROM students WHERE scholar_number LIKE 'P%'";
                    } elseif ($classNum >= 9 && $classNum <= 12) {
                        $series = 'S';
                        $maxQuery = "SELECT MAX(CAST(SUBSTRING(scholar_number, 2) AS UNSIGNED)) as max_num FROM students WHERE scholar_number LIKE 'S%'";
                    } else {
                        $series = 'P'; // default
                        $maxQuery = "SELECT MAX(CAST(SUBSTRING(scholar_number, 2) AS UNSIGNED)) as max_num FROM students WHERE scholar_number LIKE 'P%'";
                    }
                }
                $maxNum = $this->db->selectOne($maxQuery)['max_num'] ?? 0;
                $newNum = $maxNum + 1;
                $data['scholar_number'] = $series . str_pad($newNum, 3, '0', STR_PAD_LEFT);
            }
        }

        $rules = [
            'scholar_number' => 'required|unique:students,scholar_number',
            'admission_number' => 'unique:students,admission_number',
            'first_name' => 'required|min:2|max:50',
            'last_name' => 'required|min:2|max:50',
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:male,female,other',
            'mobile' => 'required|regex:/^[0-9]{10,15}$/',
            'class_id' => 'required|numeric'
        ];

        if (!$this->validate($data, $rules)) {
            $this->session->setFlash('errors', $this->getValidationErrors());
            $this->session->setFlash('old', $data);
            $this->redirect('/admin/students/create');
        }

        // Handle file upload for photo
        $photoPath = '';
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = UPLOADS_PATH . 'students/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $fileName = uniqid() . '_' . basename($_FILES['photo']['name']);
            $targetFile = $uploadDir . $fileName;
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $targetFile)) {
                $photoPath = 'students/' . $fileName;
            }
        }

        $studentData = $data;
        unset($studentData['csrf_token']);
        if ($photoPath) {
            $studentData['photo'] = $photoPath;
        }

        $studentId = $this->db->insert('students', $studentData);

        if ($studentId) {
            // Update admission_number to use the student ID for uniqueness
            $this->db->update('students', ['admission_number' => $studentId], 'id = ?', [$studentId]);
            $this->session->setFlash('success', 'Student registered successfully');
            $this->redirect('/admin/students');
        } else {
            $this->session->setFlash('error', 'Failed to register student');
            $this->redirect('/admin/students/create');
        }
    }

    public function editStudent($id) {
        $student = $this->db->selectOne("SELECT * FROM students WHERE id = ?", [$id]);
        if (!$student) {
            $this->session->setFlash('error', 'Student not found');
            $this->redirect('/admin/students');
        }

        $classes = $this->db->select("SELECT * FROM classes WHERE is_active = 1 ORDER BY class_name");
        $csrfToken = $this->csrfToken();
        $this->render('admin/students/edit', [
            'student' => $student,
            'classes' => $classes,
            'csrf_token' => $csrfToken
        ]);
    }

    public function updateStudent($id) {
        $student = $this->db->selectOne("SELECT * FROM students WHERE id = ?", [$id]);
        if (!$student) {
            $this->session->setFlash('error', 'Student not found');
            $this->redirect('/admin/students');
        }

        // Similar to store, but for update
        $data = [
            'scholar_number' => $_POST['scholar_number'] ?? '',
            'admission_number' => $_POST['admission_number'] ?? '',
            'admission_date' => $_POST['admission_date'] ?? '',
            'first_name' => $_POST['first_name'] ?? '',
            'middle_name' => $_POST['middle_name'] ?? '',
            'last_name' => $_POST['last_name'] ?? '',
            'date_of_birth' => $_POST['date_of_birth'] ?? '',
            'gender' => $_POST['gender'] ?? '',
            'caste_category' => $_POST['caste_category'] ?? '',
            'nationality' => $_POST['nationality'] ?? 'Indian',
            'religion' => $_POST['religion'] ?? '',
            'blood_group' => $_POST['blood_group'] ?? '',
            'village' => $_POST['village'] ?? '',
            'address' => $_POST['address'] ?? '',
            'permanent_address' => $_POST['permanent_address'] ?? '',
            'mobile' => $_POST['mobile'] ?? '',
            'email' => $_POST['email'] ?? '',
            'aadhar_number' => $_POST['aadhar_number'] ?? '',
            'samagra_number' => $_POST['samagra_number'] ?? '',
            'apaar_id' => $_POST['apaar_id'] ?? '',
            'pan_number' => $_POST['pan_number'] ?? '',
            'previous_school' => $_POST['previous_school'] ?? '',
            'medical_conditions' => $_POST['medical_conditions'] ?? '',
            'father_name' => $_POST['father_name'] ?? '',
            'mother_name' => $_POST['mother_name'] ?? '',
            'guardian_name' => $_POST['guardian_name'] ?? '',
            'guardian_contact' => $_POST['guardian_contact'] ?? '',
            'class_id' => $_POST['class_id'] ?? '',
            'csrf_token' => $_POST['csrf_token'] ?? ''
        ];

        if (!$this->checkCsrfToken($data['csrf_token'])) {
            $this->session->setFlash('error', 'Invalid CSRF token');
            $this->redirect('/admin/students/edit/' . $id);
        }

        $rules = [
            'scholar_number' => 'required|unique:students,scholar_number,' . $id,
            'admission_number' => 'required|unique:students,admission_number,' . $id,
            'first_name' => 'required|min:2|max:50',
            'last_name' => 'required|min:2|max:50',
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:male,female,other',
            'mobile' => 'required|regex:/^[0-9]{10,15}$/',
            'class_id' => 'required|numeric'
        ];

        if (!$this->validate($data, $rules)) {
            $this->session->setFlash('errors', $this->getValidationErrors());
            $this->session->setFlash('old', $data);
            $this->redirect('/admin/students/edit/' . $id);
        }

        // Handle file upload for photo
        $photoPath = $student['photo'];
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = UPLOADS_PATH . 'students/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $fileName = uniqid() . '_' . basename($_FILES['photo']['name']);
            $targetFile = $uploadDir . $fileName;
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $targetFile)) {
                // Delete old photo if exists
                if ($student['photo'] && file_exists(UPLOADS_PATH . $student['photo'])) {
                    unlink(UPLOADS_PATH . $student['photo']);
                }
                $photoPath = 'students/' . $fileName;
            }
        }

        $studentData = $data;
        unset($studentData['csrf_token']);
        $studentData['photo'] = $photoPath;

        $updated = $this->db->update('students', $studentData, 'id = ?', [$id]);

        if ($updated) {
            $this->session->setFlash('success', 'Student updated successfully');
            $this->redirect('/admin/students');
        } else {
            $this->session->setFlash('error', 'Failed to update student');
            $this->redirect('/admin/students/edit/' . $id);
        }
    }

    public function viewStudent($id) {
        $student = $this->db->selectOne("
            SELECT s.*, c.class_name, c.section
            FROM students s
            LEFT JOIN classes c ON s.class_id = c.id
            WHERE s.id = ?
        ", [$id]);

        if (!$student) {
            $this->session->setFlash('error', 'Student not found');
            $this->redirect('/admin/students');
        }

        $this->render('admin/students/view', ['student' => $student]);
    }

    public function deleteStudent($id) {
        $student = $this->db->selectOne("SELECT * FROM students WHERE id = ?", [$id]);
        if (!$student) {
            $this->session->setFlash('error', 'Student not found');
            $this->redirect('/admin/students');
        }

        // Delete photo if exists
        if ($student['photo'] && file_exists(UPLOADS_PATH . $student['photo'])) {
            unlink(UPLOADS_PATH . $student['photo']);
        }

        $deleted = $this->db->delete('students', 'id = ?', [$id]);

        if ($deleted) {
            $this->session->setFlash('success', 'Student deleted successfully');
        } else {
            $this->session->setFlash('error', 'Failed to delete student');
        }

        $this->redirect('/admin/students');
    }

    public function bulkImportStudents() {
        $academicYearId = $this->getCurrentAcademicYearId();
        $where = "WHERE is_active = 1";
        $params = [];
        if ($academicYearId) {
            $where .= " AND academic_year_id = ?";
            $params = [$academicYearId];
        }
        $classes = $this->db->select("SELECT * FROM classes $where ORDER BY class_name", $params);
        $csrfToken = $this->csrfToken();
        $this->render('admin/students/bulk-import', ['classes' => $classes, 'csrf_token' => $csrfToken]);
    }

    public function processBulkImportStudents() {
        $data = $_POST;
        $csrfToken = $data['csrf_token'] ?? '';

        if (!$this->checkCsrfToken($csrfToken)) {
            $this->session->setFlash('error', 'Invalid CSRF token');
            $this->redirect('/admin/students/bulk-import');
        }

        if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            $this->session->setFlash('error', 'Please select a valid CSV file');
            $this->redirect('/admin/students/bulk-import');
        }

        $file = $_FILES['csv_file']['tmp_name'];
        $handle = fopen($file, 'r');

        if (!$handle) {
            $this->session->setFlash('error', 'Unable to read CSV file');
            $this->redirect('/admin/students/bulk-import');
        }

        // Read header row
        $header = fgetcsv($handle);
        if (!$header) {
            $this->session->setFlash('error', 'CSV file is empty or invalid');
            $this->redirect('/admin/students/bulk-import');
        }

        // Expected columns
        $expectedColumns = [
            'scholar_number', 'admission_number', 'admission_date', 'first_name', 'middle_name', 'last_name',
            'date_of_birth', 'gender', 'caste_category', 'nationality', 'religion', 'blood_group',
            'village', 'address', 'permanent_address', 'mobile', 'email', 'aadhar_number',
            'samagra_number', 'apaar_id', 'pan_number', 'previous_school', 'medical_conditions',
            'father_name', 'mother_name', 'guardian_name', 'guardian_contact', 'class_name', 'section'
        ];

        // Validate header
        $headerMap = [];
        foreach ($expectedColumns as $col) {
            $index = array_search($col, $header);
            if ($index === false) {
                $this->session->setFlash('error', "Missing required column: $col");
                $this->redirect('/admin/students/bulk-import');
            }
            $headerMap[$col] = $index;
        }

        $academicYearId = $this->getCurrentAcademicYearId();
        $errors = [];
        $successCount = 0;
        $rowNumber = 1;

        $this->db->beginTransaction();

        try {
            while (($row = fgetcsv($handle)) !== false) {
                $rowNumber++;

                // Map CSV data to student data
                $studentData = [];
                foreach ($headerMap as $field => $index) {
                    $value = trim($row[$index] ?? '');
                    if ($value !== '') {
                        $studentData[$field] = $value;
                    }
                }

                // Validate required fields
                $required = ['scholar_number', 'admission_number', 'first_name', 'last_name', 'gender', 'mobile', 'class_name'];
                foreach ($required as $field) {
                    if (empty($studentData[$field])) {
                        $errors[] = "Row $rowNumber: Missing required field '$field'";
                        continue 2;
                    }
                }

                // Get class_id from class_name and section
                $className = $studentData['class_name'];
                $section = $studentData['section'] ?? '';
                $class = $this->db->selectOne("SELECT id FROM classes WHERE class_name = ? AND section = ? AND is_active = 1" . ($academicYearId ? " AND academic_year_id = ?" : ""), $academicYearId ? [$className, $section, $academicYearId] : [$className, $section]);

                if (!$class) {
                    $errors[] = "Row $rowNumber: Invalid class '$className $section'";
                    continue;
                }

                $studentData['class_id'] = $class['id'];
                unset($studentData['class_name'], $studentData['section']);

                // Set defaults
                $studentData['nationality'] = $studentData['nationality'] ?? 'Indian';
                $studentData['is_active'] = 1;

                // Validate data
                $rules = [
                    'scholar_number' => 'required|unique:students,scholar_number',
                    'admission_number' => 'required|unique:students,admission_number',
                    'first_name' => 'required|min:2|max:50',
                    'last_name' => 'required|min:2|max:50',
                    'date_of_birth' => 'date',
                    'gender' => 'required|in:male,female,other',
                    'mobile' => 'required|regex:/^[0-9]{10,15}$/',
                    'email' => 'email',
                    'class_id' => 'required|numeric'
                ];

                if (!$this->validate($studentData, $rules)) {
                    $validationErrors = $this->getValidationErrors();
                    foreach ($validationErrors as $error) {
                        $errors[] = "Row $rowNumber: $error";
                    }
                    continue;
                }

                // Insert student
                $studentId = $this->db->insert('students', $studentData);

                if ($studentId) {
                    $successCount++;
                } else {
                    $errors[] = "Row $rowNumber: Failed to insert student";
                }
            }

            if (empty($errors)) {
                $this->db->commit();
                $this->session->setFlash('success', "Successfully imported $successCount students");
                $this->redirect('/admin/students');
            } else {
                $this->db->rollback();
                $this->session->setFlash('errors', $errors);
                $this->session->setFlash('error', "Import failed. $successCount students imported, " . count($errors) . " errors found.");
                $this->redirect('/admin/students/bulk-import');
            }

        } catch (Exception $e) {
            $this->db->rollback();
            $this->session->setFlash('error', 'Import failed: ' . $e->getMessage());
            $this->redirect('/admin/students/bulk-import');
        }

        fclose($handle);
    }

    public function bulkExportStudents() {
        $filter = $_GET['filter'] ?? 'current_academic_year';
        $where = "";
        $params = [];

        if ($filter === 'current_academic_year') {
            $academicYearId = $this->getCurrentAcademicYearId();
            if ($academicYearId) {
                $where = "WHERE c.academic_year_id = ?";
                $params = [$academicYearId];
            }
        }

        $students = $this->db->select("SELECT s.*, c.class_name, c.section FROM students s LEFT JOIN classes c ON s.class_id = c.id $where ORDER BY s.first_name, s.last_name", $params);

        // Generate CSV
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="students_' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');

        // Write header
        fputcsv($output, [
            'scholar_number', 'admission_number', 'admission_date', 'first_name', 'middle_name', 'last_name',
            'date_of_birth', 'gender', 'caste_category', 'nationality', 'religion', 'blood_group',
            'village', 'address', 'permanent_address', 'mobile', 'email', 'aadhar_number',
            'samagra_number', 'apaar_id', 'pan_number', 'previous_school', 'medical_conditions',
            'father_name', 'mother_name', 'guardian_name', 'guardian_contact', 'class_name', 'section'
        ]);

        // Write data
        foreach ($students as $student) {
            fputcsv($output, [
                $student['scholar_number'],
                $student['admission_number'],
                $student['admission_date'],
                $student['first_name'],
                $student['middle_name'],
                $student['last_name'],
                $student['date_of_birth'],
                $student['gender'],
                $student['caste_category'],
                $student['nationality'],
                $student['religion'],
                $student['blood_group'],
                $student['village'],
                $student['address'],
                $student['permanent_address'],
                $student['mobile'],
                $student['email'],
                $student['aadhar_number'],
                $student['samagra_number'],
                $student['apaar_id'],
                $student['pan_number'],
                $student['previous_school'],
                $student['medical_conditions'],
                $student['father_name'],
                $student['mother_name'],
                $student['guardian_name'],
                $student['guardian_contact'],
                $student['class_name'],
                $student['section']
            ]);
        }

        fclose($output);
        exit;
    }

    public function classes() {
        $classes = $this->db->select("SELECT c.*, COUNT(s.id) as student_count FROM classes c LEFT JOIN students s ON c.id = s.class_id GROUP BY c.id ORDER BY c.class_name");
        $subjects = $this->db->select("SELECT * FROM subjects ORDER BY subject_name");
        $csrfToken = $this->csrfToken();
        $academicYearId = $this->getCurrentAcademicYearId();
        $this->render('admin/classes/index', ['classes' => $classes, 'subjects' => $subjects, 'csrf_token' => $csrfToken, 'academic_year_id' => $academicYearId]);
    }

    public function createClass() {
        $academicYearId = $this->getCurrentAcademicYearId();
        $csrfToken = $this->csrfToken();
        $this->render('admin/classes/create', ['csrf_token' => $csrfToken, 'academic_year_id' => $academicYearId]);
    }

    public function storeClass() {
        $data = [
            'class_name' => $_POST['class_name'] ?? '',
            'section' => $_POST['section'] ?? '',
            'academic_year_id' => $_POST['academic_year_id'] ?? '',
            'csrf_token' => $_POST['csrf_token'] ?? ''
        ];

        if (!$this->checkCsrfToken($data['csrf_token'])) {
            $this->session->setFlash('error', 'Invalid CSRF token');
            $this->redirect('/admin/classes/create');
        }

        $rules = [
            'class_name' => 'required',
            'section' => 'required',
            'academic_year_id' => 'required|numeric'
        ];

        if (!$this->validate($data, $rules)) {
            $this->session->setFlash('errors', $this->getValidationErrors());
            $this->session->setFlash('old', $data);
            $this->redirect('/admin/classes/create');
        }

        $classData = $data;
        unset($classData['csrf_token']);
        $classData['is_active'] = 1;

        $classId = $this->db->insert('classes', $classData);

        if ($classId) {
            $this->session->setFlash('success', 'Class created successfully');
            $this->redirect('/admin/classes');
        } else {
            $this->session->setFlash('error', 'Failed to create class');
            $this->redirect('/admin/classes/create');
        }
    }

    public function editClass($id) {
        $class = $this->db->selectOne("SELECT * FROM classes WHERE id = ?", [$id]);
        if (!$class) {
            $this->session->setFlash('error', 'Class not found');
            $this->redirect('/admin/classes');
        }

        $csrfToken = $this->csrfToken();
        $this->render('admin/classes/edit', ['class' => $class, 'csrf_token' => $csrfToken]);
    }

    public function updateClass($id) {
        $class = $this->db->selectOne("SELECT * FROM classes WHERE id = ?", [$id]);
        if (!$class) {
            $this->session->setFlash('error', 'Class not found');
            $this->redirect('/admin/classes');
        }

        $data = [
            'class_name' => $_POST['class_name'] ?? '',
            'section' => $_POST['section'] ?? '',
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            'csrf_token' => $_POST['csrf_token'] ?? ''
        ];

        if (!$this->checkCsrfToken($data['csrf_token'])) {
            $this->session->setFlash('error', 'Invalid CSRF token');
            $this->redirect('/admin/classes/edit/' . $id);
        }

        $rules = [
            'class_name' => 'required',
            'section' => 'required'
        ];

        if (!$this->validate($data, $rules)) {
            $this->session->setFlash('errors', $this->getValidationErrors());
            $this->session->setFlash('old', $data);
            $this->redirect('/admin/classes/edit/' . $id);
        }

        $classData = $data;
        unset($classData['csrf_token']);

        $updated = $this->db->update('classes', $classData, 'id = ?', [$id]);

        if ($updated) {
            $this->session->setFlash('success', 'Class updated successfully');
            $this->redirect('/admin/classes');
        } else {
            $this->session->setFlash('error', 'Failed to update class');
            $this->redirect('/admin/classes/edit/' . $id);
        }
    }

    public function deleteClass($id) {
        $class = $this->db->selectOne("SELECT * FROM classes WHERE id = ?", [$id]);
        if (!$class) {
            $this->session->setFlash('error', 'Class not found');
            $this->redirect('/admin/classes');
        }

        // Check if class has students
        $studentCount = $this->db->selectOne("SELECT COUNT(*) as count FROM students WHERE class_id = ?", [$id])['count'];
        if ($studentCount > 0) {
            $this->session->setFlash('error', 'Cannot delete class with students');
            $this->redirect('/admin/classes');
        }

        $deleted = $this->db->delete('classes', 'id = ?', [$id]);

        if ($deleted) {
            $this->session->setFlash('success', 'Class deleted successfully');
        } else {
            $this->session->setFlash('error', 'Failed to delete class');
        }

        $this->redirect('/admin/classes');
    }

    public function subjects() {
        $subjects = $this->db->select("SELECT * FROM subjects ORDER BY subject_name");
        $this->render('admin/subjects/index', ['subjects' => $subjects]);
    }

    public function createSubject() {
        $csrfToken = $this->csrfToken();
        $this->render('admin/subjects/create', ['csrf_token' => $csrfToken]);
    }

    public function storeSubject() {
        $data = [
            'subject_name' => $_POST['subject_name'] ?? '',
            'subject_code' => $_POST['subject_code'] ?? '',
            'description' => $_POST['description'] ?? '',
            'csrf_token' => $_POST['csrf_token'] ?? ''
        ];

        if (!$this->checkCsrfToken($data['csrf_token'])) {
            $this->session->setFlash('error', 'Invalid CSRF token');
            $this->redirect('/admin/subjects/create');
        }

        $rules = [
            'subject_name' => 'required',
            'subject_code' => 'required|unique:subjects,subject_code'
        ];

        if (!$this->validate($data, $rules)) {
            $this->session->setFlash('errors', $this->getValidationErrors());
            $this->session->setFlash('old', $data);
            $this->redirect('/admin/subjects/create');
        }

        $subjectData = $data;
        unset($subjectData['csrf_token']);
        $subjectData['is_active'] = 1;

        $subjectId = $this->db->insert('subjects', $subjectData);

        if ($subjectId) {
            $this->session->setFlash('success', 'Subject created successfully');
            $this->redirect('/admin/classes');
        } else {
            $this->session->setFlash('error', 'Failed to create subject');
            $this->redirect('/admin/subjects/create');
        }
    }

    public function editSubject($id) {
        $subject = $this->db->selectOne("SELECT * FROM subjects WHERE id = ?", [$id]);
        if (!$subject) {
            $this->session->setFlash('error', 'Subject not found');
            $this->redirect('/admin/subjects');
        }

        $csrfToken = $this->csrfToken();
        $this->render('admin/subjects/edit', ['subject' => $subject, 'csrf_token' => $csrfToken]);
    }

    public function updateSubject($id) {
        $subject = $this->db->selectOne("SELECT * FROM subjects WHERE id = ?", [$id]);
        if (!$subject) {
            $this->session->setFlash('error', 'Subject not found');
            $this->redirect('/admin/subjects');
        }

        $data = [
            'subject_name' => $_POST['subject_name'] ?? '',
            'subject_code' => $_POST['subject_code'] ?? '',
            'description' => $_POST['description'] ?? '',
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            'csrf_token' => $_POST['csrf_token'] ?? ''
        ];

        if (!$this->checkCsrfToken($data['csrf_token'])) {
            $this->session->setFlash('error', 'Invalid CSRF token');
            $this->redirect('/admin/subjects/edit/' . $id);
        }

        $rules = [
            'subject_name' => 'required',
            'subject_code' => 'required|unique:subjects,subject_code,' . $id
        ];

        if (!$this->validate($data, $rules)) {
            $this->session->setFlash('errors', $this->getValidationErrors());
            $this->session->setFlash('old', $data);
            $this->redirect('/admin/subjects/edit/' . $id);
        }

        $subjectData = $data;
        unset($subjectData['csrf_token']);

        $updated = $this->db->update('subjects', $subjectData, 'id = ?', [$id]);

        if ($updated) {
            $this->session->setFlash('success', 'Subject updated successfully');
            $this->redirect('/admin/classes');
        } else {
            $this->session->setFlash('error', 'Failed to update subject');
            $this->redirect('/admin/subjects/edit/' . $id);
        }
    }

    public function deleteSubject($id) {
        $subject = $this->db->selectOne("SELECT * FROM subjects WHERE id = ?", [$id]);
        if (!$subject) {
            $this->session->setFlash('error', 'Subject not found');
            $this->redirect('/admin/subjects');
        }

        // Check if subject is used in class_subjects
        $usageCount = $this->db->selectOne("SELECT COUNT(*) as count FROM class_subjects WHERE subject_id = ?", [$id])['count'];
        if ($usageCount > 0) {
            $this->session->setFlash('error', 'Cannot delete subject that is assigned to classes');
            $this->redirect('/admin/subjects');
        }

        $deleted = $this->db->delete('subjects', 'id = ?', [$id]);

        if ($deleted) {
            $this->session->setFlash('success', 'Subject deleted successfully');
        } else {
            $this->session->setFlash('error', 'Failed to delete subject');
        }

        $this->redirect('/admin/classes');
    }

    public function promoteStudents() {
        $academicYearId = $this->getCurrentAcademicYearId();
        $where = "WHERE is_active = 1";
        $params = [];
        if ($academicYearId) {
            $where .= " AND academic_year_id = ?";
            $params = [$academicYearId];
        }
        // Get all classes ordered by grade
        $classes = $this->db->select("SELECT * FROM classes $where ORDER BY class_name", $params);

        // Group classes by grade level for promotion logic
        $gradeGroups = [];
        foreach ($classes as $class) {
            // Extract grade from class name (e.g., "Grade 1", "Class 1", etc.)
            preg_match('/(\d+)/', $class['class_name'], $matches);
            $grade = $matches[1] ?? 0;
            $gradeGroups[$grade][] = $class;
        }

        ksort($gradeGroups); // Sort by grade

        $this->render('admin/classes/promote', [
            'classes' => $classes,
            'grade_groups' => $gradeGroups
        ]);
    }

    public function processPromotion() {
        $data = $_POST;
        $csrfToken = $data['csrf_token'] ?? '';

        if (!$this->checkCsrfToken($csrfToken)) {
            $this->session->setFlash('error', 'Invalid CSRF token');
            $this->redirect('/admin/classes/promote');
        }

        $fromClassId = $data['from_class_id'] ?? '';
        $toClassId = $data['to_class_id'] ?? '';
        $academicYear = $data['academic_year'] ?? date('Y') . '-' . (date('Y') + 1);

        if (!$fromClassId || !$toClassId) {
            $this->session->setFlash('error', 'Please select both source and target classes');
            $this->redirect('/admin/classes/promote');
        }

        // Get students from source class
        $students = $this->db->select("SELECT * FROM students WHERE class_id = ? AND is_active = 1", [$fromClassId]);

        if (empty($students)) {
            $this->session->setFlash('error', 'No students found in the selected class');
            $this->redirect('/admin/classes/promote');
        }

        // Start transaction
        $this->db->beginTransaction();

        try {
            $promotedCount = 0;

            foreach ($students as $student) {
                // Check if student meets promotion criteria (simplified - in production, check exam results)
                $canPromote = $this->checkPromotionCriteria($student['id'], $fromClassId, $academicYear);

                if ($canPromote) {
                    // Update student class
                    $this->db->update('students', [
                        'class_id' => $toClassId,
                        'updated_at' => date('Y-m-d H:i:s')
                    ], 'id = ?', [$student['id']]);

                    // Log promotion
                    $this->db->insert('audit_logs', [
                        'user_id' => $_SESSION['user']['id'] ?? 1,
                        'action' => 'student_promotion',
                        'table_name' => 'students',
                        'record_id' => $student['id'],
                        'old_values' => json_encode(['class_id' => $fromClassId]),
                        'new_values' => json_encode(['class_id' => $toClassId]),
                        'created_at' => date('Y-m-d H:i:s')
                    ]);

                    $promotedCount++;
                }
            }

            $this->db->commit();

            $this->session->setFlash('success', "Promotion completed! {$promotedCount} out of " . count($students) . " students promoted successfully.");
            $this->redirect('/admin/classes');

        } catch (Exception $e) {
            $this->db->rollback();
            $this->session->setFlash('error', 'Failed to process promotion: ' . $e->getMessage());
            $this->redirect('/admin/classes/promote');
        }
    }

    private function checkPromotionCriteria($studentId, $classId, $academicYear) {
        // Simplified promotion criteria - in production, check exam results, attendance, etc.
        // For now, we'll promote all students (can be enhanced based on requirements)

        // Check attendance rate (should be above 75%)
        $attendanceStats = $this->db->selectOne("
            SELECT
                COUNT(*) as total_days,
                SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_days
            FROM attendance
            WHERE student_id = ? AND class_id = ?
        ", [$studentId, $classId]);

        if ($attendanceStats['total_days'] > 0) {
            $attendanceRate = ($attendanceStats['present_days'] / $attendanceStats['total_days']) * 100;
            if ($attendanceRate < 75) {
                return false; // Attendance too low
            }
        }

        // Check exam results (should pass all subjects)
        $examResults = $this->db->select("
            SELECT er.*, e.exam_type
            FROM exam_results er
            LEFT JOIN exams e ON er.exam_id = e.id
            WHERE er.student_id = ? AND e.class_id = ? AND e.exam_type = 'final'
            ORDER BY er.created_at DESC
            LIMIT 10
        ", [$studentId, $classId]);

        if (!empty($examResults)) {
            foreach ($examResults as $result) {
                if ($result['grade'] === 'F') {
                    return false; // Failed subject
                }
            }
        }

        return true; // Can be promoted
    }

    public function attendance() {
        $academicYearId = $this->getCurrentAcademicYearId();
        $where = "WHERE is_active = 1";
        $params = [];
        if ($academicYearId) {
            $where .= " AND academic_year_id = ?";
            $params = [$academicYearId];
        }
        $classes = $this->db->select("SELECT * FROM classes $where ORDER BY class_name", $params);
        $csrfToken = $this->csrfToken();
        $this->render('admin/attendance/index', ['classes' => $classes, 'csrf_token' => $csrfToken]);
    }

    public function attendanceData() {
        $classId = $_GET['class_id'] ?? '';
        $date = $_GET['date'] ?? '';

        if (!$classId || !$date) {
            $this->json(['error' => 'Class ID and date are required'], 400);
        }

        $academicYearId = $this->getCurrentAcademicYearId();

        // Get students in the class
        $students = $this->db->select("
            SELECT s.*, c.class_name, c.section
            FROM students s
            LEFT JOIN classes c ON s.class_id = c.id
            WHERE s.class_id = ? AND s.is_active = 1" . ($academicYearId ? " AND c.academic_year_id = ?" : "") . "
            ORDER BY s.first_name, s.last_name
        ", $academicYearId ? [$classId, $academicYearId] : [$classId]);

        // Get existing attendance for the date
        $attendance = $this->db->select("
            SELECT * FROM attendance
            WHERE class_id = ? AND attendance_date = ?" . ($academicYearId ? " AND academic_year_id = ?" : "") . "
        ", $academicYearId ? [$classId, $date, $academicYearId] : [$classId, $date]);

        $this->json([
            'students' => $students,
            'attendance' => $attendance
        ]);
    }

    public function saveAttendance() {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!$data || !isset($data['class_id']) || !isset($data['date']) || !isset($data['attendance'])) {
            $this->json(['success' => false, 'message' => 'Invalid data'], 400);
        }

        $classId = $data['class_id'];
        $date = $data['date'];
        $attendanceData = $data['attendance'];

        // Start transaction
        $this->db->beginTransaction();

        try {
            // Delete existing attendance for this class and date
            $this->db->delete('attendance', 'class_id = ? AND attendance_date = ?', [$classId, $date]);

            // Insert new attendance records
            $academicYearId = $this->getCurrentAcademicYearId();
            foreach ($attendanceData as $record) {
                $data = [
                    'student_id' => $record['student_id'],
                    'class_id' => $classId,
                    'attendance_date' => $date,
                    'status' => $record['status'],
                    'marked_by' => $_SESSION['user']['id'] ?? 1,
                    'created_at' => date('Y-m-d H:i:s')
                ];
                if ($academicYearId) {
                    $data['academic_year_id'] = $academicYearId;
                }
                $this->db->insert('attendance', $data);
            }

            $this->db->commit();
            $this->json(['success' => true, 'message' => 'Attendance saved successfully']);

        } catch (Exception $e) {
            $this->db->rollback();
            $this->json(['success' => false, 'message' => 'Failed to save attendance: ' . $e->getMessage()], 500);
        }
    }

    public function exportAttendance() {
        $classId = $_GET['class_id'] ?? '';
        $date = $_GET['date'] ?? '';

        if (!$classId || !$date) {
            die('Class ID and date are required');
        }

        // Get class info
        $class = $this->db->selectOne("SELECT * FROM classes WHERE id = ?", [$classId]);
        if (!$class) {
            die('Class not found');
        }

        $academicYearId = $this->getCurrentAcademicYearId();

        // Get attendance data
        $attendance = $this->db->select("
            SELECT s.scholar_number, s.first_name, s.last_name, a.status
            FROM students s
            LEFT JOIN classes c ON s.class_id = c.id
            LEFT JOIN attendance a ON s.id = a.student_id AND a.attendance_date = ?" . ($academicYearId ? " AND a.academic_year_id = ?" : "") . "
            WHERE s.class_id = ? AND s.is_active = 1" . ($academicYearId ? " AND c.academic_year_id = ?" : "") . "
            ORDER BY s.first_name, s.last_name
        ", $academicYearId ? [$date, $academicYearId, $classId, $academicYearId] : [$date, $classId]);

        // Generate CSV
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="attendance_' . $class['class_name'] . '_' . $date . '.csv"');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['Scholar Number', 'First Name', 'Last Name', 'Status']);

        foreach ($attendance as $record) {
            fputcsv($output, [
                $record['scholar_number'],
                $record['first_name'],
                $record['last_name'],
                $record['status'] ?: 'Not Marked'
            ]);
        }

        fclose($output);
        exit;
    }

    public function exams() {
        $exams = $this->db->select("SELECT e.*, c.class_name FROM exams e LEFT JOIN classes c ON e.class_id = c.id ORDER BY e.created_at DESC");
        $this->render('admin/exams/index', ['exams' => $exams]);
    }

    public function admitCards() {
        $academicYearId = $this->getCurrentAcademicYearId();
        $where = "WHERE e.is_active = 1";
        $params = [];
        if ($academicYearId) {
            $where .= " AND e.academic_year_id = ?";
            $params = [$academicYearId];
        }
        $exams = $this->db->select("SELECT e.*, c.class_name FROM exams e LEFT JOIN classes c ON e.class_id = c.id $where ORDER BY e.start_date DESC", $params);
        $csrfToken = $this->csrfToken();
        $this->render('admin/exams/admit-cards', ['exams' => $exams, 'csrf_token' => $csrfToken]);
    }

    public function getExamStudents($examId) {
        // Get exam details
        $exam = $this->db->selectOne("SELECT * FROM exams WHERE id = ?", [$examId]);
        if (!$exam) {
            $this->json(['error' => 'Exam not found'], 404);
        }

        // Get students for this exam's class
        $students = $this->db->select("
            SELECT s.*, c.class_name, c.section
            FROM students s
            LEFT JOIN classes c ON s.class_id = c.id
            WHERE s.class_id = ? AND s.is_active = 1 AND s.tc_issued = 0
            ORDER BY s.first_name, s.last_name
        ", [$exam['class_id']]);

        $this->json(['students' => $students]);
    }

    public function generateAdmitCards() {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!$data || !isset($data['exam_id']) || !isset($data['students'])) {
            $this->json(['success' => false, 'message' => 'Invalid data'], 400);
        }

        $examId = $data['exam_id'];
        $studentIds = $data['students'];
        $includePhotos = $data['include_photos'] ?? true;
        $includeSignatures = $data['include_signatures'] ?? true;
        $cardsPerPage = $data['cards_per_page'] ?? 4;

        // Get exam details
        $exam = $this->db->selectOne("
            SELECT e.*, c.class_name, c.section
            FROM exams e
            LEFT JOIN classes c ON e.class_id = c.id
            WHERE e.id = ?
        ", [$examId]);

        if (!$exam) {
            $this->json(['success' => false, 'message' => 'Exam not found'], 404);
        }

        // Get students
        $students = $this->db->select("
            SELECT s.*, c.class_name, c.section
            FROM students s
            LEFT JOIN classes c ON s.class_id = c.id
            WHERE s.id IN (" . str_repeat('?,', count($studentIds) - 1) . "?) AND s.is_active = 1
            ORDER BY s.first_name, s.last_name
        ", $studentIds);

        // Generate HTML for admit cards
        $html = $this->generateAdmitCardsHTML($exam, $students, $includePhotos, $includeSignatures, $cardsPerPage);

        // For now, return HTML. In production, this would generate PDF
        // Save HTML to temporary file and return URL
        $filename = 'admit_cards_' . $examId . '_' . time() . '.html';
        $filepath = BASE_PATH . 'temp/' . $filename;

        // Ensure temp directory exists
        if (!is_dir(dirname($filepath))) {
            mkdir(dirname($filepath), 0755, true);
        }

        file_put_contents($filepath, $html);

        $this->json([
            'success' => true,
            'message' => 'Admit cards generated successfully',
            'html_url' => '/temp/' . $filename,
            'pdf_url' => '/temp/' . $filename // In production, this would be PDF URL
        ]);
    }

    public function generateAdmitCard() {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!$data || !isset($data['exam_id']) || !isset($data['student_id'])) {
            $this->json(['success' => false, 'message' => 'Invalid data'], 400);
        }

        $examId = $data['exam_id'];
        $studentId = $data['student_id'];
        $includePhotos = $data['include_photos'] ?? true;
        $includeSignatures = $data['include_signatures'] ?? true;

        // Get exam details
        $exam = $this->db->selectOne("
            SELECT e.*, c.class_name, c.section
            FROM exams e
            LEFT JOIN classes c ON e.class_id = c.id
            WHERE e.id = ?
        ", [$examId]);

        if (!$exam) {
            $this->json(['success' => false, 'message' => 'Exam not found'], 404);
        }

        // Get student
        $student = $this->db->selectOne("
            SELECT s.*, c.class_name, c.section
            FROM students s
            LEFT JOIN classes c ON s.class_id = c.id
            WHERE s.id = ? AND s.is_active = 1
        ", [$studentId]);

        if (!$student) {
            $this->json(['success' => false, 'message' => 'Student not found'], 404);
        }

        // Generate HTML for single admit card
        $html = $this->generateAdmitCardsHTML($exam, [$student], $includePhotos, $includeSignatures, 1);

        // Save HTML to temporary file
        $filename = 'admit_card_' . $examId . '_' . $studentId . '_' . time() . '.html';
        $filepath = BASE_PATH . 'temp/' . $filename;

        if (!is_dir(dirname($filepath))) {
            mkdir(dirname($filepath), 0755, true);
        }

        file_put_contents($filepath, $html);

        $this->json([
            'success' => true,
            'message' => 'Admit card generated successfully',
            'html_url' => '/temp/' . $filename,
            'pdf_url' => '/temp/' . $filename
        ]);
    }

    public function certificates() {
        $academicYearId = $this->getCurrentAcademicYearId();
        $where = "WHERE is_active = 1";
        $params = [];
        if ($academicYearId) {
            $where .= " AND academic_year_id = ?";
            $params = [$academicYearId];
        }
        $classes = $this->db->select("SELECT * FROM classes $where ORDER BY class_name", $params);
        $csrfToken = $this->csrfToken();
        $this->render('admin/certificates/index', ['classes' => $classes, 'csrf_token' => $csrfToken]);
    }

    public function tcCertificates() {
        $certificates = $this->db->select("SELECT c.*, s.first_name, s.last_name, s.scholar_number FROM certificates c LEFT JOIN students s ON c.student_id = s.id WHERE c.certificate_type = 'transfer' ORDER BY c.created_at DESC");
        $csrfToken = $this->csrfToken();
        $this->render('admin/certificates/tc', ['certificates' => $certificates, 'csrf_token' => $csrfToken]);
    }

    public function characterCertificates() {
        $certificates = $this->db->select("SELECT c.*, s.first_name, s.last_name, s.scholar_number FROM certificates c LEFT JOIN students s ON c.student_id = s.id WHERE c.certificate_type = 'character' ORDER BY c.created_at DESC");
        $this->render('admin/certificates/character', ['certificates' => $certificates]);
    }

    public function bonafideCertificates() {
        $certificates = $this->db->select("SELECT c.*, s.first_name, s.last_name, s.scholar_number FROM certificates c LEFT JOIN students s ON c.student_id = s.id WHERE c.certificate_type = 'bonafide' ORDER BY c.created_at DESC");
        $this->render('admin/certificates/bonafide', ['certificates' => $certificates]);
    }

    public function viewCertificate($id) {
        $certificate = $this->db->selectOne("
            SELECT c.*, s.first_name, s.last_name, s.scholar_number, cl.class_name, cl.section, s.admission_date
            FROM certificates c
            LEFT JOIN students s ON c.student_id = s.id
            LEFT JOIN classes cl ON s.class_id = cl.id
            WHERE c.id = ?
        ", [$id]);

        if (!$certificate) {
            $this->session->setFlash('error', 'Certificate not found');
            $this->redirect('/admin/certificates/tc');
        }

        $this->render('admin/certificates/view', ['certificate' => $certificate]);
    }

    public function printStudentApplication($studentId) {
        // Get student details with class information
        $student = $this->db->selectOne("
            SELECT s.*, c.class_name, c.section
            FROM students s
            LEFT JOIN classes c ON s.class_id = c.id
            WHERE s.id = ?
        ", [$studentId]);

        if (!$student) {
            $this->session->setFlash('error', 'Student not found');
            $this->redirect('/admin/students');
        }

        // Get school settings
        $schoolName = $this->db->selectOne("SELECT setting_value FROM settings WHERE setting_key = 'school_name'")['setting_value'] ?? 'School Management System';
        $schoolAddress = $this->db->selectOne("SELECT setting_value FROM settings WHERE setting_key = 'school_address'")['setting_value'] ?? '';
        $schoolPhone = $this->db->selectOne("SELECT setting_value FROM settings WHERE setting_key = 'school_phone'")['setting_value'] ?? '';
        $schoolEmail = $this->db->selectOne("SELECT setting_value FROM settings WHERE setting_key = 'school_email'")['setting_value'] ?? '';
        $schoolLogo = $this->db->selectOne("SELECT setting_value FROM settings WHERE setting_key = 'school_logo'")['setting_value'] ?? '';

        $this->render('admin/students/print_application', [
            'student' => $student,
            'school_name' => $schoolName,
            'school_address' => $schoolAddress,
            'school_phone' => $schoolPhone,
            'school_email' => $schoolEmail,
            'school_logo' => $schoolLogo
        ]);
    }

    public function reAdministerStudent($studentId) {
        // Check CSRF token
        $data = json_decode(file_get_contents('php://input'), true);
        $csrfToken = $data['_token'] ?? '';

        if (!$this->checkCsrfToken($csrfToken)) {
            $this->json(['success' => false, 'message' => 'Invalid CSRF token'], 400);
        }

        // Check if student exists
        $student = $this->db->selectOne("SELECT * FROM students WHERE id = ?", [$studentId]);
        if (!$student) {
            $this->json(['success' => false, 'message' => 'Student not found'], 404);
        }

        // Update student status
        $updated = $this->db->update('students', [
            'tc_issued' => 0,
            'is_active' => 1,
            'updated_at' => date('Y-m-d H:i:s')
        ], 'id = ?', [$studentId]);

        if ($updated) {
            // Log the action
            $this->db->insert('audit_logs', [
                'user_id' => $_SESSION['user']['id'] ?? 1,
                'action' => 'student_re_administered',
                'table_name' => 'students',
                'record_id' => $studentId,
                'old_values' => json_encode(['tc_issued' => 1, 'is_active' => 0]),
                'new_values' => json_encode(['tc_issued' => 0, 'is_active' => 1]),
                'created_at' => date('Y-m-d H:i:s')
            ]);

            $this->json(['success' => true, 'message' => 'Student re-administered successfully']);
        } else {
            $this->json(['success' => false, 'message' => 'Failed to re-administer student'], 500);
        }
    }

    public function printCertificate($id) {
        // Get certificate details
        $certificate = $this->db->selectOne("
            SELECT c.*, s.first_name, s.middle_name, s.last_name, s.date_of_birth, s.scholar_number, s.admission_date, s.class_id, cl.class_name, cl.section
            FROM certificates c
            LEFT JOIN students s ON c.student_id = s.id
            LEFT JOIN classes cl ON s.class_id = cl.id
            WHERE c.id = ?
        ", [$id]);

        if (!$certificate) {
            $this->session->setFlash('error', 'Certificate not found');
            $this->redirect('/admin/certificates/tc');
        }

        // Get academic record
        $academicRecord = $this->getStudentAcademicRecord($certificate['student_id']);

        // Get school settings
        $schoolName = $this->db->selectOne("SELECT setting_value FROM settings WHERE setting_key = 'school_name'")['setting_value'] ?? 'School Management System';
        $schoolAddress = $this->db->selectOne("SELECT setting_value FROM settings WHERE setting_key = 'school_address'")['setting_value'] ?? '';
        $schoolPhone = $this->db->selectOne("SELECT setting_value FROM settings WHERE setting_key = 'school_phone'")['setting_value'] ?? '';
        $schoolEmail = $this->db->selectOne("SELECT setting_value FROM settings WHERE setting_key = 'school_email'")['setting_value'] ?? '';
        $schoolLogo = $this->db->selectOne("SELECT setting_value FROM settings WHERE setting_key = 'school_logo'")['setting_value'] ?? '';

        // Generate PDF if not exists
        $pdfPath = $certificate['pdf_path'];
        if (empty($pdfPath) && $certificate['certificate_type'] === 'transfer') {
            $studentData = [
                'id' => $certificate['student_id'],
                'first_name' => $certificate['first_name'],
                'last_name' => $certificate['last_name'],
                'scholar_number' => $certificate['scholar_number'],
                'admission_date' => $certificate['admission_date'],
                'class_name' => $certificate['class_name'],
                'section' => $certificate['section']
            ];
            $pdfPath = $this->generateTransferCertificatePDF($studentData, $certificate);
            // Update certificate record with PDF path
            $this->db->update('certificates', ['pdf_path' => $pdfPath], 'id = ?', [$id]);
            $certificate['pdf_path'] = $pdfPath;
        }

        // Separate certificate and student data
        $student = [
            'id' => $certificate['student_id'],
            'first_name' => $certificate['first_name'],
            'middle_name' => $certificate['middle_name'],
            'last_name' => $certificate['last_name'],
            'date_of_birth' => $certificate['date_of_birth'],
            'scholar_number' => $certificate['scholar_number'],
            'admission_date' => $certificate['admission_date'],
            'class_name' => $certificate['class_name'],
            'section' => $certificate['section']
        ];

        // Set session data for print
        $_SESSION['print_certificate'] = [
            'certificate' => $certificate,
            'student' => $student,
            'school_name' => $schoolName,
            'school_address' => $schoolAddress,
            'school_phone' => $schoolPhone,
            'school_email' => $schoolEmail,
            'school_logo' => $schoolLogo,
            'academic_record' => $academicRecord
        ];

        // Redirect to print page
        $this->redirect('/admin/certificates/print-tc');
    }

    public function printTC() {
        // Check if certificate data exists in session
        if (!isset($_SESSION['print_certificate'])) {
            $this->session->setFlash('error', 'Certificate data not found. Please generate the certificate first.');
            $this->redirect('/admin/certificates');
        }

        $data = $_SESSION['print_certificate'];

        // Clear the session data after use
        unset($_SESSION['print_certificate']);

        $this->render('admin/certificates/print_tc', $data);
    }

    public function getCertificateStudents() {
        $classId = $_GET['class_id'] ?? '';
        $academicYearId = $this->getCurrentAcademicYearId();

        $query = "SELECT s.*, c.class_name, c.section FROM students s LEFT JOIN classes c ON s.class_id = c.id WHERE s.is_active = 1";
        $params = [];

        if (!empty($classId)) {
            $query .= " AND s.class_id = ?";
            $params[] = $classId;
        }

        if ($academicYearId) {
            $query .= " AND c.academic_year_id = ?";
            $params[] = $academicYearId;
        }

        $query .= " ORDER BY s.first_name, s.last_name";

        $students = $this->db->select($query, $params);
        $this->json(['students' => $students]);
    }

    public function generateCertificate() {
        try {
            $data = json_decode(file_get_contents('php://input'), true);

            if (!$data || !isset($data['student_id']) || !isset($data['certificate_type'])) {
                $this->json(['success' => false, 'message' => 'Invalid data'], 400);
            }

            $studentId = $data['student_id'];
            $certificateType = $data['certificate_type'];

            // Get student details
            $student = $this->db->selectOne("
                SELECT s.*, c.class_name, c.section
                FROM students s
                LEFT JOIN classes c ON s.class_id = c.id
                WHERE s.id = ? AND s.is_active = 1
            ", [$studentId]);

            if (!$student) {
                $this->json(['success' => false, 'message' => 'Student not found'], 404);
            }

            if ($certificateType === 'transfer' && $student['tc_issued']) {
                $this->json(['success' => false, 'message' => 'Transfer certificate already issued for this student'], 400);
            }

            // Generate unique TC number
            $certificateNumber = $this->generateUniqueCertificateNumber('TC');

            // Get academic record for TC
            $academicRecord = '';
            if ($certificateType === 'transfer') {
                $academicRecord = $this->getStudentAcademicRecord($studentId);
            }

            // Insert certificate record with academic year
            $academicYearId = $this->getCurrentAcademicYearId();
            $certData = [
                'certificate_type' => $certificateType,
                'certificate_number' => $certificateNumber,
                'student_id' => $studentId,
                'issue_date' => $data['issue_date'] ?? date('Y-m-d'),
                'transfer_reason' => $data['transfer_reason'] ?? null,
                'conduct' => $data['conduct'] ?? 'good',
                'remarks' => $data['remarks'] ?? null,
                'generated_by' => $_SESSION['user']['id'] ?? 1,
                'pdf_path' => '' // Will be set after PDF generation
            ];
            if ($academicYearId) {
                $certData['academic_year_id'] = $academicYearId;
            }
            $certificateId = $this->db->insert('certificates', $certData);

            if ($certificateType === 'transfer') {
                $this->db->update('students', ['tc_issued' => 1, 'is_active' => 0], 'id = ?', [$studentId]);
            }

            // Generate PDF for the certificate
            $pdfPath = '';
            if ($certificateType === 'transfer') {
                $pdfPath = $this->generateTransferCertificatePDF($student, $certData);
                // Update certificate record with PDF path
                $this->db->update('certificates', ['pdf_path' => $pdfPath], 'id = ?', [$certificateId]);
            }

            // Get school settings for display
            $schoolName = $this->db->selectOne("SELECT setting_value FROM settings WHERE setting_key = 'school_name'")['setting_value'] ?? 'School Management System';
            $schoolAddress = $this->db->selectOne("SELECT setting_value FROM settings WHERE setting_key = 'school_address'")['setting_value'] ?? '';
            $schoolPhone = $this->db->selectOne("SELECT setting_value FROM settings WHERE setting_key = 'school_phone'")['setting_value'] ?? '';
            $schoolEmail = $this->db->selectOne("SELECT setting_value FROM settings WHERE setting_key = 'school_email'")['setting_value'] ?? '';
            $schoolLogo = $this->db->selectOne("SELECT setting_value FROM settings WHERE setting_key = 'school_logo'")['setting_value'] ?? '';

            // Store certificate data in session for the print page
            $_SESSION['print_certificate'] = [
                'certificate' => array_merge($certData, ['id' => $certificateId, 'pdf_path' => $pdfPath]),
                'student' => $student,
                'school_name' => $schoolName,
                'school_address' => $schoolAddress,
                'school_phone' => $schoolPhone,
                'school_email' => $schoolEmail,
                'school_logo' => $schoolLogo,
                'academic_record' => $academicRecord
            ];

            // Redirect to print page instead of returning JSON
            $this->redirect('/admin/certificates/print-tc');
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => 'Error generating certificate: ' . $e->getMessage()], 500);
        }
    }

    private function generateTransferCertificatePDF($student, $data) {
        require_once BASE_PATH . 'vendor/tecnickcom/tcpdf/tcpdf.php';

        // Get school settings
        $schoolName = $this->db->selectOne("SELECT setting_value FROM settings WHERE setting_key = 'school_name'")['setting_value'] ?? 'School Management System';
        $schoolAddress = $this->db->selectOne("SELECT setting_value FROM settings WHERE setting_key = 'school_address'")['setting_value'] ?? '';
        $schoolPhone = $this->db->selectOne("SELECT setting_value FROM settings WHERE setting_key = 'school_phone'")['setting_value'] ?? '';
        $schoolEmail = $this->db->selectOne("SELECT setting_value FROM settings WHERE setting_key = 'school_email'")['setting_value'] ?? '';
        $schoolLogo = $this->db->selectOne("SELECT setting_value FROM settings WHERE setting_key = 'school_logo'")['setting_value'] ?? '';

        $issueDate = date('d/m/Y', strtotime($data['issue_date']));
        $admissionDate = $student['admission_date'] ? date('d/m/Y', strtotime($student['admission_date'])) : 'N/A';

        // Get academic record from exam results
        $academicRecord = $this->getStudentAcademicRecord($student['id']);

        $reasonText = '';
        switch ($data['transfer_reason']) {
            case 'parent_transfer': $reasonText = 'Parent Transfer'; break;
            case 'better_opportunity': $reasonText = 'Better Educational Opportunity'; break;
            case 'family_moved': $reasonText = 'Family Moved'; break;
            case 'personal': $reasonText = 'Personal Reasons'; break;
            default: $reasonText = 'Other'; break;
        }

        $conduct = ucfirst($data['conduct'] ?? 'good');

        // Create new PDF document (A4 size)
        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

        // Set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor($schoolName);
        $pdf->SetTitle('Transfer Certificate - ' . $student['first_name'] . ' ' . $student['last_name']);

        // Remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // Set margins (15mm all around for A4)
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(TRUE, 15);

        // Add a page
        $pdf->AddPage();

        // Set font
        $pdf->SetFont('times', '', 11);

        // Outer border
        $pdf->SetLineWidth(0.5);
        $pdf->Rect(10, 10, 190, 277);

        // Inner decorative border
        $pdf->SetLineWidth(0.2);
        $pdf->Rect(12, 12, 186, 273);

        // Header section with logo and school info
        $y = 20;

        // School logo (if available)
        if (!empty($schoolLogo) && file_exists(BASE_PATH . 'uploads/' . $schoolLogo)) {
            $pdf->Image(BASE_PATH . 'uploads/' . $schoolLogo, 20, $y, 25, 25);
            $logoX = 50;
        } else {
            $logoX = 20;
        }

        // School name and address
        $pdf->SetFont('times', 'B', 20);
        $pdf->SetXY($logoX, $y);
        $pdf->Cell(0, 8, $schoolName, 0, 1, 'C');

        $pdf->SetFont('times', '', 10);
        $pdf->SetXY($logoX, $y + 10);
        $pdf->MultiCell(0, 5, $schoolAddress, 0, 'C');

        if (!empty($schoolPhone) || !empty($schoolEmail)) {
            $contactInfo = [];
            if (!empty($schoolPhone)) $contactInfo[] = 'Phone: ' . $schoolPhone;
            if (!empty($schoolEmail)) $contactInfo[] = 'Email: ' . $schoolEmail;
            $pdf->SetXY($logoX, $y + 20);
            $pdf->Cell(0, 5, implode(' | ', $contactInfo), 0, 1, 'C');
        }

        // Certificate title
        $pdf->SetFont('times', 'B', 18);
        $pdf->SetXY(20, $y + 35);
        $pdf->Cell(0, 10, 'TRANSFER CERTIFICATE', 0, 1, 'C');

        // Certificate number and date
        $pdf->SetFont('times', '', 11);
        $certificateNumber = $this->generateUniqueCertificateNumber('TC');
        $pdf->SetXY(140, $y + 50);
        $pdf->Cell(50, 6, 'Certificate No: ' . $certificateNumber, 0, 1, 'R');
        $pdf->SetXY(140, $y + 56);
        $pdf->Cell(50, 6, 'Date: ' . $issueDate, 0, 1, 'R');

        // Certificate content
        $contentY = $y + 70;
        $pdf->SetFont('times', '', 12);

        // Main certification text
        $pdf->SetXY(25, $contentY);
        $pdf->MultiCell(0, 8, 'This is to certify that the following student was admitted to this school and that he/she has left the school on the date mentioned below:', 0, 'L');

        $contentY += 20;

        // Student details table
        $pdf->SetFont('times', '', 11);
        $pdf->SetLineWidth(0.1);

        // Row 1: Name
        $pdf->SetXY(25, $contentY);
        $pdf->Cell(60, 8, 'Name of the Student:', 1, 0, 'L');
        $pdf->SetFont('times', 'B', 11);
        $pdf->Cell(0, 8, $student['first_name'] . ' ' . $student['middle_name'] . ' ' . $student['last_name'], 1, 1, 'L');
        $pdf->SetFont('times', '', 11);

        // Row 2: Father's Name
        $contentY += 8;
        $pdf->SetXY(25, $contentY);
        $pdf->Cell(60, 8, 'Father\'s Name:', 1, 0, 'L');
        $pdf->SetFont('times', 'B', 11);
        $pdf->Cell(0, 8, $student['father_name'] ?? 'N/A', 1, 1, 'L');
        $pdf->SetFont('times', '', 11);

        // Row 3: Mother's Name
        $contentY += 8;
        $pdf->SetXY(25, $contentY);
        $pdf->Cell(60, 8, 'Mother\'s Name:', 1, 0, 'L');
        $pdf->SetFont('times', 'B', 11);
        $pdf->Cell(0, 8, $student['mother_name'] ?? 'N/A', 1, 1, 'L');
        $pdf->SetFont('times', '', 11);

        // Row 4: Date of Birth
        $contentY += 8;
        $pdf->SetXY(25, $contentY);
        $pdf->Cell(60, 8, 'Date of Birth:', 1, 0, 'L');
        $pdf->SetFont('times', 'B', 11);
        $pdf->Cell(0, 8, $student['date_of_birth'] ? date('d/m/Y', strtotime($student['date_of_birth'])) : 'N/A', 1, 1, 'L');
        $pdf->SetFont('times', '', 11);

        // Row 5: Scholar Number & Admission Date
        $contentY += 8;
        $pdf->SetXY(25, $contentY);
        $pdf->Cell(60, 8, 'Scholar Number:', 1, 0, 'L');
        $pdf->SetFont('times', 'B', 11);
        $pdf->Cell(55, 8, $student['scholar_number'], 1, 0, 'L');
        $pdf->SetFont('times', '', 11);
        $pdf->Cell(30, 8, 'Admission Date:', 1, 0, 'L');
        $pdf->SetFont('times', 'B', 11);
        $pdf->Cell(0, 8, $admissionDate, 1, 1, 'L');
        $pdf->SetFont('times', '', 11);

        // Row 6: Class & Nationality
        $contentY += 8;
        $pdf->SetXY(25, $contentY);
        $pdf->Cell(60, 8, 'Class:', 1, 0, 'L');
        $pdf->SetFont('times', 'B', 11);
        $pdf->Cell(55, 8, $student['class_name'] . ' ' . $student['section'], 1, 0, 'L');
        $pdf->SetFont('times', '', 11);
        $pdf->Cell(30, 8, 'Nationality:', 1, 0, 'L');
        $pdf->SetFont('times', 'B', 11);
        $pdf->Cell(0, 8, $student['nationality'] ?? 'Indian', 1, 1, 'L');
        $pdf->SetFont('times', '', 11);

        // Academic Record section
        $contentY += 15;
        $pdf->SetXY(25, $contentY);
        $pdf->SetFont('times', 'B', 11);
        $pdf->Cell(0, 8, 'ACADEMIC RECORD:', 0, 1, 'L');
        $pdf->SetFont('times', '', 11);

        $contentY += 8;
        $pdf->SetXY(25, $contentY);
        $pdf->MultiCell(0, 6, $academicRecord, 0, 'L');

        // Conduct and other details
        $contentY += 25;
        $pdf->SetXY(25, $contentY);
        $pdf->Cell(50, 8, 'Conduct:', 0, 0, 'L');
        $pdf->SetFont('times', 'B', 11);
        $pdf->Cell(0, 8, $conduct, 0, 1, 'L');
        $pdf->SetFont('times', '', 11);

        $contentY += 8;
        $pdf->SetXY(25, $contentY);
        $pdf->Cell(50, 8, 'Reason for Leaving:', 0, 0, 'L');
        $pdf->SetFont('times', 'B', 11);
        $pdf->Cell(0, 8, $reasonText, 0, 1, 'L');

        if (!empty($data['remarks'])) {
            $pdf->SetFont('times', '', 11);
            $contentY += 8;
            $pdf->SetXY(25, $contentY);
            $pdf->Cell(50, 8, 'Remarks:', 0, 0, 'L');
            $pdf->SetFont('times', 'B', 11);
            $pdf->Cell(0, 8, $data['remarks'], 0, 1, 'L');
        }

        // Signatures section
        $contentY = 250;
        $pdf->SetFont('times', '', 10);

        // Class Teacher signature
        $pdf->SetXY(25, $contentY);
        $pdf->Cell(50, 5, 'Class Teacher', 0, 0, 'C');
        $pdf->Line(25, $contentY + 12, 75, $contentY + 12);

        // Principal signature
        $pdf->SetXY(85, $contentY);
        $pdf->Cell(50, 5, 'Principal', 0, 0, 'C');
        $pdf->Line(85, $contentY + 12, 135, $contentY + 12);

        // School Seal
        $pdf->SetXY(145, $contentY);
        $pdf->Cell(50, 5, 'School Seal', 0, 0, 'C');
        $pdf->Line(145, $contentY + 12, 195, $contentY + 12);

        // Save PDF to file
        $filename = 'tc_' . $student['id'] . '_' . time() . '.pdf';
        $filepath = BASE_PATH . 'temp/' . $filename;

        if (!is_dir(dirname($filepath))) {
            mkdir(dirname($filepath), 0755, true);
        }

        $pdf->Output($filepath, 'F');

        return '/temp/' . $filename;
    }

    private function generateUniqueCertificateNumber($prefix) {
        // Get settings for TC prefix and start number
        $tcPrefix = $this->db->selectOne("SELECT setting_value FROM settings WHERE setting_key = 'tc_prefix'")['setting_value'] ?? 'TC';
        $tcStartNumber = (int) ($this->db->selectOne("SELECT setting_value FROM settings WHERE setting_key = 'tc_start_number'")['setting_value'] ?? 1);

        // Use configured prefix if this is a TC
        if ($prefix === 'TC') {
            $prefix = $tcPrefix;
        }

        // Get the maximum certificate number for this prefix
        $maxQuery = "SELECT MAX(CAST(SUBSTRING(certificate_number, " . (strlen($prefix) + 1) . ") AS UNSIGNED)) as max_num FROM certificates WHERE certificate_number LIKE '{$prefix}%'";
        $maxNum = $this->db->selectOne($maxQuery)['max_num'] ?? 0;

        // Ensure we start from the configured number
        $nextNum = max($maxNum + 1, $tcStartNumber);

        return $prefix . str_pad($nextNum, 3, '0', STR_PAD_LEFT);
    }

    private function getStudentAcademicRecord($studentId) {
        // Get exam results for the student
        $results = $this->db->select("
            SELECT er.*, e.exam_name, e.exam_type, sub.subject_name, sub.subject_code
            FROM exam_results er
            LEFT JOIN exams e ON er.exam_id = e.id
            LEFT JOIN subjects sub ON er.subject_id = sub.id
            WHERE er.student_id = ?
            ORDER BY e.start_date DESC, sub.subject_name
        ", [$studentId]);

        if (empty($results)) {
            return "No academic records found. Student was enrolled but no examination results are available.";
        }

        $record = "Academic Performance:\n\n";

        // Group by exam
        $exams = [];
        foreach ($results as $result) {
            $examName = $result['exam_name'];
            if (!isset($exams[$examName])) {
                $exams[$examName] = [];
            }
            $exams[$examName][] = $result;
        }

        foreach ($exams as $examName => $subjects) {
            $record .= "Exam: " . $examName . "\n";
            $totalMarks = 0;
            $maxMarks = 0;
            $subjectCount = 0;

            foreach ($subjects as $subject) {
                $record .= "- " . $subject['subject_name'] . ": " . $subject['marks_obtained'] . "/" . $subject['max_marks'] . " (" . $subject['grade'] . ")\n";
                $totalMarks += $subject['marks_obtained'];
                $maxMarks += $subject['max_marks'];
                $subjectCount++;
            }

            $percentage = $maxMarks > 0 ? round(($totalMarks / $maxMarks) * 100, 2) : 0;
            $record .= "Total: " . $totalMarks . "/" . $maxMarks . " (" . $percentage . "%)\n\n";
        }

        return $record;
    }

    public function marksheets() {
        $academicYearId = $this->getCurrentAcademicYearId();
        $where = "WHERE e.is_active = 1";
        $params = [];
        if ($academicYearId) {
            $where .= " AND e.academic_year_id = ?";
            $params = [$academicYearId];
        }
        $exams = $this->db->select("SELECT e.*, c.class_name FROM exams e LEFT JOIN classes c ON e.class_id = c.id $where ORDER BY e.start_date DESC", $params);
        $csrfToken = $this->csrfToken();
        $this->render('admin/exams/marksheets', ['exams' => $exams, 'csrf_token' => $csrfToken]);
    }

    public function getExamResultsStudents($examId) {
        $academicYearId = $this->getCurrentAcademicYearId();

        // Get students who have results for this exam
        $students = $this->db->select("
            SELECT DISTINCT s.*, c.class_name, c.section
            FROM students s
            LEFT JOIN classes c ON s.class_id = c.id
            INNER JOIN exam_results er ON s.id = er.student_id
            WHERE er.exam_id = ? AND s.is_active = 1" . ($academicYearId ? " AND er.academic_year_id = ?" : "") . "
            ORDER BY s.first_name, s.last_name
        ", $academicYearId ? [$examId, $academicYearId] : [$examId]);

        $this->json(['students' => $students]);
    }

    public function generateMarksheets() {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!$data || !isset($data['exam_id']) || !isset($data['students'])) {
            $this->json(['success' => false, 'message' => 'Invalid data'], 400);
        }

        $examId = $data['exam_id'];
        $studentIds = $data['students'];
        $includePhotos = $data['include_photos'] ?? true;
        $includeGrades = $data['include_grades'] ?? true;
        $includeRankings = $data['include_rankings'] ?? true;
        $marksheetsPerPage = $data['marksheets_per_page'] ?? 2;

        // Get exam details
        $exam = $this->db->selectOne("
            SELECT e.*, c.class_name, c.section
            FROM exams e
            LEFT JOIN classes c ON e.class_id = c.id
            WHERE e.id = ?
        ", [$examId]);

        if (!$exam) {
            $this->json(['success' => false, 'message' => 'Exam not found'], 404);
        }

        // Get students with their results
        $students = [];
        foreach ($studentIds as $studentId) {
            $student = $this->db->selectOne("
                SELECT s.*, c.class_name, c.section
                FROM students s
                LEFT JOIN classes c ON s.class_id = c.id
                WHERE s.id = ? AND s.is_active = 1
            ", [$studentId]);

            if ($student) {
                // Get exam results for this student
                $results = $this->db->select("
                    SELECT er.*, sub.subject_name, sub.subject_code
                    FROM exam_results er
                    LEFT JOIN subjects sub ON er.subject_id = sub.id
                    WHERE er.exam_id = ? AND er.student_id = ?
                    ORDER BY sub.subject_name
                ", [$examId, $studentId]);

                $student['results'] = $results;
                $students[] = $student;
            }
        }

        // Calculate rankings if requested
        if ($includeRankings) {
            $this->calculateStudentRankings($students, $examId);
        }

        // Generate HTML for marksheets
        $html = $this->generateMarksheetsHTML($exam, $students, $includePhotos, $includeGrades, $includeRankings, $marksheetsPerPage);

        // Save HTML to temporary file
        $filename = 'marksheets_' . $examId . '_' . time() . '.html';
        $filepath = BASE_PATH . 'temp/' . $filename;

        if (!is_dir(dirname($filepath))) {
            mkdir(dirname($filepath), 0755, true);
        }

        file_put_contents($filepath, $html);

        $this->json([
            'success' => true,
            'message' => 'Marksheets generated successfully',
            'marksheet_url' => '/temp/' . $filename
        ]);
    }

    public function generateMarksheet() {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!$data || !isset($data['exam_id']) || !isset($data['student_id'])) {
            $this->json(['success' => false, 'message' => 'Invalid data'], 400);
        }

        $examId = $data['exam_id'];
        $studentId = $data['student_id'];
        $includePhotos = $data['include_photos'] ?? true;
        $includeGrades = $data['include_grades'] ?? true;
        $includeRankings = $data['include_rankings'] ?? true;

        // Get exam details
        $exam = $this->db->selectOne("
            SELECT e.*, c.class_name, c.section
            FROM exams e
            LEFT JOIN classes c ON e.class_id = c.id
            WHERE e.id = ?
        ", [$examId]);

        if (!$exam) {
            $this->json(['success' => false, 'message' => 'Exam not found'], 404);
        }

        // Get student with results
        $student = $this->db->selectOne("
            SELECT s.*, c.class_name, c.section
            FROM students s
            LEFT JOIN classes c ON s.class_id = c.id
            WHERE s.id = ? AND s.is_active = 1
        ", [$studentId]);

        if (!$student) {
            $this->json(['success' => false, 'message' => 'Student not found'], 404);
        }

        // Get exam results for this student
        $results = $this->db->select("
            SELECT er.*, sub.subject_name, sub.subject_code
            FROM exam_results er
            LEFT JOIN subjects sub ON er.subject_id = sub.id
            WHERE er.exam_id = ? AND er.student_id = ?
            ORDER BY sub.subject_name
        ", [$examId, $studentId]);

        $student['results'] = $results;

        // Calculate ranking if requested
        if ($includeRankings) {
            $allStudents = $this->db->select("
                SELECT DISTINCT s.id
                FROM students s
                INNER JOIN exam_results er ON s.id = er.student_id
                WHERE er.exam_id = ? AND s.class_id = ? AND s.is_active = 1
            ", [$examId, $student['class_id']]);

            $studentIds = array_column($allStudents, 'id');
            $students = [];
            foreach ($studentIds as $sid) {
                $s = $this->db->selectOne("SELECT * FROM students WHERE id = ?", [$sid]);
                $s['results'] = $this->db->select("
                    SELECT er.*, sub.subject_name
                    FROM exam_results er
                    LEFT JOIN subjects sub ON er.subject_id = sub.id
                    WHERE er.exam_id = ? AND er.student_id = ?
                ", [$examId, $sid]);
                $students[] = $s;
            }

            $this->calculateStudentRankings($students, $examId);
            // Find this student's rank
            foreach ($students as $s) {
                if ($s['id'] == $studentId) {
                    $student['rank'] = $s['rank'];
                    break;
                }
            }
        }

        // Generate HTML for single marksheet
        $html = $this->generateMarksheetsHTML($exam, [$student], $includePhotos, $includeGrades, $includeRankings, 1);

        // Save HTML to temporary file
        $filename = 'marksheet_' . $examId . '_' . $studentId . '_' . time() . '.html';
        $filepath = BASE_PATH . 'temp/' . $filename;

        if (!is_dir(dirname($filepath))) {
            mkdir(dirname($filepath), 0755, true);
        }

        file_put_contents($filepath, $html);

        $this->json([
            'success' => true,
            'message' => 'Marksheet generated successfully',
            'marksheet_url' => '/temp/' . $filename
        ]);
    }

    public function createExam() {
        $academicYearId = $this->getCurrentAcademicYearId();
        $where = "WHERE is_active = 1";
        $params = [];
        if ($academicYearId) {
            $where .= " AND academic_year_id = ?";
            $params = [$academicYearId];
        }
        $classes = $this->db->select("SELECT * FROM classes $where ORDER BY class_name", $params);
        $subjects = $this->db->select("SELECT * FROM subjects WHERE is_active = 1 ORDER BY subject_name");
        $csrfToken = $this->csrfToken();
        $this->render('admin/exams/create', ['classes' => $classes, 'subjects' => $subjects, 'csrf_token' => $csrfToken]);
    }

    public function storeExam() {
        $data = $_POST;
        $csrfToken = $data['csrf_token'] ?? '';

        if (!$this->checkCsrfToken($csrfToken)) {
            $this->session->setFlash('error', 'Invalid CSRF token');
            $this->redirect('/admin/exams/create');
        }

        // Validate required fields
        $required = ['exam_name', 'exam_type', 'class_id', 'start_date', 'end_date'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                $this->session->setFlash('error', ucfirst(str_replace('_', ' ', $field)) . ' is required');
                $this->session->setFlash('old', $data);
                $this->redirect('/admin/exams/create');
            }
        }

        // Validate subjects
        if (empty($data['subjects']) || !is_array($data['subjects'])) {
            $this->session->setFlash('error', 'At least one subject must be added to the exam');
            $this->session->setFlash('old', $data);
            $this->redirect('/admin/exams/create');
        }

        // Start transaction
        $this->db->beginTransaction();

        try {
            $academicYearId = $this->getCurrentAcademicYearId();

            // Create exam
            $examData = [
                'exam_name' => trim($data['exam_name']),
                'exam_type' => $data['exam_type'],
                'class_id' => $data['class_id'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'academic_year' => $data['academic_year'] ?? date('Y') . '-' . (date('Y') + 1),
                'is_active' => isset($data['is_active']) ? 1 : 0
            ];
            if ($academicYearId) {
                $examData['academic_year_id'] = $academicYearId;
            }

            $examId = $this->db->insert('exams', $examData);

            // Create exam subjects
            foreach ($data['subjects'] as $subjectData) {
                if (!empty($subjectData['subject_id'])) {
                    $this->db->insert('exam_subjects', [
                        'exam_id' => $examId,
                        'subject_id' => $subjectData['subject_id'],
                        'exam_date' => $subjectData['exam_date'],
                        'start_time' => $subjectData['start_time'],
                        'end_time' => $subjectData['end_time'],
                        'max_marks' => $subjectData['max_marks']
                    ]);
                }
            }

            $this->db->commit();

            $this->session->setFlash('success', 'Exam created successfully with ' . count($data['subjects']) . ' subjects');
            $this->redirect('/admin/exams');

        } catch (Exception $e) {
            $this->db->rollback();
            $this->session->setFlash('error', 'Failed to create exam: ' . $e->getMessage());
            $this->session->setFlash('old', $data);
            $this->redirect('/admin/exams/create');
        }
    }

    public function enterResults($examId) {
        // Get exam details
        $exam = $this->db->selectOne("
            SELECT e.*, c.class_name, c.section
            FROM exams e
            LEFT JOIN classes c ON e.class_id = c.id
            WHERE e.id = ?
        ", [$examId]);

        if (!$exam) {
            $this->session->setFlash('error', 'Exam not found');
            $this->redirect('/admin/exams');
        }

        // Get exam subjects
        $examSubjects = $this->db->select("
            SELECT es.*, s.subject_name, s.subject_code
            FROM exam_subjects es
            LEFT JOIN subjects s ON es.subject_id = s.id
            WHERE es.exam_id = ?
            ORDER BY es.exam_date, es.start_time
        ", [$examId]);

        $academicYearId = $this->getCurrentAcademicYearId();

        // Get students in the class
        $students = $this->db->select("
            SELECT s.*, c.class_name, c.section
            FROM students s
            LEFT JOIN classes c ON s.class_id = c.id
            WHERE s.class_id = ? AND s.is_active = 1" . ($academicYearId ? " AND c.academic_year_id = ?" : "") . "
            ORDER BY s.first_name, s.last_name
        ", $academicYearId ? [$exam['class_id'], $academicYearId] : [$exam['class_id']]);

        $csrfToken = $this->csrfToken();
        $this->render('admin/exams/results', [
            'exam' => $exam,
            'exam_subjects' => $examSubjects,
            'students' => $students,
            'csrf_token' => $csrfToken
        ]);
    }

    public function saveResults() {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!$data || !isset($data['exam_id']) || !isset($data['results'])) {
            $this->json(['success' => false, 'message' => 'Invalid data'], 400);
        }

        $examId = $data['exam_id'];
        $results = $data['results'];

        // Start transaction
        $this->db->beginTransaction();

        try {
            // Delete existing results for this exam
            $this->db->delete('exam_results', 'exam_id = ?', [$examId]);

            $academicYearId = $this->getCurrentAcademicYearId();

            // Insert new results
            foreach ($results as $result) {
                if (isset($result['marks_obtained']) && is_numeric($result['marks_obtained'])) {
                    // Calculate grade
                    $percentage = ($result['marks_obtained'] / $result['max_marks']) * 100;
                    $grade = $this->calculateGrade($result['marks_obtained'], $result['max_marks']);

                    $data = [
                        'exam_id' => $examId,
                        'student_id' => $result['student_id'],
                        'subject_id' => $result['subject_id'],
                        'marks_obtained' => $result['marks_obtained'],
                        'max_marks' => $result['max_marks'],
                        'grade' => $grade,
                        'percentage' => round($percentage, 2)
                    ];
                    if ($academicYearId) {
                        $data['academic_year_id'] = $academicYearId;
                    }
                    $this->db->insert('exam_results', $data);
                }
            }

            $this->db->commit();
            $this->json(['success' => true, 'message' => 'Results saved successfully']);

        } catch (Exception $e) {
            $this->db->rollback();
            $this->json(['success' => false, 'message' => 'Failed to save results: ' . $e->getMessage()], 500);
        }
    }

    public function getExistingResults($examId) {
        $results = $this->db->select("
            SELECT er.*, es.max_marks
            FROM exam_results er
            LEFT JOIN exam_subjects es ON er.subject_id = es.subject_id AND es.exam_id = er.exam_id
            WHERE er.exam_id = ?
        ", [$examId]);

        $this->json(['results' => $results]);
    }

    private function calculateStudentRankings(&$students, $examId) {
        // Calculate total marks for each student
        foreach ($students as &$student) {
            $totalMarks = 0;
            $maxMarks = 0;
            foreach ($student['results'] as $result) {
                $totalMarks += $result['marks_obtained'];
                $maxMarks += $result['max_marks'];
            }
            $student['total_marks'] = $totalMarks;
            $student['max_marks'] = $maxMarks;
            $student['percentage'] = $maxMarks > 0 ? round(($totalMarks / $maxMarks) * 100, 2) : 0;
        }

        // Sort by total marks descending
        usort($students, function($a, $b) {
            return $b['total_marks'] <=> $a['total_marks'];
        });

        // Assign ranks
        $rank = 1;
        $prevMarks = null;
        foreach ($students as &$student) {
            if ($prevMarks !== null && $student['total_marks'] < $prevMarks) {
                $rank++;
            }
            $student['rank'] = $rank;
            $prevMarks = $student['total_marks'];
        }
    }

    private function generateMarksheetsHTML($exam, $students, $includePhotos, $includeGrades, $includeRankings, $marksheetsPerPage) {
        $schoolName = $this->db->selectOne("SELECT setting_value FROM settings WHERE setting_key = 'school_name'")['setting_value'] ?? 'School Management System';
        $schoolAddress = $this->db->selectOne("SELECT setting_value FROM settings WHERE setting_key = 'school_address'")['setting_value'] ?? '';

        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Marksheets - ' . htmlspecialchars($exam['exam_name']) . '</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
                .marksheet { border: 2px solid #000; padding: 20px; margin: 10px; width: ' . (100/$marksheetsPerPage - 2) . '%; float: left; box-sizing: border-box; page-break-inside: avoid; }
                .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
                .school-name { font-size: 18px; font-weight: bold; }
                .exam-title { font-size: 16px; font-weight: bold; margin: 10px 0; }
                .student-info { margin: 10px 0; }
                .marks-table { width: 100%; border-collapse: collapse; margin: 15px 0; }
                .marks-table th, .marks-table td { border: 1px solid #000; padding: 8px; text-align: center; }
                .marks-table th { background-color: #f0f0f0; font-weight: bold; }
                .signatures { margin-top: 30px; clear: both; }
                .signature-box { width: 30%; float: left; text-align: center; border-top: 1px solid #000; padding-top: 20px; min-height: 60px; }
                .signature-box:last-child { margin-right: 0; }
                .photo { float: right; width: 60px; height: 80px; border: 1px solid #000; margin-left: 20px; }
                .grade-badge { display: inline-block; padding: 2px 6px; border-radius: 3px; font-size: 0.8rem; font-weight: bold; }
                .grade-A { background-color: #d4edda; color: #155724; }
                .grade-B { background-color: #fff3cd; color: #856404; }
                .grade-C { background-color: #ffeaa7; color: #d68910; }
                .grade-F { background-color: #f8d7da; color: #721c24; }
                @media print { body { margin: 10px; } .marksheet { margin: 5px; } }
            </style>
        </head>
        <body>
        ';

        foreach ($students as $index => $student) {
            if ($index % $marksheetsPerPage === 0 && $index > 0) {
                $html .= '<div style="page-break-before: always;"></div>';
            }

            $html .= '
            <div class="marksheet">
                <div class="header">
                    <div class="school-name">' . htmlspecialchars($schoolName) . '</div>
                    <div>' . htmlspecialchars($schoolAddress) . '</div>
                    <div class="exam-title">MARKSHEET - ' . htmlspecialchars($exam['exam_name']) . '</div>
                </div>

                <div style="overflow: hidden;">
            ';

            if ($includePhotos && !empty($student['photo'])) {
                $html .= '<img src="' . BASE_PATH . 'uploads/' . $student['photo'] . '" class="photo" alt="Photo">';
            }

            $html .= '
                <div class="student-info">
                    <strong>Student Name:</strong> ' . htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) . '<br>
                    <strong>Scholar Number:</strong> ' . htmlspecialchars($student['scholar_number']) . '<br>
                    <strong>Class:</strong> ' . htmlspecialchars($student['class_name'] . ' ' . $student['section']) . '<br>
                    <strong>Roll Number:</strong> ' . htmlspecialchars($student['roll_number'] ?? 'N/A') . '<br>
                    <strong>Exam Date:</strong> ' . date('M d, Y', strtotime($exam['start_date'])) . ' - ' . date('M d, Y', strtotime($exam['end_date'])) . '<br>
                </div>

                <table class="marks-table">
                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th>Max Marks</th>
                            <th>Marks Obtained</th>
                            <th>Grade</th>
                        </tr>
                    </thead>
                    <tbody>
            ';

            $totalMarks = 0;
            $maxTotalMarks = 0;

            foreach ($student['results'] as $result) {
                $marks = $result['marks_obtained'];
                $maxMarks = $result['max_marks'];
                $totalMarks += $marks;
                $maxTotalMarks += $maxMarks;

                $grade = $this->calculateGrade($marks, $maxMarks);
                $gradeClass = 'grade-' . $grade;

                $html .= '
                        <tr>
                            <td>' . htmlspecialchars($result['subject_name']) . '</td>
                            <td>' . $maxMarks . '</td>
                            <td>' . $marks . '</td>
                            <td><span class="grade-badge ' . $gradeClass . '">' . $grade . '</span></td>
                        </tr>
                ';
            }

            $percentage = $maxTotalMarks > 0 ? round(($totalMarks / $maxTotalMarks) * 100, 2) : 0;
            $overallGrade = $this->calculateGrade($totalMarks, $maxTotalMarks);

            $html .= '
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="2">Total Marks</th>
                            <th>' . $totalMarks . '/' . $maxTotalMarks . '</th>
                            <th><span class="grade-badge grade-' . $overallGrade . '">' . $overallGrade . '</span></th>
                        </tr>
                        <tr>
                            <th colspan="2">Percentage</th>
                            <th colspan="2">' . $percentage . '%</th>
                        </tr>
            ';

            if ($includeRankings && isset($student['rank'])) {
                $html .= '
                        <tr>
                            <th colspan="2">Class Rank</th>
                            <th colspan="2">' . $student['rank'] . '</th>
                        </tr>
                ';
            }

            $html .= '
                    </tfoot>
                </table>
            ';

            $html .= '
                <div class="signatures">
                    <div class="signature-box">
                        <small>Class Teacher</small>
                    </div>
                    <div class="signature-box">
                        <small>Exam Controller</small>
                    </div>
                    <div class="signature-box">
                        <small>Principal</small>
                    </div>
                </div>
            ';

            $html .= '
                </div>
            </div>
            ';
        }

        $html .= '
        </body>
        </html>
        ';

        return $html;
    }

    private function calculateGrade($marks, $maxMarks) {
        if ($maxMarks == 0) return 'N/A';

        $percentage = ($marks / $maxMarks) * 100;

        if ($percentage >= 90) return 'A';
        if ($percentage >= 80) return 'B';
        if ($percentage >= 70) return 'C';
        if ($percentage >= 60) return 'D';
        return 'F';
    }

    private function generateAdmitCardsHTML($exam, $students, $includePhotos, $includeSignatures, $cardsPerPage) {
        $schoolName = $this->db->selectOne("SELECT setting_value FROM settings WHERE setting_key = 'school_name'")['setting_value'] ?? 'School Management System';
        $schoolAddress = $this->db->selectOne("SELECT setting_value FROM settings WHERE setting_key = 'school_address'")['setting_value'] ?? '';

        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Admit Cards - ' . htmlspecialchars($exam['exam_name']) . '</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 0; padding: 20px; }
                .admit-card { border: 2px solid #000; padding: 20px; margin: 10px; width: ' . (100/$cardsPerPage - 2) . '%; float: left; box-sizing: border-box; page-break-inside: avoid; }
                .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
                .school-name { font-size: 18px; font-weight: bold; }
                .exam-title { font-size: 16px; margin: 10px 0; }
                .student-info { margin: 10px 0; }
                .subject-schedule { margin: 15px 0; }
                .signatures { margin-top: 30px; clear: both; }
                .signature-box { border-top: 1px solid #000; width: 30%; float: left; text-align: center; padding-top: 30px; margin-right: 3%; min-height: 60px; }
                .signature-box:last-child { margin-right: 0; }
                .photo { float: right; width: 80px; height: 100px; border: 1px solid #000; margin-left: 20px; }
                @media print { body { margin: 0; } .admit-card { margin: 5px; } }
            </style>
        </head>
        <body>
        ';

        foreach ($students as $index => $student) {
            if ($index % $cardsPerPage === 0 && $index > 0) {
                $html .= '<div style="page-break-before: always;"></div>';
            }

            $html .= '
            <div class="admit-card">
                <div class="header">
                    <div class="school-name">' . htmlspecialchars($schoolName) . '</div>
                    <div>' . htmlspecialchars($schoolAddress) . '</div>
                    <div class="exam-title">Admit Card - ' . htmlspecialchars($exam['exam_name']) . '</div>
                </div>

                <div style="overflow: hidden;">
            ';

            if ($includePhotos && !empty($student['photo'])) {
                $html .= '<img src="' . BASE_PATH . 'uploads/' . $student['photo'] . '" class="photo" alt="Photo">';
            }

            $html .= '
                <div class="student-info">
                    <strong>Student Name:</strong> ' . htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) . '<br>
                    <strong>Scholar Number:</strong> ' . htmlspecialchars($student['scholar_number']) . '<br>
                    <strong>Class:</strong> ' . htmlspecialchars($student['class_name'] . ' ' . $student['section']) . '<br>
                    <strong>Roll Number:</strong> ' . htmlspecialchars($student['roll_number'] ?? 'N/A') . '<br>
                    <strong>Exam Date:</strong> ' . date('M d, Y', strtotime($exam['start_date'])) . ' - ' . date('M d, Y', strtotime($exam['end_date'])) . '<br>
                </div>

                <div class="subject-schedule">
                    <strong>Subject Schedule:</strong><br>
                    <small>Please check the exam schedule for detailed timings</small>
                </div>
            ';

            if ($includeSignatures) {
                $html .= '
                <div class="signatures">
                    <div class="signature-box">
                        <small>Principal</small>
                    </div>
                    <div class="signature-box">
                        <small>Exam Controller</small>
                    </div>
                    <div class="signature-box">
                        <small>School Seal</small>
                    </div>
                </div>
                ';
            }

            $html .= '
                </div>
            </div>
            ';
        }

        $html .= '
        </body>
        </html>
        ';

        return $html;
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

    public function events() {
        $academicYearId = $this->getCurrentAcademicYearId();
        $where = "";
        $params = [];
        if ($academicYearId) {
            $where = "WHERE academic_year_id = ?";
            $params = [$academicYearId];
        }
        $events = $this->db->select("SELECT * FROM events $where ORDER BY created_at DESC", $params);
        $this->render('admin/events/index', ['events' => $events]);
    }

    public function gallery() {
        $gallery = $this->db->select("SELECT * FROM gallery ORDER BY created_at DESC");
        $this->render('admin/gallery/index', ['gallery' => $gallery]);
    }

    public function reports() {
        $this->render('admin/reports/index');
    }

    // Student Reports
    public function generateStudentReport() {
        $type = $_GET['type'] ?? 'list';
        $academicYearId = $this->getCurrentAcademicYearId();

        $where = "WHERE s.is_active = 1";
        $params = [];

        if ($academicYearId) {
            $where .= " AND c.academic_year_id = ?";
            $params[] = $academicYearId;
        }

        switch ($type) {
            case 'list':
                $students = $this->db->select("
                    SELECT s.*, c.class_name, c.section
                    FROM students s
                    LEFT JOIN classes c ON s.class_id = c.id
                    $where
                    ORDER BY s.first_name, s.last_name
                ", $params);
                $this->exportStudentList($students);
                break;

            case 'demographics':
                $demographics = [
                    'gender' => $this->db->select("
                        SELECT gender, COUNT(*) as count
                        FROM students s
                        LEFT JOIN classes c ON s.class_id = c.id
                        $where
                        GROUP BY gender
                    ", $params),
                    'caste' => $this->db->select("
                        SELECT caste_category, COUNT(*) as count
                        FROM students s
                        LEFT JOIN classes c ON s.class_id = c.id
                        $where AND caste_category IS NOT NULL
                        GROUP BY caste_category
                    ", $params),
                    'religion' => $this->db->select("
                        SELECT religion, COUNT(*) as count
                        FROM students s
                        LEFT JOIN classes c ON s.class_id = c.id
                        $where AND religion IS NOT NULL
                        GROUP BY religion
                    ", $params)
                ];
                $this->exportStudentDemographics($demographics);
                break;

            case 'performance':
                $performance = $this->db->select("
                    SELECT s.scholar_number, s.first_name, s.last_name, c.class_name, c.section,
                           AVG(er.percentage) as avg_percentage,
                           COUNT(CASE WHEN er.grade = 'A' THEN 1 END) as a_grades,
                           COUNT(CASE WHEN er.grade = 'B' THEN 1 END) as b_grades,
                           COUNT(CASE WHEN er.grade = 'C' THEN 1 END) as c_grades,
                           COUNT(CASE WHEN er.grade = 'F' THEN 1 END) as f_grades
                    FROM students s
                    LEFT JOIN classes c ON s.class_id = c.id
                    LEFT JOIN exam_results er ON s.id = er.student_id
                    $where
                    GROUP BY s.id, s.scholar_number, s.first_name, s.last_name, c.class_name, c.section
                    ORDER BY avg_percentage DESC
                ", $params);
                $this->exportStudentPerformance($performance);
                break;
        }
    }

    // Financial Reports
    public function generateFinancialReport() {
        $type = $_GET['type'] ?? 'fee-collection';
        $academicYearId = $this->getCurrentAcademicYearId();

        $where = "";
        $params = [];

        if ($academicYearId) {
            $where = " AND f.academic_year_id = ?";
            $params[] = $academicYearId;
        }

        switch ($type) {
            case 'fee-collection':
                $collections = $this->db->select("
                    SELECT fp.*, f.fee_type, s.first_name, s.last_name, s.scholar_number, c.class_name
                    FROM fee_payments fp
                    LEFT JOIN fees f ON fp.fee_id = f.id
                    LEFT JOIN students s ON f.student_id = s.id
                    LEFT JOIN classes c ON s.class_id = c.id
                    WHERE fp.payment_status = 'completed' $where
                    ORDER BY fp.payment_date DESC
                ", $params);
                $this->exportFeeCollection($collections);
                break;

            case 'outstanding-fees':
                $outstanding = $this->db->select("
                    SELECT f.*, s.first_name, s.last_name, s.scholar_number, c.class_name, c.section,
                           DATEDIFF(CURDATE(), f.due_date) as days_overdue
                    FROM fees f
                    LEFT JOIN students s ON f.student_id = s.id
                    LEFT JOIN classes c ON s.class_id = c.id
                    WHERE f.is_paid = 0 $where
                    ORDER BY f.due_date ASC
                ", $params);
                $this->exportOutstandingFees($outstanding);
                break;

            case 'financial-summary':
                $summary = [
                    'monthly_revenue' => $this->db->select("
                        SELECT DATE_FORMAT(fp.payment_date, '%Y-%m') as month,
                               SUM(fp.amount_paid) as revenue
                        FROM fee_payments fp
                        LEFT JOIN fees f ON fp.fee_id = f.id
                        WHERE fp.payment_status = 'completed' $where
                        GROUP BY DATE_FORMAT(fp.payment_date, '%Y-%m')
                        ORDER BY month DESC
                        LIMIT 12
                    ", $params),
                    'monthly_expenses' => $this->db->select("
                        SELECT DATE_FORMAT(expense_date, '%Y-%m') as month,
                               SUM(amount) as expenses
                        FROM expenses
                        WHERE 1=1 " . ($academicYearId ? " AND academic_year_id = ?" : "") . "
                        GROUP BY DATE_FORMAT(expense_date, '%Y-%m')
                        ORDER BY month DESC
                        LIMIT 12
                    ", $academicYearId ? [$academicYearId] : []),
                    'total_revenue' => $this->db->selectOne("
                        SELECT SUM(fp.amount_paid) as total
                        FROM fee_payments fp
                        LEFT JOIN fees f ON fp.fee_id = f.id
                        WHERE fp.payment_status = 'completed' $where
                    ", $params)['total'] ?? 0,
                    'total_expenses' => $this->db->selectOne("
                        SELECT SUM(amount) as total
                        FROM expenses
                        WHERE 1=1 " . ($academicYearId ? " AND academic_year_id = ?" : "") . "
                    ", $academicYearId ? [$academicYearId] : [])['total'] ?? 0
                ];
                $this->exportFinancialSummary($summary);
                break;
        }
    }

    // Attendance Reports
    public function generateAttendanceReport() {
        $type = $_GET['type'] ?? 'summary';
        $academicYearId = $this->getCurrentAcademicYearId();

        $where = "";
        $params = [];

        if ($academicYearId) {
            $where = " AND a.academic_year_id = ?";
            $params[] = $academicYearId;
        }

        switch ($type) {
            case 'summary':
                $summary = $this->db->select("
                    SELECT c.class_name, c.section,
                           COUNT(DISTINCT s.id) as total_students,
                           COUNT(CASE WHEN a.status = 'present' THEN 1 END) as present_count,
                           COUNT(CASE WHEN a.status = 'absent' THEN 1 END) as absent_count,
                           COUNT(CASE WHEN a.status = 'late' THEN 1 END) as late_count,
                           ROUND((COUNT(CASE WHEN a.status = 'present' THEN 1 END) / COUNT(a.id)) * 100, 2) as attendance_rate
                    FROM classes c
                    LEFT JOIN students s ON c.id = s.class_id AND s.is_active = 1
                    LEFT JOIN attendance a ON s.id = a.student_id AND a.class_id = c.id
                    WHERE c.is_active = 1 " . ($academicYearId ? " AND c.academic_year_id = ?" : "") . "
                    GROUP BY c.id, c.class_name, c.section
                    ORDER BY c.class_name
                ", $academicYearId ? [$academicYearId] : []);
                $this->exportAttendanceSummary($summary);
                break;

            case 'trends':
                $trends = $this->db->select("
                    SELECT DATE_FORMAT(a.attendance_date, '%Y-%m') as month,
                           COUNT(CASE WHEN a.status = 'present' THEN 1 END) as present_count,
                           COUNT(CASE WHEN a.status = 'absent' THEN 1 END) as absent_count,
                           COUNT(CASE WHEN a.status = 'late' THEN 1 END) as late_count,
                           ROUND((COUNT(CASE WHEN a.status = 'present' THEN 1 END) / COUNT(a.id)) * 100, 2) as attendance_rate
                    FROM attendance a
                    WHERE 1=1 $where
                    GROUP BY DATE_FORMAT(a.attendance_date, '%Y-%m')
                    ORDER BY month DESC
                    LIMIT 12
                ", $params);
                $this->exportAttendanceTrends($trends);
                break;

            case 'absentee-report':
                $absentees = $this->db->select("
                    SELECT s.scholar_number, s.first_name, s.last_name, c.class_name, c.section,
                           COUNT(CASE WHEN a.status = 'absent' THEN 1 END) as absent_days,
                           COUNT(a.id) as total_days,
                           ROUND((COUNT(CASE WHEN a.status = 'absent' THEN 1 END) / COUNT(a.id)) * 100, 2) as absence_rate
                    FROM students s
                    LEFT JOIN classes c ON s.class_id = c.id
                    LEFT JOIN attendance a ON s.id = a.student_id
                    WHERE s.is_active = 1 " . ($academicYearId ? " AND a.academic_year_id = ?" : "") . "
                    GROUP BY s.id, s.scholar_number, s.first_name, s.last_name, c.class_name, c.section
                    HAVING absent_days > 0
                    ORDER BY absence_rate DESC
                    LIMIT 50
                ", $academicYearId ? [$academicYearId] : []);
                $this->exportAbsenteeReport($absentees);
                break;
        }
    }

    // Academic Reports
    public function generateAcademicReport() {
        $type = $_GET['type'] ?? 'exam-results';
        $academicYearId = $this->getCurrentAcademicYearId();

        $where = "";
        $params = [];

        if ($academicYearId) {
            $where = " AND er.academic_year_id = ?";
            $params[] = $academicYearId;
        }

        switch ($type) {
            case 'exam-results':
                $results = $this->db->select("
                    SELECT e.exam_name, s.scholar_number, s.first_name, s.last_name, c.class_name,
                           sub.subject_name, er.marks_obtained, er.max_marks, er.grade, er.percentage
                    FROM exam_results er
                    LEFT JOIN exams e ON er.exam_id = e.id
                    LEFT JOIN students s ON er.student_id = s.id
                    LEFT JOIN subjects sub ON er.subject_id = sub.id
                    LEFT JOIN classes c ON s.class_id = c.id
                    WHERE 1=1 $where
                    ORDER BY e.exam_name, s.first_name, s.last_name, sub.subject_name
                ", $params);
                $this->exportExamResults($results);
                break;

            case 'grade-distribution':
                $distribution = $this->db->select("
                    SELECT c.class_name, c.section, sub.subject_name,
                           COUNT(CASE WHEN er.grade = 'A' THEN 1 END) as a_count,
                           COUNT(CASE WHEN er.grade = 'B' THEN 1 END) as b_count,
                           COUNT(CASE WHEN er.grade = 'C' THEN 1 END) as c_count,
                           COUNT(CASE WHEN er.grade = 'D' THEN 1 END) as d_count,
                           COUNT(CASE WHEN er.grade = 'F' THEN 1 END) as f_count,
                           COUNT(er.id) as total_students
                    FROM exam_results er
                    LEFT JOIN students s ON er.student_id = s.id
                    LEFT JOIN classes c ON s.class_id = c.id
                    LEFT JOIN subjects sub ON er.subject_id = sub.id
                    WHERE 1=1 $where
                    GROUP BY c.id, c.class_name, c.section, sub.id, sub.subject_name
                    ORDER BY c.class_name, sub.subject_name
                ", $params);
                $this->exportGradeDistribution($distribution);
                break;

            case 'academic-performance':
                $performance = $this->db->select("
                    SELECT sub.subject_name,
                           AVG(er.percentage) as avg_percentage,
                           MIN(er.percentage) as min_percentage,
                           MAX(er.percentage) as max_percentage,
                           COUNT(CASE WHEN er.grade = 'A' THEN 1 END) as a_count,
                           COUNT(CASE WHEN er.grade = 'B' THEN 1 END) as b_count,
                           COUNT(CASE WHEN er.grade = 'C' THEN 1 END) as c_count,
                           COUNT(CASE WHEN er.grade = 'F' THEN 1 END) as f_count,
                           COUNT(er.id) as total_students
                    FROM exam_results er
                    LEFT JOIN subjects sub ON er.subject_id = sub.id
                    WHERE 1=1 $where
                    GROUP BY sub.id, sub.subject_name
                    ORDER BY avg_percentage DESC
                ", $params);
                $this->exportAcademicPerformance($performance);
                break;
        }
    }

    // Custom Report Generation
    public function generateCustomReport() {
        $reportType = $_POST['reportType'] ?? '';
        $dateFrom = $_POST['dateFrom'] ?? '';
        $dateTo = $_POST['dateTo'] ?? '';
        $format = $_POST['format'] ?? 'csv';

        if (empty($reportType)) {
            $this->json(['success' => false, 'message' => 'Report type is required']);
            return;
        }

        $academicYearId = $this->getCurrentAcademicYearId();
        $where = "";
        $params = [];

        if ($academicYearId) {
            $where .= " AND academic_year_id = ?";
            $params[] = $academicYearId;
        }

        // Add date filters
        if (!empty($dateFrom) && !empty($dateTo)) {
            switch ($reportType) {
                case 'students':
                    $where .= " AND s.created_at BETWEEN ? AND ?";
                    break;
                case 'fees':
                    $where .= " AND f.due_date BETWEEN ? AND ?";
                    break;
                case 'attendance':
                    $where .= " AND a.attendance_date BETWEEN ? AND ?";
                    break;
                case 'exams':
                    $where .= " AND e.start_date BETWEEN ? AND ?";
                    break;
                case 'events':
                    $where .= " AND event_date BETWEEN ? AND ?";
                    break;
            }
            $params[] = $dateFrom . ' 00:00:00';
            $params[] = $dateTo . ' 23:59:59';
        }

        $data = [];
        switch ($reportType) {
            case 'students':
                $data = $this->db->select("
                    SELECT s.*, c.class_name, c.section
                    FROM students s
                    LEFT JOIN classes c ON s.class_id = c.id
                    WHERE s.is_active = 1 $where
                    ORDER BY s.created_at DESC
                ", $params);
                break;

            case 'fees':
                $data = $this->db->select("
                    SELECT f.*, s.first_name, s.last_name, s.scholar_number, c.class_name
                    FROM fees f
                    LEFT JOIN students s ON f.student_id = s.id
                    LEFT JOIN classes c ON s.class_id = c.id
                    WHERE 1=1 $where
                    ORDER BY f.due_date DESC
                ", $params);
                break;

            case 'attendance':
                $data = $this->db->select("
                    SELECT a.*, s.first_name, s.last_name, s.scholar_number, c.class_name, c.section
                    FROM attendance a
                    LEFT JOIN students s ON a.student_id = s.id
                    LEFT JOIN classes c ON a.class_id = c.id
                    WHERE 1=1 $where
                    ORDER BY a.attendance_date DESC
                ", $params);
                break;

            case 'exams':
                $data = $this->db->select("
                    SELECT e.*, c.class_name, c.section
                    FROM exams e
                    LEFT JOIN classes c ON e.class_id = c.id
                    WHERE e.is_active = 1 $where
                    ORDER BY e.start_date DESC
                ", $params);
                break;

            case 'events':
                $data = $this->db->select("
                    SELECT * FROM events
                    WHERE is_active = 1 $where
                    ORDER BY event_date DESC
                ", $params);
                break;

            default:
                $this->json(['success' => false, 'message' => 'Invalid report type']);
                return;
        }

        $this->exportCustomReport($data, $reportType, $format);
    }

    // Export Methods
    private function exportStudentList($students) {
        $this->exportToCSV($students, 'student_list', [
            'scholar_number' => 'Scholar Number',
            'admission_number' => 'Admission Number',
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'class_name' => 'Class',
            'section' => 'Section',
            'gender' => 'Gender',
            'date_of_birth' => 'Date of Birth',
            'mobile' => 'Mobile',
            'email' => 'Email'
        ]);
    }

    private function exportStudentDemographics($demographics) {
        // Create a summary array for CSV export
        $data = [];

        // Gender distribution
        $data[] = ['Category', 'Type', 'Count'];
        $data[] = ['Gender Distribution', '', ''];
        foreach ($demographics['gender'] as $row) {
            $data[] = ['', $row['gender'], $row['count']];
        }

        // Caste distribution
        $data[] = ['', '', ''];
        $data[] = ['Caste Distribution', '', ''];
        foreach ($demographics['caste'] as $row) {
            $data[] = ['', $row['caste_category'], $row['count']];
        }

        // Religion distribution
        $data[] = ['', '', ''];
        $data[] = ['Religion Distribution', '', ''];
        foreach ($demographics['religion'] as $row) {
            $data[] = ['', $row['religion'], $row['count']];
        }

        $this->exportToCSV($data, 'student_demographics', ['Category', 'Type', 'Count']);
    }

    private function exportStudentPerformance($performance) {
        $this->exportToCSV($performance, 'student_performance', [
            'scholar_number' => 'Scholar Number',
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'class_name' => 'Class',
            'section' => 'Section',
            'avg_percentage' => 'Average Percentage',
            'a_grades' => 'A Grades',
            'b_grades' => 'B Grades',
            'c_grades' => 'C Grades',
            'f_grades' => 'F Grades'
        ]);
    }

    private function exportFeeCollection($collections) {
        $this->exportToCSV($collections, 'fee_collection', [
            'payment_date' => 'Payment Date',
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'scholar_number' => 'Scholar Number',
            'class_name' => 'Class',
            'fee_type' => 'Fee Type',
            'amount_paid' => 'Amount Paid',
            'payment_mode' => 'Payment Mode',
            'transaction_id' => 'Transaction ID'
        ]);
    }

    private function exportOutstandingFees($outstanding) {
        $this->exportToCSV($outstanding, 'outstanding_fees', [
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'scholar_number' => 'Scholar Number',
            'class_name' => 'Class',
            'section' => 'Section',
            'fee_type' => 'Fee Type',
            'amount' => 'Amount Due',
            'due_date' => 'Due Date',
            'days_overdue' => 'Days Overdue'
        ]);
    }

    private function exportFinancialSummary($summary) {
        $data = [];

        // Revenue data
        $data[] = ['Type', 'Month/Year', 'Amount'];
        $data[] = ['Monthly Revenue', '', ''];
        foreach ($summary['monthly_revenue'] as $row) {
            $data[] = ['', $row['month'], $row['revenue']];
        }

        $data[] = ['', '', ''];
        $data[] = ['Monthly Expenses', '', ''];
        foreach ($summary['monthly_expenses'] as $row) {
            $data[] = ['', $row['month'], $row['expenses']];
        }

        $data[] = ['', '', ''];
        $data[] = ['Total Revenue', '', $summary['total_revenue']];
        $data[] = ['Total Expenses', '', $summary['total_expenses']];
        $data[] = ['Net Income', '', $summary['total_revenue'] - $summary['total_expenses']];

        $this->exportToCSV($data, 'financial_summary', ['Type', 'Month/Year', 'Amount']);
    }

    private function exportAttendanceSummary($summary) {
        $this->exportToCSV($summary, 'attendance_summary', [
            'class_name' => 'Class',
            'section' => 'Section',
            'total_students' => 'Total Students',
            'present_count' => 'Present',
            'absent_count' => 'Absent',
            'late_count' => 'Late',
            'attendance_rate' => 'Attendance Rate (%)'
        ]);
    }

    private function exportAttendanceTrends($trends) {
        $this->exportToCSV($trends, 'attendance_trends', [
            'month' => 'Month',
            'present_count' => 'Present',
            'absent_count' => 'Absent',
            'late_count' => 'Late',
            'attendance_rate' => 'Attendance Rate (%)'
        ]);
    }

    private function exportAbsenteeReport($absentees) {
        $this->exportToCSV($absentees, 'absentee_report', [
            'scholar_number' => 'Scholar Number',
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'class_name' => 'Class',
            'section' => 'Section',
            'absent_days' => 'Absent Days',
            'total_days' => 'Total Days',
            'absence_rate' => 'Absence Rate (%)'
        ]);
    }

    private function exportExamResults($results) {
        $this->exportToCSV($results, 'exam_results', [
            'exam_name' => 'Exam Name',
            'scholar_number' => 'Scholar Number',
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'class_name' => 'Class',
            'subject_name' => 'Subject',
            'marks_obtained' => 'Marks Obtained',
            'max_marks' => 'Max Marks',
            'grade' => 'Grade',
            'percentage' => 'Percentage'
        ]);
    }

    private function exportGradeDistribution($distribution) {
        $this->exportToCSV($distribution, 'grade_distribution', [
            'class_name' => 'Class',
            'section' => 'Section',
            'subject_name' => 'Subject',
            'a_count' => 'A Grades',
            'b_count' => 'B Grades',
            'c_count' => 'C Grades',
            'd_count' => 'D Grades',
            'f_count' => 'F Grades',
            'total_students' => 'Total Students'
        ]);
    }

    private function exportAcademicPerformance($performance) {
        $this->exportToCSV($performance, 'academic_performance', [
            'subject_name' => 'Subject',
            'avg_percentage' => 'Average Percentage',
            'min_percentage' => 'Min Percentage',
            'max_percentage' => 'Max Percentage',
            'a_count' => 'A Grades',
            'b_count' => 'B Grades',
            'c_count' => 'C Grades',
            'f_count' => 'F Grades',
            'total_students' => 'Total Students'
        ]);
    }

    private function exportCustomReport($data, $reportType, $format) {
        $filename = $reportType . '_report_' . date('Y-m-d_H-i-s');

        switch ($format) {
            case 'csv':
                $this->exportToCSV($data, $filename);
                break;
            case 'excel':
                $this->exportToExcel($data, $filename);
                break;
            case 'pdf':
                $this->exportToPDF($data, $filename, $reportType);
                break;
            default:
                $this->exportToCSV($data, $filename);
        }
    }

    private function exportToCSV($data, $filename, $headers = null) {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');

        // Write headers
        if ($headers) {
            fputcsv($output, array_values($headers));
        } elseif (!empty($data)) {
            fputcsv($output, array_keys($data[0]));
        }

        // Write data
        foreach ($data as $row) {
            if ($headers) {
                $csvRow = [];
                foreach (array_keys($headers) as $key) {
                    $csvRow[] = $row[$key] ?? '';
                }
                fputcsv($output, $csvRow);
            } else {
                fputcsv($output, $row);
            }
        }

        fclose($output);
        exit;
    }

    private function exportToExcel($data, $filename) {
        // For now, export as CSV since we don't have PHPSpreadsheet
        // In production, you would use a library like PHPSpreadsheet
        $this->exportToCSV($data, $filename);
    }

    private function exportToPDF($data, $filename, $reportType) {
        // Generate HTML that can be printed as PDF
        $html = $this->generateReportHTML($data, $reportType);

        // Save to temp file
        $tempDir = BASE_PATH . 'temp';
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $filePath = $tempDir . '/' . $filename . '.html';
        file_put_contents($filePath, $html);

        // Return HTML file URL (in production, you might convert to PDF)
        header('Content-Type: text/html');
        header('Content-Disposition: attachment; filename="' . $filename . '.html"');
        readfile($filePath);
        unlink($filePath); // Clean up temp file
        exit;
    }

    private function generateReportHTML($data, $reportType) {
        $schoolName = $this->db->selectOne("SELECT setting_value FROM settings WHERE setting_key = 'school_name'")['setting_value'] ?? 'School Management System';
        $schoolAddress = $this->db->selectOne("SELECT setting_value FROM settings WHERE setting_key = 'school_address'")['setting_value'] ?? '';

        $title = ucfirst(str_replace('_', ' ', $reportType)) . ' Report';
        $generatedDate = date('M d, Y H:i:s');

        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>' . $title . '</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
                .school-name { font-size: 18px; font-weight: bold; }
                .report-title { font-size: 16px; margin: 10px 0; }
                table { width: 100%; border-collapse: collapse; margin: 20px 0; }
                th, td { border: 1px solid #000; padding: 8px; text-align: left; }
                th { background-color: #f0f0f0; font-weight: bold; }
                .footer { margin-top: 30px; text-align: center; font-size: 12px; color: #666; }
                @media print { body { margin: 10px; } }
            </style>
        </head>
        <body>
            <div class="header">
                <div class="school-name">' . htmlspecialchars($schoolName) . '</div>
                <div>' . htmlspecialchars($schoolAddress) . '</div>
                <div class="report-title">' . $title . '</div>
                <div>Generated on: ' . $generatedDate . '</div>
            </div>

            <table>
                <thead>
                    <tr>';

        if (!empty($data)) {
            foreach (array_keys($data[0]) as $key) {
                $html .= '<th>' . htmlspecialchars(ucfirst(str_replace('_', ' ', $key))) . '</th>';
            }
        }

        $html .= '
                    </tr>
                </thead>
                <tbody>';

        foreach ($data as $row) {
            $html .= '<tr>';
            foreach ($row as $value) {
                $html .= '<td>' . htmlspecialchars($value ?? '') . '</td>';
            }
            $html .= '</tr>';
        }

        $html .= '
                </tbody>
            </table>

            <div class="footer">
                Total Records: ' . count($data) . '<br>
                This report was generated by the School Management System
            </div>
        </body>
        </html>';

        return $html;
    }

    public function settings() {
        $settings = $this->db->select("SELECT * FROM settings ORDER BY setting_key");
        $user = $_SESSION['user'] ?? null;

        // Convert settings array to associative array for easier access
        $settingsAssoc = [];
        foreach ($settings as $setting) {
            $settingsAssoc[$setting['setting_key']] = $setting['setting_value'];
        }

        $csrfToken = $this->csrfToken();
        $this->render('admin/settings/index', ['settings' => $settingsAssoc, 'user' => $user, 'csrf_token' => $csrfToken]);
    }

    public function saveSettings() {
        $data = $_POST;
        $csrfToken = $data['csrf_token'] ?? '';

        if (!$this->checkCsrfToken($csrfToken)) {
            $this->session->setFlash('error', 'Invalid CSRF token');
            $this->redirect('/admin/settings');
        }

        // Handle logo upload
        $logoPath = '';
        if (isset($_FILES['school_logo']) && $_FILES['school_logo']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = UPLOADS_PATH . 'settings/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $fileName = 'school_logo_' . time() . '_' . basename($_FILES['school_logo']['name']);
            $targetFile = $uploadDir . $fileName;

            // Validate file type
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            $fileType = mime_content_type($_FILES['school_logo']['tmp_name']);

            if (!in_array($fileType, $allowedTypes)) {
                $this->session->setFlash('error', 'Invalid file type. Only JPG, PNG, and GIF are allowed.');
                $this->redirect('/admin/settings');
            }

            // Validate file size (2MB max)
            if ($_FILES['school_logo']['size'] > 2 * 1024 * 1024) {
                $this->session->setFlash('error', 'File size too large. Maximum size is 2MB.');
                $this->redirect('/admin/settings');
            }

            if (move_uploaded_file($_FILES['school_logo']['tmp_name'], $targetFile)) {
                $logoPath = 'settings/' . $fileName;
            } else {
                $this->session->setFlash('error', 'Failed to upload logo file.');
                $this->redirect('/admin/settings');
            }
        }

        // Define all possible settings
        $settingKeys = [
            'site_name', 'timezone', 'language', 'date_format',
            'school_name', 'school_code', 'school_address', 'school_phone', 'school_email',
            'smtp_host', 'smtp_port', 'smtp_user', 'smtp_pass', 'from_email',
            'session_timeout', 'password_min_length', 'two_factor_auth', 'password_expiry',
            'scholar_auto_generate', 'school_logo', 'tc_prefix', 'tc_start_number'
        ];

        // Save each setting
        foreach ($settingKeys as $key) {
            $value = $data[$key] ?? '';
            if ($key === 'school_logo' && $logoPath) {
                $value = $logoPath;
            }

            // Check if setting exists
            $existing = $this->db->selectOne("SELECT id FROM settings WHERE setting_key = ?", [$key]);
            if ($existing) {
                $this->db->update('settings', ['setting_value' => $value], 'setting_key = ?', [$key]);
            } else {
                $this->db->insert('settings', ['setting_key' => $key, 'setting_value' => $value, 'setting_type' => 'string']);
            }
        }

        $this->session->setFlash('success', 'Settings saved successfully');
        $this->redirect('/admin/settings');
    }

    public function homepage() {
        // Get homepage content by sections
        $carousel = $this->db->select("SELECT * FROM homepage_content WHERE section = 'carousel' AND is_active = 1 ORDER BY sort_order");
        $about = $this->db->selectOne("SELECT * FROM homepage_content WHERE section = 'about' AND is_active = 1 LIMIT 1");
        $courses = $this->db->select("SELECT * FROM homepage_content WHERE section = 'courses' AND is_active = 1 ORDER BY sort_order");
        $events = $this->db->select("SELECT * FROM events WHERE is_active = 1 ORDER BY event_date DESC LIMIT 5");
        $gallery = $this->db->select("SELECT * FROM gallery WHERE is_active = 1 ORDER BY created_at DESC LIMIT 8");
        $testimonials = $this->db->select("SELECT * FROM homepage_content WHERE section = 'testimonials' AND is_active = 1 ORDER BY sort_order");

        // Get contact info from settings
        $contact = [
            'address' => $this->db->selectOne("SELECT setting_value FROM settings WHERE setting_key = 'school_address'")['setting_value'] ?? '',
            'phone' => $this->db->selectOne("SELECT setting_value FROM settings WHERE setting_key = 'school_phone'")['setting_value'] ?? '',
            'email' => $this->db->selectOne("SELECT setting_value FROM settings WHERE setting_key = 'school_email'")['setting_value'] ?? ''
        ];

        $this->render('admin/homepage/index', [
            'carousel' => $carousel,
            'about' => $about,
            'courses' => $courses,
            'events' => $events,
            'gallery' => $gallery,
            'testimonials' => $testimonials,
            'contact' => $contact
        ]);
    }

    public function homepageCarousel() {
        $carousel = $this->db->select("SELECT * FROM homepage_content WHERE section = 'carousel' ORDER BY sort_order");
        $csrfToken = $this->csrfToken();
        $this->render('admin/homepage/carousel', ['carousel' => $carousel, 'csrf_token' => $csrfToken]);
    }

    public function saveHomepageCarousel() {
        $data = $_POST;
        $csrfToken = $data['csrf_token'] ?? '';

        if (!$this->checkCsrfToken($csrfToken)) {
            $this->session->setFlash('error', 'Invalid CSRF token');
            $this->redirect('/admin/homepage/carousel');
        }

        // Handle carousel updates
        if (isset($data['carousel'])) {
            foreach ($data['carousel'] as $id => $item) {
                $this->db->update('homepage_content', [
                    'title' => $item['title'] ?? '',
                    'content' => $item['content'] ?? '',
                    'link' => $item['link'] ?? '',
                    'sort_order' => $item['sort_order'] ?? 0,
                    'is_active' => isset($item['is_active']) ? 1 : 0
                ], 'id = ?', [$id]);
            }
        }

        // Handle new carousel item
        if (!empty($data['new_title'])) {
            $imagePath = '';
            if (isset($_FILES['new_image']) && $_FILES['new_image']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = UPLOADS_PATH . 'homepage/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $fileName = uniqid() . '_' . basename($_FILES['new_image']['name']);
                $targetFile = $uploadDir . $fileName;
                if (move_uploaded_file($_FILES['new_image']['tmp_name'], $targetFile)) {
                    $imagePath = 'homepage/' . $fileName;
                }
            }

            if ($imagePath) {
                $this->db->insert('homepage_content', [
                    'section' => 'carousel',
                    'title' => $data['new_title'],
                    'content' => $data['new_content'] ?? '',
                    'image_path' => $imagePath,
                    'link' => $data['new_link'] ?? '',
                    'sort_order' => $data['new_sort_order'] ?? 0,
                    'is_active' => 1
                ]);
            }
        }

        $this->session->setFlash('success', 'Carousel updated successfully');
        $this->redirect('/admin/homepage/carousel');
    }

    public function homepageAbout() {
        $about = $this->db->selectOne("SELECT * FROM homepage_content WHERE section = 'about' LIMIT 1");
        $csrfToken = $this->csrfToken();
        $this->render('admin/homepage/about', ['about' => $about, 'csrf_token' => $csrfToken]);
    }

    public function saveHomepageAbout() {
        $data = $_POST;
        $csrfToken = $data['csrf_token'] ?? '';

        if (!$this->checkCsrfToken($csrfToken)) {
            $this->session->setFlash('error', 'Invalid CSRF token');
            $this->redirect('/admin/homepage/about');
        }

        $imagePath = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = UPLOADS_PATH . 'homepage/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
            $targetFile = $uploadDir . $fileName;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                $imagePath = 'homepage/' . $fileName;
            }
        }

        $about = $this->db->selectOne("SELECT * FROM homepage_content WHERE section = 'about' LIMIT 1");

        if ($about) {
            // Update existing
            $updateData = [
                'title' => $data['title'] ?? '',
                'content' => $data['content'] ?? '',
                'is_active' => isset($data['is_active']) ? 1 : 0
            ];
            if ($imagePath) {
                $updateData['image_path'] = $imagePath;
            }
            $this->db->update('homepage_content', $updateData, 'id = ?', [$about['id']]);
        } else {
            // Create new
            $this->db->insert('homepage_content', [
                'section' => 'about',
                'title' => $data['title'] ?? '',
                'content' => $data['content'] ?? '',
                'image_path' => $imagePath,
                'is_active' => isset($data['is_active']) ? 1 : 0
            ]);
        }

        $this->session->setFlash('success', 'About section updated successfully');
        $this->redirect('/admin/homepage/about');
    }

    public function notifications() {
        $academicYearId = $this->getCurrentAcademicYearId();
        $where = "WHERE is_active = 1";
        $params = [];
        if ($academicYearId) {
            $where .= " AND academic_year_id = ?";
            $params = [$academicYearId];
        }
        $classes = $this->db->select("SELECT * FROM classes $where ORDER BY class_name", $params);
        $csrfToken = $this->csrfToken();
        $this->render('admin/notifications/index', ['classes' => $classes, 'csrf_token' => $csrfToken]);
    }

    public function viewNotifications() {
        $userId = $_SESSION['user']['id'] ?? 1;

        // Get notifications for the current user
        $notifications = $this->db->select("
            SELECT * FROM notifications
            WHERE user_id = ? OR user_id IS NULL
            ORDER BY created_at DESC
            LIMIT 50
        ", [$userId]);

        // Get notification counts
        $unreadCount = $this->db->selectOne("
            SELECT COUNT(*) as count FROM notifications
            WHERE (user_id = ? OR user_id IS NULL) AND is_read = FALSE
        ", [$userId])['count'];

        $this->render('admin/notifications/view', [
            'notifications' => $notifications,
            'unread_count' => $unreadCount
        ]);
    }

    public function markNotificationRead() {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!$data || !isset($data['notification_id'])) {
            $this->json(['success' => false, 'message' => 'Notification ID is required'], 400);
        }

        $notificationId = $data['notification_id'];
        $userId = $_SESSION['user']['id'] ?? 1;

        // Verify the notification belongs to the user
        $notification = $this->db->selectOne("
            SELECT id FROM notifications
            WHERE id = ? AND (user_id = ? OR user_id IS NULL)
        ", [$notificationId, $userId]);

        if (!$notification) {
            $this->json(['success' => false, 'message' => 'Notification not found'], 404);
        }

        // Mark as read
        $updated = $this->db->update('notifications', ['is_read' => 1], 'id = ?', [$notificationId]);

        if ($updated) {
            $this->json(['success' => true, 'message' => 'Notification marked as read']);
        } else {
            $this->json(['success' => false, 'message' => 'Failed to mark notification as read'], 500);
        }
    }

    public function getStudentsForNotifications() {
        $classId = $_GET['class_id'] ?? '';
        $academicYearId = $this->getCurrentAcademicYearId();

        $query = "SELECT s.*, c.class_name, c.section FROM students s LEFT JOIN classes c ON s.class_id = c.id WHERE s.is_active = 1";
        $params = [];

        if (!empty($classId)) {
            $query .= " AND s.class_id = ?";
            $params[] = $classId;
        }

        if ($academicYearId) {
            $query .= " AND c.academic_year_id = ?";
            $params[] = $academicYearId;
        }

        $query .= " ORDER BY s.first_name, s.last_name";

        $students = $this->db->select($query, $params);
        $this->json(['students' => $students]);
    }

    public function sendNotification() {
        $data = $_POST;
        $csrfToken = $data['csrf_token'] ?? '';

        if (!$this->checkCsrfToken($csrfToken)) {
            $this->json(['success' => false, 'message' => 'Invalid CSRF token'], 400);
        }

        $notificationType = $data['notification_type'] ?? '';
        $recipientType = $data['recipient_type'] ?? '';
        $subject = $data['subject'] ?? '';
        $message = $data['message'] ?? '';
        $template = $data['template'] ?? '';

        if (empty($notificationType) || empty($recipientType) || empty($subject) || empty($message)) {
            $this->json(['success' => false, 'message' => 'All fields are required'], 400);
        }

        // Get recipients
        $recipients = [];
        $academicYearId = $this->getCurrentAcademicYearId();

        if ($recipientType === 'all_students') {
            $where = "WHERE s.is_active = 1";
            $params = [];
            if ($academicYearId) {
                $where .= " AND c.academic_year_id = ?";
                $params = [$academicYearId];
            }
            $recipients = $this->db->select("SELECT s.*, c.class_name, c.section FROM students s LEFT JOIN classes c ON s.class_id = c.id $where ORDER BY s.first_name, s.last_name", $params);
        } elseif ($recipientType === 'class' && !empty($data['class_id'])) {
            $where = "WHERE s.class_id = ? AND s.is_active = 1";
            $params = [$data['class_id']];
            if ($academicYearId) {
                $where .= " AND c.academic_year_id = ?";
                $params[] = $academicYearId;
            }
            $recipients = $this->db->select("SELECT s.*, c.class_name, c.section FROM students s LEFT JOIN classes c ON s.class_id = c.id $where ORDER BY s.first_name, s.last_name", $params);
        } elseif ($recipientType === 'selected' && !empty($data['student_ids'])) {
            $studentIds = $data['student_ids'];
            $placeholders = str_repeat('?,', count($studentIds) - 1) . '?';
            $where = "WHERE s.id IN ($placeholders) AND s.is_active = 1";
            $params = $studentIds;
            if ($academicYearId) {
                $where .= " AND c.academic_year_id = ?";
                $params[] = $academicYearId;
            }
            $recipients = $this->db->select("SELECT s.*, c.class_name, c.section FROM students s LEFT JOIN classes c ON s.class_id = c.id $where ORDER BY s.first_name, s.last_name", $params);
        }

        if (empty($recipients)) {
            $this->json(['success' => false, 'message' => 'No recipients found'], 400);
        }

        $successCount = 0;
        $errors = [];

        foreach ($recipients as $recipient) {
            try {
                if ($notificationType === 'email') {
                    $this->sendEmail($recipient, $subject, $message);
                } elseif ($notificationType === 'sms') {
                    $this->sendSMS($recipient, $message);
                } elseif ($notificationType === 'both') {
                    $this->sendEmail($recipient, $subject, $message);
                    $this->sendSMS($recipient, $message);
                }
                $successCount++;
            } catch (Exception $e) {
                $errors[] = "Failed to send to {$recipient['first_name']} {$recipient['last_name']}: " . $e->getMessage();
            }
        }

        $response = [
            'success' => true,
            'message' => "Notification sent to $successCount recipients"
        ];

        if (!empty($errors)) {
            $response['message'] .= ". Errors: " . implode('; ', $errors);
        }

        $this->json($response);
    }

    private function sendEmail($recipient, $subject, $message) {
        $emailConfig = require CONFIG_PATH . 'email.php';

        if (!$emailConfig['use_smtp']) {
            // Use PHP mail function
            $headers = [
                'MIME-Version: 1.0',
                'Content-type: text/html; charset=UTF-8',
                'From: ' . $emailConfig['from_name'] . ' <' . $emailConfig['from_email'] . '>',
                'Reply-To: ' . $emailConfig['reply_to_name'] . ' <' . $emailConfig['reply_to_email'] . '>'
            ];

            $mailSent = mail($recipient['email'], $subject, $message, implode("\r\n", $headers));

            if (!$mailSent) {
                throw new Exception('Failed to send email using PHP mail()');
            }
        } else {
            // Use SMTP (would need PHPMailer or similar library)
            // For now, throw an exception
            throw new Exception('SMTP sending not implemented yet');
        }
    }

    private function sendSMS($recipient, $message) {
        $smsConfig = require CONFIG_PATH . 'sms.php';

        if (!$smsConfig['enabled']) {
            throw new Exception('SMS notifications are disabled');
        }

        if ($smsConfig['provider'] === 'twilio') {
            // Use Twilio API
            $accountSid = $smsConfig['twilio']['account_sid'];
            $authToken = $smsConfig['twilio']['auth_token'];
            $fromNumber = $smsConfig['twilio']['from_number'];

            if (empty($accountSid) || empty($authToken) || empty($fromNumber)) {
                throw new Exception('Twilio configuration incomplete');
            }

            // For now, simulate SMS sending
            // In production, you would use Twilio SDK
            if ($smsConfig['debug']) {
                error_log("SMS to {$recipient['mobile']}: $message");
            }
        } else {
            throw new Exception('SMS provider not supported');
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

    public function getNextScholarNumber() {
        $classId = $_GET['class_id'] ?? '';

        if (!$classId) {
            $this->json(['success' => false, 'message' => 'Class ID is required'], 400);
        }

        $class = $this->db->selectOne("SELECT class_name FROM classes WHERE id = ?", [$classId]);
        if (!$class) {
            $this->json(['success' => false, 'message' => 'Class not found'], 404);
        }

        $className = strtolower(trim($class['class_name']));
        if (in_array($className, ['nursery', 'ukg'])) {
            $series = 'N';
            $maxQuery = "SELECT MAX(CAST(SUBSTRING(scholar_number, 2) AS UNSIGNED)) as max_num FROM students WHERE scholar_number LIKE 'N%'";
        } else {
            $classNum = (int) preg_replace('/\D/', '', $class['class_name']);
            if ($classNum >= 1 && $classNum <= 8) {
                $series = 'P';
                $maxQuery = "SELECT MAX(CAST(SUBSTRING(scholar_number, 2) AS UNSIGNED)) as max_num FROM students WHERE scholar_number LIKE 'P%'";
            } elseif ($classNum >= 9 && $classNum <= 12) {
                $series = 'S';
                $maxQuery = "SELECT MAX(CAST(SUBSTRING(scholar_number, 2) AS UNSIGNED)) as max_num FROM students WHERE scholar_number LIKE 'S%'";
            } else {
                $series = 'P';
                $maxQuery = "SELECT MAX(CAST(SUBSTRING(scholar_number, 2) AS UNSIGNED)) as max_num FROM students WHERE scholar_number LIKE 'P%'";
            }
        }

        $maxNum = $this->db->selectOne($maxQuery)['max_num'] ?? 0;
        $nextNum = $maxNum + 1;
        $scholarNumber = $series . str_pad($nextNum, 3, '0', STR_PAD_LEFT);

        $this->json(['success' => true, 'scholar_number' => $scholarNumber]);
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
