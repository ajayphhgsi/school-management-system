<?php
/**
 * Admin Class Controller
 */

class ClassController extends Controller {

    public function __construct() {
        parent::__construct();
        $this->middleware('auth');
        $this->middleware('admin');
    }

    private function getCurrentAcademicYearId() {
        return $_SESSION['academic_year_id'] ?? null;
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

        $subjects = $this->db->select("
            SELECT s.*, CASE WHEN cs.class_id IS NOT NULL THEN 1 ELSE 0 END as assigned
            FROM subjects s
            LEFT JOIN class_subjects cs ON s.id = cs.subject_id AND cs.class_id = ?
            WHERE s.is_active = 1
            ORDER BY s.subject_name
        ", [$id]);

        $this->render('admin/classes/edit', ['class' => $class, 'subjects' => $subjects, 'csrf_token' => $csrfToken]);
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
            'assigned_subjects' => $_POST['assigned_subjects'] ?? [],
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

        // Start transaction for data integrity
        $this->db->beginTransaction();

        try {
            // Delete existing class_subjects for this class
            $this->db->delete('class_subjects', 'class_id = ?', [$id]);

            // Insert new class_subjects based on assigned_subjects array
            if (is_array($data['assigned_subjects'])) {
                foreach ($data['assigned_subjects'] as $subjectId) {
                    if (!empty($subjectId)) {
                        $this->db->insert('class_subjects', [
                            'class_id' => $id,
                            'subject_id' => $subjectId
                        ]);
                    }
                }
            }

            // Update class data
            $classData = $data;
            unset($classData['csrf_token'], $classData['assigned_subjects']);

            $updated = $this->db->update('classes', $classData, 'id = ?', [$id]);

            if ($updated === false) {
                throw new Exception('Failed to update class');
            }

            // Commit transaction
            $this->db->commit();

            $this->session->setFlash('success', 'Class updated successfully');
            $this->redirect('/admin/classes');

        } catch (Exception $e) {
            // Rollback transaction on error
            $this->db->rollback();
            $this->session->setFlash('error', 'Failed to update class: ' . $e->getMessage());
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
}