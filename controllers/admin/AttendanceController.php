<?php
/**
 * Admin Attendance Controller
 */

class AttendanceController extends Controller {

    public function __construct() {
        parent::__construct();
        $this->middleware('auth');
        $this->middleware('admin');
    }

    private function getCurrentAcademicYearId() {
        return $_SESSION['academic_year_id'] ?? null;
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
}