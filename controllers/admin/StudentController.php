<?php
/**
 * Admin Student Controller
 */

class StudentController extends Controller {

    public function __construct() {
        parent::__construct();
        $this->middleware('auth');
        $this->middleware('admin');
    }

    private function getCurrentAcademicYearId() {
        return $_SESSION['academic_year_id'] ?? null;
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
}