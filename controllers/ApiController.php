<?php
/**
 * API Controller - RESTful API Endpoints
 */

class ApiController extends Controller {

    public function __construct() {
        parent::__construct();
        // API endpoints might need different authentication
        // For now, we'll keep basic auth
    }

    // Authentication endpoints
    public function login() {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!$data || !isset($data['username']) || !isset($data['password'])) {
            $this->json(['success' => false, 'message' => 'Username and password are required'], 400);
        }

        $user = $this->db->selectOne(
            "SELECT * FROM users WHERE (username = ? OR email = ?) AND is_active = 1",
            [$data['username'], $data['username']]
        );

        if ($user && $this->security->verifyPassword($data['password'], $user['password'])) {
            // Generate API token (simplified - in production use JWT)
            $token = bin2hex(random_bytes(32));

            // Store token (simplified - in production use proper token storage)
            $_SESSION['api_token'] = $token;
            $_SESSION['api_user'] = $user['id'];

            $this->json([
                'success' => true,
                'message' => 'Login successful',
                'token' => $token,
                'user' => [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'email' => $user['email'],
                    'role' => $user['role'],
                    'first_name' => $user['first_name'],
                    'last_name' => $user['last_name']
                ]
            ]);
        } else {
            $this->json(['success' => false, 'message' => 'Invalid credentials'], 401);
        }
    }

    // Student data endpoints
    public function getStudents() {
        $this->checkApiAuth();

        $page = $_GET['page'] ?? 1;
        $limit = $_GET['limit'] ?? 20;
        $offset = ($page - 1) * $limit;

        $students = $this->db->select("
            SELECT s.*, c.class_name, c.section
            FROM students s
            LEFT JOIN classes c ON s.class_id = c.id
            WHERE s.is_active = 1
            ORDER BY s.first_name, s.last_name
            LIMIT ? OFFSET ?
        ", [$limit, $offset]);

        $total = $this->db->selectOne("SELECT COUNT(*) as count FROM students WHERE is_active = 1")['count'];

        $this->json([
            'success' => true,
            'data' => $students,
            'pagination' => [
                'page' => (int)$page,
                'limit' => (int)$limit,
                'total' => (int)$total,
                'pages' => ceil($total / $limit)
            ]
        ]);
    }

    public function getStudent($id) {
        $this->checkApiAuth();

        $student = $this->db->selectOne("
            SELECT s.*, c.class_name, c.section
            FROM students s
            LEFT JOIN classes c ON s.class_id = c.id
            WHERE s.id = ? AND s.is_active = 1
        ", [$id]);

        if (!$student) {
            $this->json(['success' => false, 'message' => 'Student not found'], 404);
        }

        $this->json(['success' => true, 'data' => $student]);
    }

    // Fee management endpoints
    public function getFees() {
        $this->checkApiAuth();

        $studentId = $_GET['student_id'] ?? null;

        $query = "SELECT f.*, s.first_name, s.last_name, s.scholar_number FROM fees f LEFT JOIN students s ON f.student_id = s.id";
        $params = [];

        if ($studentId) {
            $query .= " WHERE f.student_id = ?";
            $params[] = $studentId;
        }

        $query .= " ORDER BY f.created_at DESC";

        $fees = $this->db->select($query, $params);
        $this->json(['success' => true, 'data' => $fees]);
    }

    public function getStudentFees($studentId) {
        $this->checkApiAuth();

        $fees = $this->db->select("
            SELECT f.*, fp.amount_paid, fp.payment_date, fp.payment_mode
            FROM fees f
            LEFT JOIN fee_payments fp ON f.id = fp.fee_id
            WHERE f.student_id = ?
            ORDER BY f.due_date DESC
        ", [$studentId]);

        $this->json(['success' => true, 'data' => $fees]);
    }

    // Exam endpoints
    public function getExams() {
        $this->checkApiAuth();

        $exams = $this->db->select("
            SELECT e.*, c.class_name, c.section
            FROM exams e
            LEFT JOIN classes c ON e.class_id = c.id
            WHERE e.is_active = 1
            ORDER BY e.start_date DESC
        ");

        $this->json(['success' => true, 'data' => $exams]);
    }

    public function getExamResults($examId) {
        $this->checkApiAuth();

        $results = $this->db->select("
            SELECT er.*, s.first_name, s.last_name, s.scholar_number, sub.subject_name
            FROM exam_results er
            LEFT JOIN students s ON er.student_id = s.id
            LEFT JOIN subjects sub ON er.subject_id = sub.id
            WHERE er.exam_id = ?
            ORDER BY s.first_name, s.last_name, sub.subject_name
        ", [$examId]);

        $this->json(['success' => true, 'data' => $results]);
    }

    // Attendance endpoints
    public function getAttendance() {
        $this->checkApiAuth();

        $studentId = $_GET['student_id'] ?? null;
        $classId = $_GET['class_id'] ?? null;
        $date = $_GET['date'] ?? null;

        $query = "SELECT a.*, s.first_name, s.last_name, s.scholar_number, c.class_name, c.section FROM attendance a LEFT JOIN students s ON a.student_id = s.id LEFT JOIN classes c ON a.class_id = c.id WHERE 1=1";
        $params = [];

        if ($studentId) {
            $query .= " AND a.student_id = ?";
            $params[] = $studentId;
        }

        if ($classId) {
            $query .= " AND a.class_id = ?";
            $params[] = $classId;
        }

        if ($date) {
            $query .= " AND a.attendance_date = ?";
            $params[] = $date;
        }

        $query .= " ORDER BY a.attendance_date DESC, s.first_name, s.last_name";

        $attendance = $this->db->select($query, $params);
        $this->json(['success' => true, 'data' => $attendance]);
    }

    // Reports endpoint
    public function getReports() {
        $this->checkApiAuth();

        $type = $_GET['type'] ?? 'students';

        switch ($type) {
            case 'students':
                $data = $this->db->select("SELECT COUNT(*) as total_students FROM students WHERE is_active = 1");
                break;
            case 'attendance':
                $data = $this->db->selectOne("
                    SELECT
                        COUNT(*) as total_records,
                        SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_count,
                        SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent_count,
                        ROUND(AVG(CASE WHEN status = 'present' THEN 1 ELSE 0 END) * 100, 2) as attendance_rate
                    FROM attendance
                ");
                break;
            case 'fees':
                $data = $this->db->selectOne("
                    SELECT
                        SUM(amount) as total_fees,
                        SUM(amount_paid) as total_paid,
                        SUM(amount) - SUM(amount_paid) as total_pending
                    FROM fees f
                    LEFT JOIN fee_payments fp ON f.id = fp.fee_id
                ");
                break;
            default:
                $data = ['message' => 'Invalid report type'];
        }

        $this->json(['success' => true, 'data' => $data]);
    }

    // Utility methods
    private function checkApiAuth() {
        $headers = getallheaders();
        $token = $headers['Authorization'] ?? $headers['X-API-Token'] ?? $_GET['token'] ?? null;

        if (!$token) {
            $this->json(['success' => false, 'message' => 'API token required'], 401);
        }

        // Simplified token validation (in production, validate against database)
        if (!isset($_SESSION['api_token']) || $_SESSION['api_token'] !== $token) {
            $this->json(['success' => false, 'message' => 'Invalid API token'], 401);
        }
    }

    // Webhook handlers
    public function razorpayWebhook() {
        // Security check: only POST requests
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            error_log('Razorpay Webhook: Invalid request method');
            exit;
        }

        $config = require __DIR__ . '/../config/payment.php';
        $secret = $config['razorpay']['key_secret'];

        if (empty($secret)) {
            error_log('Razorpay Webhook: Secret not configured');
            http_response_code(500);
            exit;
        }

        // Get raw body and signature
        $body = file_get_contents('php://input');
        $signature = $_SERVER['HTTP_X_RAZORPAY_SIGNATURE'] ?? '';

        if (empty($signature)) {
            error_log('Razorpay Webhook: Missing signature');
            http_response_code(400);
            exit;
        }

        // Verify signature
        $expectedSignature = hash_hmac('sha256', $body, $secret);
        if (!hash_equals($expectedSignature, $signature)) {
            error_log('Razorpay Webhook: Invalid signature');
            http_response_code(400);
            exit;
        }

        // Parse payload
        $payload = json_decode($body, true);
        if (!$payload || !isset($payload['event'])) {
            error_log('Razorpay Webhook: Invalid payload');
            http_response_code(400);
            exit;
        }

        error_log('Razorpay Webhook: Received event - ' . $payload['event']);

        if ($payload['event'] === 'payment.captured') {
            $paymentId = $payload['payment']['entity']['id'];
            $orderId = $payload['payment']['entity']['order_id'] ?? null;
            $amount = $payload['payment']['entity']['amount'] / 100; // Amount in rupees

            // Find fee_payment by transaction_id (assuming transaction_id is set to payment_id during initiation)
            $payment = $this->db->selectOne("SELECT * FROM fee_payments WHERE transaction_id = ? AND payment_gateway = 'razorpay'", [$paymentId]);

            if ($payment) {
                // Update payment status
                $this->db->update(
                    "UPDATE fee_payments SET payment_status = 'completed', payment_date = CURDATE(), amount_paid = ? WHERE id = ?",
                    [$amount, $payment['id']]
                );

                // Check if fee is fully paid
                $totalPaid = $this->db->selectOne("SELECT SUM(amount_paid) as total FROM fee_payments WHERE fee_id = ? AND payment_status = 'completed'", [$payment['fee_id']])['total'];
                $feeAmount = $this->db->selectOne("SELECT amount FROM fees WHERE id = ?", [$payment['fee_id']])['amount'];

                if ($totalPaid >= $feeAmount) {
                    $this->db->update("UPDATE fees SET is_paid = 1 WHERE id = ?", [$payment['fee_id']]);
                }

                error_log('Razorpay Webhook: Payment updated successfully for fee_id ' . $payment['fee_id']);
            } else {
                error_log('Razorpay Webhook: Payment not found for transaction_id ' . $paymentId);
            }
        }

        // Respond with success
        http_response_code(200);
        echo 'OK';
        exit;
    }

    public function stripeWebhook() {
        // Security check: only POST requests
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            error_log('Stripe Webhook: Invalid request method');
            exit;
        }

        $config = require __DIR__ . '/../config/payment.php';
        $secret = $config['stripe']['webhook_secret'];

        if (empty($secret)) {
            error_log('Stripe Webhook: Secret not configured');
            http_response_code(500);
            exit;
        }

        // Get raw body and signature
        $body = file_get_contents('php://input');
        $signature = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

        if (empty($signature)) {
            error_log('Stripe Webhook: Missing signature');
            http_response_code(400);
            exit;
        }

        // Verify signature (simplified - in production use Stripe SDK)
        // Stripe signature format: t=1234567890,v1=signature
        $signatureParts = explode(',', $signature);
        $timestamp = '';
        $v1Signature = '';
        foreach ($signatureParts as $part) {
            if (strpos($part, 't=') === 0) {
                $timestamp = substr($part, 2);
            } elseif (strpos($part, 'v1=') === 0) {
                $v1Signature = substr($part, 3);
            }
        }

        $signedPayload = $timestamp . '.' . $body;
        $expectedSignature = hash_hmac('sha256', $signedPayload, $secret);

        if (!hash_equals($expectedSignature, $v1Signature)) {
            error_log('Stripe Webhook: Invalid signature');
            http_response_code(400);
            exit;
        }

        // Parse payload
        $payload = json_decode($body, true);
        if (!$payload || !isset($payload['type'])) {
            error_log('Stripe Webhook: Invalid payload');
            http_response_code(400);
            exit;
        }

        error_log('Stripe Webhook: Received event - ' . $payload['type']);

        if ($payload['type'] === 'payment_intent.succeeded') {
            $paymentIntent = $payload['data']['object'];
            $paymentId = $paymentIntent['id'];
            $amount = $paymentIntent['amount'] / 100; // Amount in dollars (assuming USD)

            // Find fee_payment by transaction_id
            $payment = $this->db->selectOne("SELECT * FROM fee_payments WHERE transaction_id = ? AND payment_gateway = 'stripe'", [$paymentId]);

            if ($payment) {
                // Update payment status
                $this->db->update(
                    "UPDATE fee_payments SET payment_status = 'completed', payment_date = CURDATE(), amount_paid = ? WHERE id = ?",
                    [$amount, $payment['id']]
                );

                // Check if fee is fully paid
                $totalPaid = $this->db->selectOne("SELECT SUM(amount_paid) as total FROM fee_payments WHERE fee_id = ? AND payment_status = 'completed'", [$payment['fee_id']])['total'];
                $feeAmount = $this->db->selectOne("SELECT amount FROM fees WHERE id = ?", [$payment['fee_id']])['amount'];

                if ($totalPaid >= $feeAmount) {
                    $this->db->update("UPDATE fees SET is_paid = 1 WHERE id = ?", [$payment['fee_id']]);
                }

                error_log('Stripe Webhook: Payment updated successfully for fee_id ' . $payment['fee_id']);
            } else {
                error_log('Stripe Webhook: Payment not found for transaction_id ' . $paymentId);
            }
        }

        // Respond with success
        http_response_code(200);
        echo 'OK';
        exit;
    }

    private function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}