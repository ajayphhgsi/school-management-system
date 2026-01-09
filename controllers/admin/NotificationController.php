<?php
/**
 * Admin Notification Controller
 */

class NotificationController extends Controller {

    public function __construct() {
        parent::__construct();
        $this->middleware('auth');
        $this->middleware('admin');
    }

    private function getCurrentAcademicYearId() {
        return $_SESSION['academic_year_id'] ?? null;
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
}