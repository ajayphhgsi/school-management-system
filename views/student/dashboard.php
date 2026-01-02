<?php
$active_page = 'dashboard';
$page_title = 'Student Dashboard';
ob_start();
?>

<style>
.welcome-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 15px;
}

.stats-card {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: white;
    border-radius: 12px;
    transition: transform 0.3s ease;
}

.stats-card:hover {
    transform: translateY(-5px);
}

.quick-action-card {
    background: white;
    border: 1px solid #e9ecef;
    border-radius: 12px;
    transition: all 0.3s ease;
    cursor: pointer;
    text-decoration: none;
    color: inherit;
}

.quick-action-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    text-decoration: none;
    color: inherit;
}
</style>

<!-- Welcome Section -->
<div class="welcome-card p-4 mb-4">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h2>Welcome back, <?php echo (is_array($student) ? $student['first_name'] . ' ' . $student['last_name'] : 'Student'); ?>! 👋</h2>
            <p class="mb-0 opacity-75">Here's your academic overview for today.</p>
        </div>
        <div class="col-md-4 text-end">
            <div class="d-flex align-items-center justify-content-end">
                <div class="me-3">
                    <small class="d-block opacity-75">Scholar Number</small>
                    <strong><?php echo (is_array($student) ? $student['scholar_number'] : 'N/A'); ?></strong>
                </div>
                <i class="fas fa-graduation-cap fa-2x opacity-75"></i>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stats-card">
            <div class="card-body text-center">
                <div class="mb-3">
                    <i class="fas fa-calendar-check fa-3x opacity-75"></i>
                </div>
                <h4 class="mb-1"><?php echo $stats['attendance_percentage']; ?>%</h4>
                <p class="mb-1">Attendance Rate</p>
                <small class="opacity-75"><?php echo $stats['total_present']; ?> Present</small>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stats-card">
            <div class="card-body text-center">
                <div class="mb-3">
                    <i class="fas fa-chart-line fa-3x opacity-75"></i>
                </div>
                <h4 class="mb-1"><?php echo count($stats['recent_results']); ?></h4>
                <p class="mb-1">Exam Results</p>
                <small class="opacity-75">Recent Exams</small>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stats-card">
            <div class="card-body text-center">
                <div class="mb-3">
                    <i class="fas fa-money-bill-wave fa-3x opacity-75"></i>
                </div>
                <h4 class="mb-1">$<?php echo number_format($stats['pending_fees'], 2); ?></h4>
                <p class="mb-1">Pending Fees</p>
                <small class="opacity-75">Outstanding</small>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stats-card">
            <div class="card-body text-center">
                <div class="mb-3">
                    <i class="fas fa-calendar-alt fa-3x opacity-75"></i>
                </div>
                <h4 class="mb-1"><?php echo count($stats['upcoming_events']); ?></h4>
                <p class="mb-1">Upcoming Events</p>
                <small class="opacity-75">This Month</small>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity & Quick Actions -->
<div class="row mb-4">
    <div class="col-lg-8 mb-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-chart-line text-success me-2"></i>Recent Exam Results</h5>
                <a href="/student/results" class="btn btn-sm btn-outline-success">View All</a>
            </div>
            <div class="card-body">
                <?php if (!empty($stats['recent_results'])): ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($stats['recent_results'] as $result): ?>
                            <div class="list-group-item px-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1"><?php echo $result['exam_name']; ?> - <?php echo $result['subject_name']; ?></h6>
                                        <small class="text-muted">
                                            Marks: <?php echo $result['marks_obtained']; ?>/<?php echo $result['max_marks']; ?>
                                            <?php if ($result['grade']): ?> | Grade: <?php echo $result['grade']; ?><?php endif; ?>
                                        </small>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-<?php echo ($result['marks_obtained'] / $result['max_marks']) >= 0.6 ? 'success' : 'warning'; ?>">
                                            <?php echo round(($result['marks_obtained'] / $result['max_marks']) * 100, 1); ?>%
                                        </span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No Exam Results Yet</h5>
                        <p class="text-muted">Your exam results will appear here once they are published.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-4 mb-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-calendar-alt text-info me-2"></i>Upcoming Events</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($stats['upcoming_events'])): ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($stats['upcoming_events'] as $event): ?>
                            <div class="list-group-item px-0">
                                <div class="d-flex align-items-start">
                                    <div class="flex-shrink-0 me-3">
                                        <div class="bg-info rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                            <i class="fas fa-calendar text-white"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1"><?php echo $event['title']; ?></h6>
                                        <small class="text-muted">
                                            <i class="fas fa-clock me-1"></i><?php echo date('M d, Y', strtotime($event['event_date'])); ?> at <?php echo $event['event_time']; ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-calendar-alt fa-2x text-muted mb-2"></i>
                        <p class="text-muted mb-0">No upcoming events</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-bolt text-warning me-2"></i>Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                        <a href="/student/attendance" class="quick-action-card card h-100">
                            <div class="card-body text-center">
                                <div class="quick-action-icon text-primary mb-2">
                                    <i class="fas fa-calendar-check fa-2x"></i>
                                </div>
                                <h6 class="card-title mb-0">My Attendance</h6>
                                <small class="text-muted">View attendance history</small>
                            </div>
                        </a>
                    </div>
                    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                        <a href="/student/results" class="quick-action-card card h-100">
                            <div class="card-body text-center">
                                <div class="quick-action-icon text-success mb-2">
                                    <i class="fas fa-chart-line fa-2x"></i>
                                </div>
                                <h6 class="card-title mb-0">Exam Results</h6>
                                <small class="text-muted">View your grades</small>
                            </div>
                        </a>
                    </div>
                    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                        <a href="/student/fees" class="quick-action-card card h-100">
                            <div class="card-body text-center">
                                <div class="quick-action-icon text-info mb-2">
                                    <i class="fas fa-money-bill-wave fa-2x"></i>
                                </div>
                                <h6 class="card-title mb-0">Fee Details</h6>
                                <small class="text-muted">Payment history</small>
                            </div>
                        </a>
                    </div>
                    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                        <a href="/student/profile" class="quick-action-card card h-100">
                            <div class="card-body text-center">
                                <div class="quick-action-icon text-warning mb-2">
                                    <i class="fas fa-user fa-2x"></i>
                                </div>
                                <h6 class="card-title mb-0">My Profile</h6>
                                <small class="text-muted">Update information</small>
                            </div>
                        </a>
                    </div>
                    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                        <a href="/student/events" class="quick-action-card card h-100">
                            <div class="card-body text-center">
                                <div class="quick-action-icon text-secondary mb-2">
                                    <i class="fas fa-calendar fa-2x"></i>
                                </div>
                                <h6 class="card-title mb-0">School Events</h6>
                                <small class="text-muted">Upcoming activities</small>
                            </div>
                        </a>
                    </div>
                    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                        <a href="/student/change-password" class="quick-action-card card h-100">
                            <div class="card-body text-center">
                                <div class="quick-action-icon text-danger mb-2">
                                    <i class="fas fa-key fa-2x"></i>
                                </div>
                                <h6 class="card-title mb-0">Change Password</h6>
                                <small class="text-muted">Account security</small>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include 'layout.php';
?>
</xai:function_call"> 

<xai:function_call name="update_todo_list">
<parameter name="todos">["Analyze school.md requirements and identify missing/imcomplete features", "[x] Implement complete attendance system with Present/Absent/Late marking, bulk CSV/Excel uploads, and reporting", "[x] Enhance exams module with exam setup, subject scheduling, admit card generation, marksheet generation, transfer certificates", "[x] Implement complete fee management with fee structure, payment processing, receipt generation (3 copies), outstanding tracking", "Add expense management module with receipt tracking, category management, and reporting", "[x] Create student portal pages (dashboard, profile, attendance, results, fees)", "Implement admit card generation system with bulk/individual printing", "Implement transfer certificate generation with bulk/individual options", "Implement marksheet generation with PDF export and printing", "Enhance gallery management with bulk uploads and categorization", "Implement comprehensive reporting system with PDF/Excel export", "Complete settings page with user management, permissions, and API security", "Add homepage management for admin to edit carousel, about, courses, events, gallery content", "Implement role-based permission system with proper access control", "Add AJAX functionality throughout the application for dynamic content loading"]