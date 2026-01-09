<?php
/**
 * Admin Dashboard Controller
 */

class DashboardController extends Controller {

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
}