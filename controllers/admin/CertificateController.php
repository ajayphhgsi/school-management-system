<?php
/**
 * Admin Certificate Controller
 */

class CertificateController extends Controller {

    public function __construct() {
        parent::__construct();
        $this->middleware('auth');
        $this->middleware('admin');
    }

    private function getCurrentAcademicYearId() {
        return $_SESSION['academic_year_id'] ?? null;
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
}