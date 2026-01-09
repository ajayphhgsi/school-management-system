<?php
/**
 * Admin Exam Controller
 */

class ExamController extends Controller {

    public function __construct() {
        parent::__construct();
        $this->middleware('auth');
        $this->middleware('admin');
    }

    private function getCurrentAcademicYearId() {
        return $_SESSION['academic_year_id'] ?? null;
    }

    public function index() {
        $academicYearId = $this->getCurrentAcademicYearId();
        $where = "WHERE e.is_active = 1";
        $params = [];

        if ($academicYearId) {
            $where .= " AND e.academic_year_id = ?";
            $params = [$academicYearId];
        }

        $exams = $this->db->select("
            SELECT e.*,
                   GROUP_CONCAT(DISTINCT CONCAT(c.class_name, ' ', c.section) SEPARATOR ', ') as class_names,
                   COUNT(DISTINCT es.class_id) as class_count
            FROM exams e
            LEFT JOIN exam_subjects es ON e.id = es.exam_id
            LEFT JOIN classes c ON es.class_id = c.id
            $where
            GROUP BY e.id
            ORDER BY e.start_date DESC
        ", $params);

        $this->render('admin/exams/index', ['exams' => $exams]);
    }

    public function generateAdmitCards() {
        $data = $_POST;

        if (!$data || !isset($data['exam_id'])) {
            $this->json(['success' => false, 'message' => 'Invalid data'], 400);
        }

        $examId = $data['exam_id'];
        $classIds = $data['class_ids'] ?? [];
        $includePhotos = isset($data['include_photos']);
        $includeSignatures = isset($data['include_signatures']);

        if (empty($classIds)) {
            $this->json(['success' => false, 'message' => 'Please select at least one class'], 400);
        }

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

        // Get students for selected classes
        $placeholders = str_repeat('?,', count($classIds) - 1) . '?';
        $students = $this->db->select("
            SELECT s.*, c.class_name, c.section
            FROM students s
            LEFT JOIN classes c ON s.class_id = c.id
            WHERE s.class_id IN ($placeholders) AND s.is_active = 1 AND s.tc_issued = 0
            ORDER BY c.class_name, s.first_name, s.last_name
        ", $classIds);

        if (empty($students)) {
            $this->json(['success' => false, 'message' => 'No students found for the selected classes'], 404);
        }

        // Get exam subjects with dates and times for the selected classes
        $subjectPlaceholders = str_repeat('?,', count($classIds) - 1) . '?';
        $subjectParams = array_merge([$examId], $classIds);
        $examSubjects = $this->db->select("
            SELECT es.*, s.subject_name, s.subject_code, c.class_name, c.section
            FROM exam_subjects es
            LEFT JOIN subjects s ON es.subject_id = s.id
            LEFT JOIN classes c ON es.class_id = c.id
            WHERE es.exam_id = ? AND es.class_id IN ($subjectPlaceholders)
            ORDER BY es.exam_date, es.start_time
        ", $subjectParams);

        // Get school settings
        $schoolName = $this->db->selectOne("SELECT setting_value FROM settings WHERE setting_key = 'school_name'")['setting_value'] ?? 'School Management System';
        $schoolAddress = $this->db->selectOne("SELECT setting_value FROM settings WHERE setting_key = 'school_address'")['setting_value'] ?? '';

        // Prepare data for view
        $viewData = [
            'exam' => $exam,
            'students' => $students,
            'examSubjects' => $examSubjects,
            'includePhotos' => $includePhotos,
            'includeSignatures' => $includeSignatures,
            'cardsPerPage' => 1, // Single card per page
            'schoolName' => $schoolName,
            'schoolAddress' => $schoolAddress
        ];

        // Generate HTML using view
        extract($viewData);
        ob_start();
        include BASE_PATH . 'views/admin/exams/print_admitcard.php';
        $html = ob_get_clean();

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
            'message' => 'Admit cards generated successfully for ' . count($classIds) . ' classes',
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

        // Get exam subjects with dates and times
        $examSubjects = $this->db->select("
            SELECT es.*, s.subject_name, s.subject_code
            FROM exam_subjects es
            LEFT JOIN subjects s ON es.subject_id = s.id
            WHERE es.exam_id = ?
            ORDER BY es.exam_date, es.start_time
        ", [$examId]);

        // Get important instructions from database
        $instructions = $this->db->select("SELECT instruction_text FROM admit_card_instructions WHERE is_active = 1 ORDER BY id");

        // Get school settings
        $schoolName = $this->db->selectOne("SELECT setting_value FROM settings WHERE setting_key = 'school_name'")['setting_value'] ?? 'School Management System';
        $schoolAddress = $this->db->selectOne("SELECT setting_value FROM settings WHERE setting_key = 'school_address'")['setting_value'] ?? '';

        // Prepare data for view
        $viewData = [
            'exam' => $exam,
            'students' => [$student],
            'examSubjects' => $examSubjects,
            'instructions' => $instructions,
            'includePhotos' => $includePhotos,
            'includeSignatures' => $includeSignatures,
            'cardsPerPage' => 1,
            'schoolName' => $schoolName,
            'schoolAddress' => $schoolAddress
        ];

        // Generate HTML using view
        extract($viewData);
        ob_start();
        include BASE_PATH . 'views/admin/exams/print_admitcard.php';
        $html = ob_get_clean();

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

    public function printAdmitCard($examId, $studentId) {
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

        // Get student
        $student = $this->db->selectOne("
            SELECT s.*, c.class_name, c.section
            FROM students s
            LEFT JOIN classes c ON s.class_id = c.id
            WHERE s.id = ? AND s.is_active = 1
        ", [$studentId]);

        if (!$student) {
            $this->session->setFlash('error', 'Student not found');
            $this->redirect('/admin/exams');
        }

        // Get exam subjects with dates and times for this student's class only
        $examSubjects = $this->db->select("
            SELECT es.*, s.subject_name, s.subject_code
            FROM exam_subjects es
            LEFT JOIN subjects s ON es.subject_id = s.id
            WHERE es.exam_id = ? AND es.class_id = ?
            ORDER BY es.exam_date, es.start_time
        ", [$examId, $student['class_id']]);

        // Get important instructions from database
        $instructions = $this->db->select("SELECT instruction_text FROM admit_card_instructions WHERE is_active = 1 ORDER BY id");

        // Get school settings
        $schoolName = $this->db->selectOne("SELECT setting_value FROM settings WHERE setting_key = 'school_name'")['setting_value'] ?? 'School Management System';
        $schoolAddress = $this->db->selectOne("SELECT setting_value FROM settings WHERE setting_key = 'school_address'")['setting_value'] ?? '';
        $schoolLogo = $this->db->selectOne("SELECT setting_value FROM settings WHERE setting_key = 'school_logo'")['setting_value'] ?? '';

        // Prepare data for view
        $viewData = [
            'exam' => $exam,
            'students' => [$student],
            'examSubjects' => $examSubjects,
            'instructions' => $instructions,
            'includePhotos' => true,
            'includeSignatures' => true,
            'cardsPerPage' => 1,
            'schoolName' => $schoolName,
            'schoolAddress' => $schoolAddress,
            'schoolLogo' => $schoolLogo
        ];

        // Render the view directly
        extract($viewData);
        include BASE_PATH . 'views/admin/exams/print_admitcard.php';
    }

    public function printAdmitCards($examId) {
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

        // Get all classes that have subjects for this exam
        $examClasses = $this->db->select("
            SELECT DISTINCT c.id, c.class_name, c.section
            FROM exam_subjects es
            LEFT JOIN classes c ON es.class_id = c.id
            WHERE es.exam_id = ?
            ORDER BY c.class_name
        ", [$examId]);

        if (empty($examClasses)) {
            $this->session->setFlash('error', 'No classes found for this exam');
            $this->redirect('/admin/exams');
        }

        // Get class IDs
        $classIds = array_column($examClasses, 'id');
        $placeholders = str_repeat('?,', count($classIds) - 1) . '?';

        // Get all students for the relevant classes
        $students = $this->db->select("
            SELECT s.*, c.class_name, c.section
            FROM students s
            LEFT JOIN classes c ON s.class_id = c.id
            WHERE s.class_id IN ($placeholders) AND s.is_active = 1 AND s.tc_issued = 0
            ORDER BY c.class_name, s.first_name, s.last_name
        ", $classIds);

        if (empty($students)) {
            $this->session->setFlash('error', 'No students found for this exam');
            $this->redirect('/admin/exams');
        }

        // Get exam subjects with dates and times (all subjects for the exam)
        $examSubjects = $this->db->select("
            SELECT es.*, s.subject_name, s.subject_code
            FROM exam_subjects es
            LEFT JOIN subjects s ON es.subject_id = s.id
            WHERE es.exam_id = ?
            ORDER BY es.exam_date, es.start_time
        ", [$examId]);

        // Get important instructions from database
        $instructions = $this->db->select("SELECT instruction_text FROM admit_card_instructions WHERE is_active = 1 ORDER BY id");

        // Get school settings
        $schoolName = $this->db->selectOne("SELECT setting_value FROM settings WHERE setting_key = 'school_name'")['setting_value'] ?? 'School Management System';
        $schoolAddress = $this->db->selectOne("SELECT setting_value FROM settings WHERE setting_key = 'school_address'")['setting_value'] ?? '';
        $schoolLogo = $this->db->selectOne("SELECT setting_value FROM settings WHERE setting_key = 'school_logo'")['setting_value'] ?? '';

        // Prepare data for view
        $viewData = [
            'exam' => $exam,
            'students' => $students,
            'examSubjects' => $examSubjects,
            'instructions' => $instructions,
            'includePhotos' => true,
            'includeSignatures' => true,
            'cardsPerPage' => 1, // Single card per page for better timetable display
            'schoolName' => $schoolName,
            'schoolAddress' => $schoolAddress,
            'schoolLogo' => $schoolLogo
        ];

        // Render the view directly
        extract($viewData);
        include BASE_PATH . 'views/admin/exams/print_admitcard.php';
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

        // Get school settings
        $schoolName = $this->db->selectOne("SELECT setting_value FROM settings WHERE setting_key = 'school_name'")['setting_value'] ?? 'School Management System';
        $schoolAddress = $this->db->selectOne("SELECT setting_value FROM settings WHERE setting_key = 'school_address'")['setting_value'] ?? '';

        // Prepare data for view
        $viewData = [
            'exam' => $exam,
            'students' => $students,
            'includePhotos' => $includePhotos,
            'includeGrades' => $includeGrades,
            'includeRankings' => $includeRankings,
            'marksheetsPerPage' => $marksheetsPerPage,
            'schoolName' => $schoolName,
            'schoolAddress' => $schoolAddress
        ];

        // Generate HTML using view
        extract($viewData);
        ob_start();
        include BASE_PATH . 'views/admin/exams/print_marksheet.php';
        $html = ob_get_clean();

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

        // Get school settings
        $schoolName = $this->db->selectOne("SELECT setting_value FROM settings WHERE setting_key = 'school_name'")['setting_value'] ?? 'School Management System';
        $schoolAddress = $this->db->selectOne("SELECT setting_value FROM settings WHERE setting_key = 'school_address'")['setting_value'] ?? '';

        // Prepare data for view
        $viewData = [
            'exam' => $exam,
            'students' => [$student],
            'includePhotos' => $includePhotos,
            'includeGrades' => $includeGrades,
            'includeRankings' => $includeRankings,
            'marksheetsPerPage' => 1,
            'schoolName' => $schoolName,
            'schoolAddress' => $schoolAddress
        ];

        // Generate HTML using view
        extract($viewData);
        ob_start();
        include BASE_PATH . 'views/admin/exams/print_marksheet.php';
        $html = ob_get_clean();

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

    public function printMarksheet($examId, $studentId) {
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

        // Get student with results
        $student = $this->db->selectOne("
            SELECT s.*, c.class_name, c.section
            FROM students s
            LEFT JOIN classes c ON s.class_id = c.id
            WHERE s.id = ? AND s.is_active = 1
        ", [$studentId]);

        if (!$student) {
            $this->session->setFlash('error', 'Student not found');
            $this->redirect('/admin/exams');
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

        // Calculate ranking
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

        // Get school settings
        $schoolName = $this->db->selectOne("SELECT setting_value FROM settings WHERE setting_key = 'school_name'")['setting_value'] ?? 'School Management System';
        $schoolAddress = $this->db->selectOne("SELECT setting_value FROM settings WHERE setting_key = 'school_address'")['setting_value'] ?? '';

        // Prepare data for view
        $viewData = [
            'exam' => $exam,
            'students' => [$student],
            'includePhotos' => true,
            'includeGrades' => true,
            'includeRankings' => true,
            'marksheetsPerPage' => 1,
            'schoolName' => $schoolName,
            'schoolAddress' => $schoolAddress
        ];

        // Render the view directly
        extract($viewData);
        include BASE_PATH . 'views/admin/exams/print_marksheet.php';
    }

    public function viewExam($examId) {
        // Get exam details with class and academic year info
        $exam = $this->db->selectOne("
            SELECT e.*, c.class_name, c.section, ay.year_name
            FROM exams e
            LEFT JOIN classes c ON e.class_id = c.id
            LEFT JOIN academic_years ay ON e.academic_year_id = ay.id
            WHERE e.id = ?
        ", [$examId]);

        if (!$exam) {
            $this->session->setFlash('error', 'Exam not found');
            $this->redirect('/admin/exams');
        }

        // Get exam subjects
        $examSubjects = $this->db->select("
            SELECT es.*, s.subject_name, s.subject_code, c.class_name, c.section
            FROM exam_subjects es
            LEFT JOIN subjects s ON es.subject_id = s.id
            LEFT JOIN classes c ON es.class_id = c.id
            WHERE es.exam_id = ?
            ORDER BY es.exam_date, es.start_time
        ", [$examId]);

        // Get exam results summary
        $resultsSummary = $this->db->select("
            SELECT
                COUNT(DISTINCT er.student_id) as total_students,
                COUNT(er.id) as total_results,
                AVG((er.marks_obtained / er.max_marks) * 100) as avg_percentage,
                MIN((er.marks_obtained / er.max_marks) * 100) as min_percentage,
                MAX((er.marks_obtained / er.max_marks) * 100) as max_percentage,
                COUNT(CASE WHEN er.grade = 'A' THEN 1 END) as a_grades,
                COUNT(CASE WHEN er.grade = 'B' THEN 1 END) as b_grades,
                COUNT(CASE WHEN er.grade = 'C' THEN 1 END) as c_grades,
                COUNT(CASE WHEN er.grade = 'F' THEN 1 END) as f_grades
            FROM exam_results er
            WHERE er.exam_id = ?
        ", [$examId]);

        $csrfToken = $this->csrfToken();
        $this->render('admin/exams/view', [
            'exam' => $exam,
            'exam_subjects' => $examSubjects,
            'results_summary' => $resultsSummary[0] ?? null,
            'csrf_token' => $csrfToken
        ]);
    }

    public function editExam($id) {
        $exam = $this->db->selectOne("SELECT * FROM exams WHERE id = ?", [$id]);
        if (!$exam) {
            $this->session->setFlash('error', 'Exam not found');
            $this->redirect('/admin/exams');
        }

        $academicYearId = $this->getCurrentAcademicYearId();
        $where = "WHERE is_active = 1";
        $params = [];
        if ($academicYearId) {
            $where .= " AND academic_year_id = ?";
            $params = [$academicYearId];
        }
        $classes = $this->db->select("SELECT * FROM classes $where ORDER BY class_name", $params);
        $subjects = $this->db->select("SELECT * FROM subjects WHERE is_active = 1 ORDER BY subject_name");
        $academicYears = $this->db->select("SELECT * FROM academic_years WHERE is_active = 1 ORDER BY year_name DESC");

        // Get exam subjects
        $examSubjects = $this->db->select("
            SELECT es.*, s.subject_name, s.subject_code, c.class_name, c.section
            FROM exam_subjects es
            LEFT JOIN subjects s ON es.subject_id = s.id
            LEFT JOIN classes c ON es.class_id = c.id
            WHERE es.exam_id = ?
            ORDER BY es.exam_date, es.start_time
        ", [$id]);

        $csrfToken = $this->csrfToken();
        $this->render('admin/exams/edit', [
            'exam' => $exam,
            'classes' => $classes,
            'subjects' => $subjects,
            'academic_years' => $academicYears,
            'exam_subjects' => $examSubjects,
            'csrf_token' => $csrfToken
        ]);
    }

    public function updateExam($id) {
        $exam = $this->db->selectOne("SELECT * FROM exams WHERE id = ?", [$id]);
        if (!$exam) {
            $this->session->setFlash('error', 'Exam not found');
            $this->redirect('/admin/exams');
        }

        $data = $_POST;
        $csrfToken = $data['csrf_token'] ?? '';

        if (!$this->checkCsrfToken($csrfToken)) {
            $this->session->setFlash('error', 'Invalid CSRF token');
            $this->redirect('/admin/exams/edit/' . $id);
        }

        // Validate required fields
        $required = ['exam_name', 'exam_type', 'start_date', 'end_date'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                $this->session->setFlash('error', ucfirst(str_replace('_', ' ', $field)) . ' is required');
                $this->session->setFlash('old', $data);
                $this->redirect('/admin/exams/edit/' . $id);
            }
        }

        // Validate class selection
        $classIds = $data['class_ids'] ?? [];
        if (!is_array($classIds) || empty($classIds)) {
            $this->session->setFlash('error', 'At least one class must be selected');
            $this->session->setFlash('old', $data);
            $this->redirect('/admin/exams/edit/' . $id);
        }

        // Validate subjects
        if (empty($data['subjects']) || !is_array($data['subjects'])) {
            $this->session->setFlash('error', 'At least one subject must be added to the exam');
            $this->session->setFlash('old', $data);
            $this->redirect('/admin/exams/edit/' . $id);
        }

        // Start transaction
        $this->db->beginTransaction();

        try {
            // Update exam
            $examData = [
                'exam_name' => trim($data['exam_name']),
                'exam_type' => $data['exam_type'],
                'class_id' => $classIds[0], // Keep for backward compatibility
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'is_active' => isset($data['is_active']) ? 1 : 0
            ];

            $updated = $this->db->update('exams', $examData, 'id = ?', [$id]);

            if (!$updated) {
                throw new Exception('Failed to update exam');
            }

            // Delete existing exam subjects
            $this->db->delete('exam_subjects', 'exam_id = ?', [$id]);

            // Insert updated exam subjects
            foreach ($data['subjects'] as $subjectData) {
                if (!empty($subjectData['subject_id'])) {
                    $this->db->insert('exam_subjects', [
                        'exam_id' => $id,
                        'subject_id' => $subjectData['subject_id'],
                        'class_id' => $subjectData['class_id'],
                        'exam_date' => $subjectData['exam_date'],
                        'start_time' => $subjectData['start_time'],
                        'end_time' => $subjectData['end_time'],
                        'max_marks' => $subjectData['max_marks'] ?: 100
                    ]);
                }
            }

            $this->db->commit();

            $this->session->setFlash('success', 'Exam updated successfully');
            $this->redirect('/admin/exams');

        } catch (Exception $e) {
            $this->db->rollback();
            $this->session->setFlash('error', 'Failed to update exam: ' . $e->getMessage());
            $this->session->setFlash('old', $data);
            $this->redirect('/admin/exams/edit/' . $id);
        }
    }

    public function deleteExam($id) {
        $exam = $this->db->selectOne("SELECT * FROM exams WHERE id = ?", [$id]);
        if (!$exam) {
            $this->session->setFlash('error', 'Exam not found');
            $this->redirect('/admin/exams');
        }

        // Check if exam has results
        $resultCount = $this->db->selectOne("SELECT COUNT(*) as count FROM exam_results WHERE exam_id = ?", [$id])['count'];
        if ($resultCount > 0) {
            $this->session->setFlash('error', 'Cannot delete exam with existing results');
            $this->redirect('/admin/exams');
        }

        $deleted = $this->db->delete('exams', 'id = ?', [$id]);

        if ($deleted) {
            // Also delete exam subjects
            $this->db->delete('exam_subjects', 'exam_id = ?', [$id]);
            $this->session->setFlash('success', 'Exam deleted successfully');
        } else {
            $this->session->setFlash('error', 'Failed to delete exam');
        }

        $this->redirect('/admin/exams');
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
        $academicYears = $this->db->select("SELECT * FROM academic_years WHERE is_active = 1 ORDER BY year_name DESC");

        // Define exam types
        $examTypes = [
            'quarterly' => 'Quarterly',
            'halfyearly' => 'Half Yearly',
            'annually' => 'Annually',
            'custom' => 'Custom'
        ];

        $csrfToken = $this->csrfToken();
        $this->render('admin/exams/create', [
            'classes' => $classes,
            'subjects' => $subjects,
            'academic_years' => $academicYears,
            'exam_types' => $examTypes,
            'csrf_token' => $csrfToken
        ]);
    }

    public function getClassSubjects() {
        $classIdsString = $_GET['class_ids'] ?? '';
        if (empty($classIdsString)) {
            $this->json(['subjects' => []]);
        }

        $classIds = explode(',', $classIdsString);
        $classIds = array_map('intval', $classIds); // Sanitize

        $academicYearId = $this->getCurrentAcademicYearId();

        // Get subjects for the selected classes with class info
        $placeholders = str_repeat('?,', count($classIds) - 1) . '?';
        $params = $classIds;
        $where = "cs.class_id IN ($placeholders) AND s.is_active = 1";
        if ($academicYearId) {
            $where .= " AND c.academic_year_id = ?";
            $params[] = $academicYearId;
        }

        $subjects = $this->db->select("
            SELECT s.id, s.subject_name, s.subject_code, c.id as class_id, c.class_name, c.section
            FROM subjects s
            INNER JOIN class_subjects cs ON s.id = cs.subject_id
            INNER JOIN classes c ON cs.class_id = c.id
            WHERE $where
            ORDER BY c.class_name, s.subject_name
        ", $params);

        $this->json(['subjects' => $subjects]);
    }

    public function getExamClasses() {
        $examId = $_GET['exam_id'] ?? '';
        if (empty($examId)) {
            $this->json(['classes' => []]);
        }

        $classes = $this->db->select("
            SELECT DISTINCT c.id, c.class_name, c.section
            FROM exam_subjects es
            LEFT JOIN classes c ON es.class_id = c.id
            WHERE es.exam_id = ?
            ORDER BY c.class_name
        ", [$examId]);

        $this->json(['classes' => $classes]);
    }

    public function storeExam() {
        $data = $_POST;
        $csrfToken = $data['csrf_token'] ?? '';

        if (!$this->checkCsrfToken($csrfToken)) {
            $this->session->setFlash('error', 'Invalid CSRF token');
            $this->redirect('/admin/exams/create');
        }

        // Validate required fields
        $required = ['exam_name', 'exam_type', 'class_ids', 'start_date', 'end_date'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                $this->session->setFlash('error', ucfirst(str_replace('_', ' ', $field)) . ' is required');
                $this->session->setFlash('old', $data);
                $this->redirect('/admin/exams/create');
            }
        }

        $classIds = $data['class_ids'];
        if (!is_array($classIds) || empty($classIds)) {
            $this->session->setFlash('error', 'At least one class must be selected');
            $this->session->setFlash('old', $data);
            $this->redirect('/admin/exams/create');
        }

        // Start transaction
        $this->db->beginTransaction();

        try {
            // Use selected academic year or current one as fallback
            $academicYearId = $data['academic_year_id'] ?? $this->getCurrentAcademicYearId();

            // Create exam (use first class as reference for backward compatibility)
            $examData = [
                'exam_name' => trim($data['exam_name']),
                'exam_type' => $data['exam_type'],
                'class_id' => $classIds[0], // Keep for backward compatibility
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'is_active' => isset($data['is_active']) ? 1 : 0
            ];
            if ($academicYearId) {
                $examData['academic_year_id'] = $academicYearId;
            }

            $examId = $this->db->insert('exams', $examData);

            // Insert exam subjects from the form data
            $totalSubjects = 0;
            $scheduledClasses = [];
            foreach ($data['subjects'] as $subjectData) {
                // Only insert if subject has valid date and time
                if (!empty($subjectData['subject_id']) && !empty($subjectData['class_id']) &&
                    !empty($subjectData['exam_date']) && !empty($subjectData['start_time']) && !empty($subjectData['end_time'])) {

                    // Check if this subject already has an exam on the same date in another class
                    $existingExam = $this->db->selectOne("
                        SELECT es.id FROM exam_subjects es
                        WHERE es.exam_id != ? AND es.subject_id = ? AND es.class_id != ? AND es.exam_date = ?
                    ", [$examId, $subjectData['subject_id'], $subjectData['class_id'], $subjectData['exam_date']]);

                    if ($existingExam) {
                        $subject = $this->db->selectOne("SELECT subject_name FROM subjects WHERE id = ?", [$subjectData['subject_id']]);
                        throw new Exception("Subject '{$subject['subject_name']}' already has an exam scheduled on {$subjectData['exam_date']} for another class");
                    }

                    $this->db->insert('exam_subjects', [
                        'exam_id' => $examId,
                        'subject_id' => $subjectData['subject_id'],
                        'class_id' => $subjectData['class_id'],
                        'exam_date' => $subjectData['exam_date'],
                        'start_time' => $subjectData['start_time'],
                        'end_time' => $subjectData['end_time'],
                        'max_marks' => $subjectData['max_marks'] ?: 100
                    ]);
                    $totalSubjects++;
                    $scheduledClasses[$subjectData['class_id']] = true;
                }
            }

            // Validate that each selected class has at least one scheduled subject
            foreach ($classIds as $classId) {
                if (!isset($scheduledClasses[$classId])) {
                    $class = $this->db->selectOne("SELECT class_name, section FROM classes WHERE id = ?", [$classId]);
                    throw new Exception("No subjects scheduled for class '{$class['class_name']} {$class['section']}'. Please schedule at least one subject for each selected class.");
                }
            }

            $this->db->commit();

            $this->session->setFlash('success', 'Exam created successfully for ' . count($classIds) . ' classes with ' . $totalSubjects . ' subject entries');
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

    public function createQuickExam() {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!$data || !isset($data['exam_name']) || !isset($data['class_id'])) {
            $this->json(['success' => false, 'message' => 'Invalid data'], 400);
        }

        $examName = trim($data['exam_name']);
        $classId = $data['class_id'];

        if (empty($examName)) {
            $this->json(['success' => false, 'message' => 'Exam name is required'], 400);
        }

        // Validate class exists
        $class = $this->db->selectOne("SELECT * FROM classes WHERE id = ? AND is_active = 1", [$classId]);
        if (!$class) {
            $this->json(['success' => false, 'message' => 'Class not found'], 404);
        }

        // Get subjects for this class
        $subjects = $this->db->select("
            SELECT cs.*, s.subject_name
            FROM class_subjects cs
            LEFT JOIN subjects s ON cs.subject_id = s.id
            WHERE cs.class_id = ?
            ORDER BY s.subject_name
        ", [$classId]);

        if (empty($subjects)) {
            $this->json(['success' => false, 'message' => 'No subjects found for this class'], 400);
        }

        // Start transaction
        $this->db->beginTransaction();

        try {
            $academicYearId = $this->getCurrentAcademicYearId();

            // Create exam
            $examData = [
                'exam_name' => $examName,
                'exam_type' => 'custom',
                'class_id' => $classId,
                'start_date' => date('Y-m-d'),
                'end_date' => date('Y-m-d'),
                'is_active' => 1
            ];
            if ($academicYearId) {
                $examData['academic_year_id'] = $academicYearId;
            }

            $examId = $this->db->insert('exams', $examData);

            // Create exam subjects with default max marks of 100
            foreach ($subjects as $subject) {
                $this->db->insert('exam_subjects', [
                    'exam_id' => $examId,
                    'subject_id' => $subject['subject_id'],
                    'exam_date' => date('Y-m-d'),
                    'start_time' => '10:00:00',
                    'end_time' => '11:00:00',
                    'max_marks' => 100
                ]);
            }

            $this->db->commit();

            $this->json([
                'success' => true,
                'message' => 'Exam created successfully',
                'exam_id' => $examId
            ]);

        } catch (Exception $e) {
            $this->db->rollback();
            $this->json(['success' => false, 'message' => 'Failed to create exam: ' . $e->getMessage()], 500);
        }
    }

    public function importMarksExcel() {
        $examId = $_POST['exam_id'] ?? '';
        $overwriteExisting = isset($_POST['overwrite_existing']);

        if (!$examId) {
            $this->json(['success' => false, 'message' => 'Exam ID is required'], 400);
        }

        // Validate exam exists
        $exam = $this->db->selectOne("SELECT * FROM exams WHERE id = ?", [$examId]);
        if (!$exam) {
            $this->json(['success' => false, 'message' => 'Exam not found'], 404);
        }

        if (!isset($_FILES['excel_file']) || $_FILES['excel_file']['error'] !== UPLOAD_ERR_OK) {
            $this->json(['success' => false, 'message' => 'Please select a valid Excel file'], 400);
        }

        $file = $_FILES['excel_file']['tmp_name'];
        $fileName = $_FILES['excel_file']['name'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        // Validate file extension
        if (!in_array($fileExt, ['xlsx', 'xls', 'csv'])) {
            $this->json(['success' => false, 'message' => 'Invalid file format. Only .xlsx, .xls, and .csv files are allowed'], 400);
        }

        try {
            // Get exam subjects for mapping
            $examSubjects = $this->db->select("
                SELECT es.*, s.subject_name, s.subject_code
                FROM exam_subjects es
                LEFT JOIN subjects s ON es.subject_id = s.id
                WHERE es.exam_id = ?
                ORDER BY es.exam_date, es.start_time
            ", [$examId]);

            if (empty($examSubjects)) {
                $this->json(['success' => false, 'message' => 'No subjects found for this exam'], 400);
            }

            // Get students for this exam
            $students = $this->db->select("
                SELECT s.id, s.scholar_number, s.first_name, s.last_name
                FROM students s
                WHERE s.class_id = ? AND s.is_active = 1
                ORDER BY s.first_name, s.last_name
            ", [$exam['class_id']]);

            // Parse Excel/CSV file
            $data = $this->parseExcelFile($file, $fileExt);

            if (empty($data)) {
                $this->json(['success' => false, 'message' => 'No data found in the file'], 400);
            }

            // Start transaction
            $this->db->beginTransaction();

            $importedCount = 0;
            $errors = [];

            foreach ($data as $rowIndex => $row) {
                // Skip header row if it exists
                if ($rowIndex === 0 && (strtolower($row[0] ?? '') === 'scholar_number' || strtolower($row[0] ?? '') === 'scholar number')) {
                    continue;
                }

                $scholarNumber = trim($row[0] ?? '');
                $subjectName = trim($row[1] ?? '');
                $marks = trim($row[2] ?? '');

                if (empty($scholarNumber) || empty($subjectName) || $marks === '') {
                    $errors[] = "Row " . ($rowIndex + 1) . ": Missing required data (scholar number, subject, marks)";
                    continue;
                }

                // Find student by scholar number
                $student = null;
                foreach ($students as $s) {
                    if ($s['scholar_number'] === $scholarNumber) {
                        $student = $s;
                        break;
                    }
                }

                if (!$student) {
                    $errors[] = "Row " . ($rowIndex + 1) . ": Student with scholar number '$scholarNumber' not found";
                    continue;
                }

                // Find subject
                $subject = null;
                foreach ($examSubjects as $es) {
                    if (strtolower($es['subject_name']) === strtolower($subjectName) ||
                        strtolower($es['subject_code']) === strtolower($subjectName)) {
                        $subject = $es;
                        break;
                    }
                }

                if (!$subject) {
                    $errors[] = "Row " . ($rowIndex + 1) . ": Subject '$subjectName' not found in this exam";
                    continue;
                }

                // Validate marks
                $marksValue = floatval($marks);
                if ($marksValue < 0 || $marksValue > $subject['max_marks']) {
                    $errors[] = "Row " . ($rowIndex + 1) . ": Invalid marks '$marks' (must be between 0 and {$subject['max_marks']})";
                    continue;
                }

                // Check if result already exists
                $existingResult = $this->db->selectOne("
                    SELECT id FROM exam_results
                    WHERE exam_id = ? AND student_id = ? AND subject_id = ?
                ", [$examId, $student['id'], $subject['subject_id']]);

                if ($existingResult && !$overwriteExisting) {
                    $errors[] = "Row " . ($rowIndex + 1) . ": Marks already exist for {$student['first_name']} {$student['last_name']} in $subjectName";
                    continue;
                }

                // Calculate grade
                $grade = $this->calculateGrade($marksValue, $subject['max_marks']);
                $percentage = round(($marksValue / $subject['max_marks']) * 100, 2);

                $resultData = [
                    'exam_id' => $examId,
                    'student_id' => $student['id'],
                    'subject_id' => $subject['subject_id'],
                    'marks_obtained' => $marksValue,
                    'max_marks' => $subject['max_marks'],
                    'grade' => $grade,
                    'percentage' => $percentage
                ];

                $academicYearId = $this->getCurrentAcademicYearId();
                if ($academicYearId) {
                    $resultData['academic_year_id'] = $academicYearId;
                }

                if ($existingResult) {
                    $this->db->update('exam_results', $resultData, 'id = ?', [$existingResult['id']]);
                } else {
                    $this->db->insert('exam_results', $resultData);
                }

                $importedCount++;
            }

            if (empty($errors)) {
                $this->db->commit();
                $this->json([
                    'success' => true,
                    'message' => "Successfully imported $importedCount marks"
                ]);
            } else {
                $this->db->rollback();
                $this->json([
                    'success' => false,
                    'message' => "Import failed. $importedCount marks imported, " . count($errors) . " errors found.",
                    'errors' => $errors
                ], 400);
            }

        } catch (Exception $e) {
            $this->db->rollback();
            $this->json(['success' => false, 'message' => 'Import failed: ' . $e->getMessage()], 500);
        }
    }

    private function parseExcelFile($file, $extension) {
        $data = [];

        if ($extension === 'csv') {
            // Parse CSV
            if (($handle = fopen($file, 'r')) !== false) {
                while (($row = fgetcsv($handle, 1000, ',')) !== false) {
                    $data[] = $row;
                }
                fclose($handle);
            }
        } else {
            // For Excel files, we'll use a simple approach
            // In production, you would use a library like PhpSpreadsheet
            // For now, we'll try to read as CSV if possible
            if (($handle = fopen($file, 'r')) !== false) {
                while (($row = fgetcsv($handle, 1000, ',')) !== false) {
                    $data[] = $row;
                }
                fclose($handle);
            }
        }

        return $data;
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

    private function calculateGrade($marks, $maxMarks) {
        if ($maxMarks == 0) return 'N/A';

        $percentage = ($marks / $maxMarks) * 100;

        if ($percentage >= 90) return 'A';
        if ($percentage >= 80) return 'B';
        if ($percentage >= 70) return 'C';
        if ($percentage >= 60) return 'D';
        return 'F';
    }
}