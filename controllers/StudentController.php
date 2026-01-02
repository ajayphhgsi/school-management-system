<?php
/**
 * Student Controller - Student Portal Management
 */

class StudentController extends Controller {

    public function __construct() {
        parent::__construct();
        $this->middleware('auth');
        $this->middleware('student');
    }

    public function dashboard() {
        $studentId = $_SESSION['user']['id'];

        // Get student basic info
        $student = $this->db->selectOne("SELECT * FROM students WHERE id = ?", [$studentId]);

        // Get attendance stats
        $attendanceStats = $this->db->selectOne("
            SELECT
                COUNT(*) as total_days,
                SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_days,
                SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent_days,
                SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late_days
            FROM attendance
            WHERE student_id = ?
        ", [$studentId]);

        $attendancePercentage = $attendanceStats['total_days'] > 0 ?
            round(($attendanceStats['present_days'] / $attendanceStats['total_days']) * 100, 1) : 0;

        // Get recent exam results
        $recentResults = $this->db->select("
            SELECT er.*, e.exam_name, s.subject_name
            FROM exam_results er
            LEFT JOIN exams e ON er.exam_id = e.id
            LEFT JOIN subjects s ON er.subject_id = s.id
            WHERE er.student_id = ?
            ORDER BY er.created_at DESC
            LIMIT 5
        ", [$studentId]);

        // Get fee status
        $feeStatus = $this->db->selectOne("
            SELECT SUM(amount) as total_fees, SUM(amount_paid) as paid_amount
            FROM fees f
            LEFT JOIN fee_payments fp ON f.id = fp.fee_id
            WHERE f.student_id = ?
        ", [$studentId]);

        $pendingFees = ($feeStatus['total_fees'] ?? 0) - ($feeStatus['paid_amount'] ?? 0);

        // Get upcoming events
        $upcomingEvents = $this->db->select("
            SELECT * FROM events
            WHERE event_date >= CURDATE() AND is_active = 1
            ORDER BY event_date LIMIT 3
        ");

        $stats = [
            'attendance_percentage' => $attendancePercentage,
            'total_present' => $attendanceStats['present_days'] ?? 0,
            'total_absent' => $attendanceStats['absent_days'] ?? 0,
            'pending_fees' => $pendingFees,
            'recent_results' => $recentResults,
            'upcoming_events' => $upcomingEvents
        ];

        $this->render('student/dashboard', ['student' => $student, 'stats' => $stats]);
    }

    public function profile() {
        $studentId = $_SESSION['user']['id'];
        $student = $this->db->selectOne("SELECT * FROM students WHERE id = ?", [$studentId]);
        $classes = $this->db->select("SELECT * FROM classes WHERE is_active = 1 ORDER BY class_name");

        $this->render('student/profile', ['student' => $student, 'classes' => $classes]);
    }

    public function updateProfile() {
        $studentId = $_SESSION['user']['id'];

        $data = [
            'mobile' => $_POST['mobile'] ?? '',
            'email' => $_POST['email'] ?? '',
            'address' => $_POST['address'] ?? '',
            'permanent_address' => $_POST['permanent_address'] ?? '',
            'guardian_contact' => $_POST['guardian_contact'] ?? '',
            'medical_conditions' => $_POST['medical_conditions'] ?? '',
            'csrf_token' => $_POST['csrf_token'] ?? ''
        ];

        if (!$this->checkCsrfToken($data['csrf_token'])) {
            $this->session->setFlash('error', 'Invalid CSRF token');
            $this->redirect('/student/profile');
        }

        $rules = [
            'mobile' => 'required|numeric|min:10|max:15',
            'email' => 'email'
        ];

        if (!$this->validate($data, $rules)) {
            $this->session->setFlash('errors', $this->getValidationErrors());
            $this->session->setFlash('old', $data);
            $this->redirect('/student/profile');
        }

        // Handle photo upload
        $photoPath = null;
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

        $updateData = $data;
        unset($updateData['csrf_token']);
        if ($photoPath) {
            $updateData['photo'] = $photoPath;
        }

        $updated = $this->db->update('students', $updateData, 'id = ?', [$studentId]);

        if ($updated) {
            $this->session->setFlash('success', 'Profile updated successfully');
        } else {
            $this->session->setFlash('error', 'Failed to update profile');
        }

        $this->redirect('/student/profile');
    }

    public function attendance() {
        $studentId = $_SESSION['user']['id'];

        // Get attendance records with pagination
        $page = $_GET['page'] ?? 1;
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        $attendance = $this->db->select("
            SELECT a.*, c.class_name, c.section
            FROM attendance a
            LEFT JOIN classes c ON a.class_id = c.id
            WHERE a.student_id = ?
            ORDER BY a.attendance_date DESC
            LIMIT " . (int)$perPage . " OFFSET " . (int)$offset
        , [$studentId]);

        $totalRecords = $this->db->selectOne("SELECT COUNT(*) as count FROM attendance WHERE student_id = ?", [$studentId])['count'];
        $totalPages = ceil($totalRecords / $perPage);

        $this->render('student/attendance', [
            'attendance' => $attendance,
            'current_page' => $page,
            'total_pages' => $totalPages
        ]);
    }

    public function results() {
        $studentId = $_SESSION['user']['id'];

        $results = $this->db->select("
            SELECT er.*, e.exam_name, e.exam_type, s.subject_name, s.subject_code
            FROM exam_results er
            LEFT JOIN exams e ON er.exam_id = e.id
            LEFT JOIN subjects s ON er.subject_id = s.id
            WHERE er.student_id = ?
            ORDER BY e.start_date DESC, s.subject_name
        ", [$studentId]);

        // Group by exam
        $groupedResults = [];
        foreach ($results as $result) {
            $examId = $result['exam_id'];
            if (!isset($groupedResults[$examId])) {
                $groupedResults[$examId] = [
                    'exam_name' => $result['exam_name'],
                    'exam_type' => $result['exam_type'],
                    'start_date' => $result['start_date'],
                    'subjects' => []
                ];
            }
            $groupedResults[$examId]['subjects'][] = $result;
        }

        $this->render('student/results', ['results' => $groupedResults]);
    }

    public function fees() {
        $studentId = $_SESSION['user']['id'];

        // Get fee records
        $fees = $this->db->select("
            SELECT f.*, fp.amount_paid, fp.payment_date, fp.payment_mode, fp.transaction_id
            FROM fees f
            LEFT JOIN fee_payments fp ON f.id = fp.fee_id
            WHERE f.student_id = ?
            ORDER BY f.due_date DESC
        ", [$studentId]);

        // Calculate totals
        $totalFees = array_sum(array_column($fees, 'amount'));
        $totalPaid = array_sum(array_column($fees, 'amount_paid'));

        $this->render('student/fees', [
            'fees' => $fees,
            'total_fees' => $totalFees,
            'total_paid' => $totalPaid,
            'pending_amount' => $totalFees - $totalPaid
        ]);
    }

    public function events() {
        $events = $this->db->select("SELECT * FROM events WHERE is_active = 1 ORDER BY event_date DESC");
        $this->render('student/events', ['events' => $events]);
    }

    public function resources() {
        $studentId = $_SESSION['user']['id'];

        // Get student's class and subjects
        $student = $this->db->selectOne("SELECT s.*, c.class_name, c.section FROM students s LEFT JOIN classes c ON s.class_id = c.id WHERE s.id = ?", [$studentId]);

        // Get study materials (simplified - in production, this would be a separate table)
        $studyMaterials = $this->db->select("
            SELECT * FROM events
            WHERE is_active = 1 AND (title LIKE '%study%' OR title LIKE '%material%' OR description LIKE '%study%')
            ORDER BY event_date DESC
            LIMIT 10
        ");

        // Get assignments (simplified - in production, this would be a separate assignments table)
        $assignments = $this->db->select("
            SELECT * FROM events
            WHERE is_active = 1 AND (title LIKE '%assignment%' OR title LIKE '%homework%' OR description LIKE '%assignment%')
            ORDER BY event_date DESC
            LIMIT 10
        ");

        // Get library resources (simplified)
        $libraryResources = [
            ['title' => 'Digital Library', 'description' => 'Access to online books and resources', 'link' => '#'],
            ['title' => 'Study Guides', 'description' => 'Download study materials and guides', 'link' => '#'],
            ['title' => 'Video Tutorials', 'description' => 'Educational video content', 'link' => '#'],
            ['title' => 'Practice Tests', 'description' => 'Sample question papers and tests', 'link' => '#']
        ];

        $this->render('student/resources', [
            'student' => $student,
            'study_materials' => $studyMaterials,
            'assignments' => $assignments,
            'library_resources' => $libraryResources
        ]);
    }

    public function changePassword() {
        $this->render('student/change-password');
    }

    public function updatePassword() {
        $data = [
            'current_password' => $_POST['current_password'] ?? '',
            'new_password' => $_POST['new_password'] ?? '',
            'confirm_password' => $_POST['confirm_password'] ?? '',
            'csrf_token' => $_POST['csrf_token'] ?? ''
        ];

        if (!$this->checkCsrfToken($data['csrf_token'])) {
            $this->session->setFlash('error', 'Invalid CSRF token');
            $this->redirect('/student/change-password');
        }

        $rules = [
            'current_password' => 'required',
            'new_password' => 'required|min:8',
            'confirm_password' => 'required|same:new_password'
        ];

        if (!$this->validate($data, $rules)) {
            $this->session->setFlash('errors', $this->getValidationErrors());
            $this->redirect('/student/change-password');
        }

        $userId = $_SESSION['user']['id'];
        $user = $this->db->selectOne("SELECT * FROM users WHERE id = ?", [$userId]);

        if (!password_verify($data['current_password'], $user['password'])) {
            $this->session->setFlash('error', 'Current password is incorrect');
            $this->redirect('/student/change-password');
        }

        $hashedPassword = password_hash($data['new_password'], PASSWORD_DEFAULT);
        $updated = $this->db->update('users', ['password' => $hashedPassword], 'id = ?', [$userId]);

        if ($updated) {
            $this->session->setFlash('success', 'Password changed successfully');
        } else {
            $this->session->setFlash('error', 'Failed to change password');
        }

        $this->redirect('/student/change-password');
    }

    private function jsonResponse($data, $statusCode = 200) {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
        exit;
    }

    public function initiatePayment() {
        $studentId = $_SESSION['user']['id'];

        $data = [
            'fee_id' => $_POST['fee_id'] ?? '',
            'amount' => $_POST['amount'] ?? '',
            'csrf_token' => $_POST['csrf_token'] ?? ''
        ];

        if (!$this->checkCsrfToken($data['csrf_token'])) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid CSRF token'], 400);
        }

        $rules = [
            'fee_id' => 'required|integer',
            'amount' => 'required|numeric|min:1'
        ];

        if (!$this->validate($data, $rules)) {
            $this->jsonResponse(['success' => false, 'message' => 'Validation failed', 'errors' => $this->getValidationErrors()], 400);
        }

        // Verify fee belongs to student
        $fee = $this->db->selectOne("SELECT * FROM fees WHERE id = ? AND student_id = ?", [$data['fee_id'], $studentId]);
        if (!$fee) {
            $this->jsonResponse(['success' => false, 'message' => 'Fee not found or access denied'], 404);
        }

        // Load payment config
        $paymentConfig = require __DIR__ . '/../config/payment.php';
        if (!$paymentConfig['enabled']) {
            $this->jsonResponse(['success' => false, 'message' => 'Payment processing is disabled'], 400);
        }

        $gateway = $paymentConfig['default_gateway'];
        $gatewayConfig = $paymentConfig[$gateway];

        try {
            $paymentData = $this->createPaymentOrder($gateway, $gatewayConfig, $data['amount'], $fee);

            // Insert payment record
            $paymentId = $this->db->insert('fee_payments', [
                'fee_id' => $data['fee_id'],
                'amount_paid' => $data['amount'],
                'payment_date' => date('Y-m-d'),
                'payment_mode' => 'online',
                'payment_gateway' => $gateway,
                'payment_status' => 'pending',
                'transaction_id' => $paymentData['order_id'] ?? $paymentData['id'],
                'collected_by' => $studentId
            ]);

            $this->jsonResponse([
                'success' => true,
                'payment_id' => $paymentId,
                'gateway' => $gateway,
                'order_data' => $paymentData
            ]);

        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => 'Payment initiation failed: ' . $e->getMessage()], 500);
        }
    }

    public function viewPaymentHistory() {
        $studentId = $_SESSION['user']['id'];

        $payments = $this->db->select("
            SELECT fp.*, f.fee_type, f.amount as fee_amount, f.due_date
            FROM fee_payments fp
            LEFT JOIN fees f ON fp.fee_id = f.id
            WHERE f.student_id = ?
            ORDER BY fp.created_at DESC
        ", [$studentId]);

        $this->jsonResponse(['success' => true, 'payments' => $payments]);
    }

    public function getPaymentStatus() {
        $studentId = $_SESSION['user']['id'];

        $data = [
            'payment_id' => $_GET['payment_id'] ?? $_POST['payment_id'] ?? ''
        ];

        $rules = [
            'payment_id' => 'required|integer'
        ];

        if (!$this->validate($data, $rules)) {
            $this->jsonResponse(['success' => false, 'message' => 'Validation failed', 'errors' => $this->getValidationErrors()], 400);
        }

        // Verify payment belongs to student
        $payment = $this->db->selectOne("
            SELECT fp.*, f.student_id
            FROM fee_payments fp
            LEFT JOIN fees f ON fp.fee_id = f.id
            WHERE fp.id = ? AND f.student_id = ?
        ", [$data['payment_id'], $studentId]);

        if (!$payment) {
            $this->jsonResponse(['success' => false, 'message' => 'Payment not found or access denied'], 404);
        }

        $this->jsonResponse(['success' => true, 'payment' => $payment]);
    }

    private function createPaymentOrder($gateway, $config, $amount, $fee) {
        $amountInPaise = round($amount * 100); // For INR

        if ($gateway === 'razorpay') {
            return $this->createRazorpayOrder($config, $amountInPaise, $fee);
        } elseif ($gateway === 'stripe') {
            return $this->createStripePaymentIntent($config, $amountInPaise, $fee);
        } else {
            throw new Exception('Unsupported payment gateway');
        }
    }

    private function createRazorpayOrder($config, $amount, $fee) {
        $url = 'https://api.razorpay.com/v1/orders';
        $data = [
            'amount' => $amount,
            'currency' => 'INR',
            'receipt' => 'fee_' . $fee['id'],
            'payment_capture' => 1
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Basic ' . base64_encode($config['key_id'] . ':' . $config['key_secret'])
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            throw new Exception('Razorpay API error: ' . $response);
        }

        return json_decode($response, true);
    }

    private function createStripePaymentIntent($config, $amount, $fee) {
        $url = 'https://api.stripe.com/v1/payment_intents';
        $data = [
            'amount' => $amount,
            'currency' => 'inr',
            'metadata' => ['fee_id' => $fee['id']]
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $config['secret_key']
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            throw new Exception('Stripe API error: ' . $response);
        }

        return json_decode($response, true);
    }
}