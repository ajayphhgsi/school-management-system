<?php
/**
 * Admin Report Controller
 */

class ReportController extends Controller {

    public function __construct() {
        parent::__construct();
        $this->middleware('auth');
        $this->middleware('admin');
    }

    private function getCurrentAcademicYearId() {
        return $_SESSION['academic_year_id'] ?? null;
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
                           AVG((er.marks_obtained / er.max_marks) * 100) as avg_percentage,
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
                           sub.subject_name, er.marks_obtained, er.max_marks, er.grade,
                           ROUND((er.marks_obtained / er.max_marks) * 100, 2) as percentage
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
                           AVG((er.marks_obtained / er.max_marks) * 100) as avg_percentage,
                           MIN((er.marks_obtained / er.max_marks) * 100) as min_percentage,
                           MAX((er.marks_obtained / er.max_marks) * 100) as max_percentage,
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
}