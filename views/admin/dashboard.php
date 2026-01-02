<?php
$active_page = 'dashboard';
$page_title = 'Dashboard';
ob_start();
?>

<style>
.stats-card {
    background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
    border: none;
    border-radius: var(--border-radius);
    color: white;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    position: relative;
    overflow: hidden;
}

.stats-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--card-shadow);
}

.stats-card::before {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    width: 100px;
    height: 100px;
    background: rgba(255,255,255,0.1);
    border-radius: 50%;
    transform: translate(30px, -30px);
}

.stats-card .card-body {
    position: relative;
    z-index: 2;
}

.stats-icon {
    opacity: 0.8;
    font-size: 2.5rem;
}

.kpi-card {
    background: var(--card-bg);
    border: 1px solid #e9ecef;
    border-radius: var(--border-radius);
    transition: all 0.3s ease;
}

.kpi-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--card-shadow);
}

.kpi-value {
    font-size: 2rem;
    font-weight: bold;
    color: var(--primary);
}

.kpi-change {
    font-size: 0.875rem;
}

.kpi-change.positive {
    color: #28a745;
}

.kpi-change.negative {
    color: #dc3545;
}

.alert-widget {
    background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%);
    border-radius: var(--border-radius);
    color: white;
    border: none;
}


.recent-activity {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    border-radius: var(--border-radius);
    color: white;
}

.welcome-section {
    background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
    border-radius: var(--border-radius);
    color: white;
    padding: 2rem;
    margin-bottom: 2rem;
}

.financial-overview {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: var(--border-radius);
    color: white;
}

.performance-card {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    border-radius: var(--border-radius);
    color: white;
}

.system-health {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    border-radius: var(--border-radius);
    color: white;
}

.activity-item {
    padding: 0.75rem;
    border-bottom: 1px solid rgba(255,255,255,0.1);
}

.activity-item:last-child {
    border-bottom: none;
}

.chart-container {
    position: relative;
    height: 300px;
}

/* Classic theme overrides */
[data-theme="classic"] .stats-card {
    background: var(--primary);
}

[data-theme="classic"] .welcome-section {
    background: var(--primary);
}

[data-theme="classic"] .recent-activity {
    background: var(--secondary);
}

[data-theme="classic"] .financial-overview {
    background: var(--primary);
}

[data-theme="classic"] .performance-card {
    background: var(--secondary);
}

[data-theme="classic"] .system-health {
    background: var(--primary);
}

@media (max-width: 768px) {
    .stats-card .card-body {
        padding: 1rem;
    }

    .stats-icon {
        font-size: 2rem;
    }

    .kpi-value {
        font-size: 1.5rem;
    }
}
</style>

<!-- Welcome Section -->
<div class="welcome-section">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h2>Welcome back, <?php echo $_SESSION['user']['first_name'] ?? 'Admin'; ?>! 👋</h2>
            <p class="mb-0">Here's what's happening with your school management system today.</p>
        </div>
        <div class="col-md-4 text-end">
            <div class="d-flex align-items-center justify-content-end">
                <div class="me-3">
                    <small class="d-block opacity-75">Academic Year</small>
                    <strong><?php echo $current_academic_year ?? 'Not Selected'; ?></strong>
                </div>
                <div class="me-3">
                    <small class="d-block opacity-75">Today's Date</small>
                    <strong><?php echo date('F j, Y'); ?></strong>
                </div>
                <i class="fas fa-calendar-alt fa-2x opacity-75"></i>
            </div>
        </div>
    </div>
</div>

<!-- Key Performance Indicators -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card kpi-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title text-muted mb-1">Monthly Revenue</h6>
                        <div class="kpi-value">₹<?php echo number_format((float)($financialStats['monthly_revenue'] ?? 0)); ?></div>
                        <div class="kpi-change positive">
                            <i class="fas fa-arrow-up me-1"></i>+12% from last month
                        </div>
                    </div>
                    <div class="text-success">
                        <i class="fas fa-rupee-sign fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card kpi-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title text-muted mb-1">Outstanding Fees</h6>
                        <div class="kpi-value">₹<?php echo number_format((float)($financialStats['outstanding_fees'] ?? 0)); ?></div>
                        <div class="kpi-change negative">
                            <i class="fas fa-arrow-down me-1"></i><?php echo $alerts['overdue_fees_count'] ?? 0; ?> overdue
                        </div>
                    </div>
                    <div class="text-warning">
                        <i class="fas fa-exclamation-triangle fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card kpi-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title text-muted mb-1">Attendance Rate</h6>
                        <div class="kpi-value"><?php echo number_format((float)($academicStats['avg_attendance'] ?? 0), 1); ?>%</div>
                        <div class="kpi-change positive">
                            <i class="fas fa-arrow-up me-1"></i>+2.3% from last week
                        </div>
                    </div>
                    <div class="text-info">
                        <i class="fas fa-chart-line fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card kpi-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title text-muted mb-1">Pass Rate</h6>
                        <div class="kpi-value"><?php echo number_format((float)($academicStats['pass_rate'] ?? 0), 1); ?>%</div>
                        <div class="kpi-change positive">
                            <i class="fas fa-arrow-up me-1"></i>+5.1% from last exam
                        </div>
                    </div>
                    <div class="text-success">
                        <i class="fas fa-graduation-cap fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stats-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-1">Total Students</h6>
                        <h2 class="mb-0"><?php echo number_format((float)($stats['total_students'] ?? 0)); ?></h2>
                        <small class="opacity-75">Enrolled</small>
                    </div>
                    <div class="stats-icon">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stats-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-1">Active Classes</h6>
                        <h2 class="mb-0"><?php echo number_format((float)($stats['total_classes'] ?? 0)); ?></h2>
                        <small class="opacity-75">Running</small>
                    </div>
                    <div class="stats-icon">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stats-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-1">School Events</h6>
                        <h2 class="mb-0"><?php echo number_format((float)($stats['total_events'] ?? 0)); ?></h2>
                        <small class="opacity-75">Scheduled</small>
                    </div>
                    <div class="stats-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stats-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-1">Gallery Items</h6>
                        <h2 class="mb-0"><?php echo number_format((float)($stats['total_gallery'] ?? 0)); ?></h2>
                        <small class="opacity-75">Photos</small>
                    </div>
                    <div class="stats-icon">
                        <i class="fas fa-images"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Alerts & Notifications -->
<?php if (!empty($alerts['overdue_fees_count']) || !empty($alerts['low_attendance_classes']) || !empty($alerts['upcoming_deadlines'])): ?>
<div class="row mb-4">
    <div class="col-12">
        <div class="card alert-widget">
            <div class="card-body">
                <h5 class="card-title mb-3">
                    <i class="fas fa-bell me-2"></i>Important Alerts
                </h5>
                <div class="row">
                    <?php if ($alerts['overdue_fees_count'] > 0): ?>
                    <div class="col-md-4 mb-3">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="fas fa-exclamation-triangle fa-2x text-warning"></i>
                            </div>
                            <div>
                                <h6 class="mb-1"><?php echo $alerts['overdue_fees_count']; ?> Overdue Fees</h6>
                                <small>Payments past due date</small>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($alerts['low_attendance_classes'])): ?>
                    <div class="col-md-4 mb-3">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="fas fa-chart-line fa-2x text-danger"></i>
                            </div>
                            <div>
                                <h6 class="mb-1">Low Attendance Alert</h6>
                                <small><?php echo count($alerts['low_attendance_classes']); ?> classes below 75%</small>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($alerts['upcoming_deadlines'])): ?>
                    <div class="col-md-4 mb-3">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="fas fa-calendar-times fa-2x text-info"></i>
                            </div>
                            <div>
                                <h6 class="mb-1">Upcoming Deadlines</h6>
                                <small>Fee payments due soon</small>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Financial Overview & System Health -->
<div class="row mb-4">
    <div class="col-lg-8 mb-4">
        <div class="card financial-overview">
            <div class="card-body">
                <h5 class="card-title mb-3">
                    <i class="fas fa-chart-pie me-2"></i>Financial Overview
                </h5>
                <div class="row text-center">
                    <div class="col-md-3 mb-3">
                        <div class="border-end border-white border-opacity-25">
                            <h4 class="mb-1">₹<?php echo number_format((float)($financialStats['total_revenue'] ?? 0)); ?></h4>
                            <small>Total Revenue</small>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="border-end border-white border-opacity-25">
                            <h4 class="mb-1">₹<?php echo number_format((float)($financialStats['outstanding_fees'] ?? 0)); ?></h4>
                            <small>Outstanding</small>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="border-end border-white border-opacity-25">
                            <h4 class="mb-1">₹<?php echo number_format((float)($financialStats['overdue_fees'] ?? 0)); ?></h4>
                            <small>Overdue</small>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <h4 class="mb-1"><?php echo number_format((float)((($financialStats['total_revenue'] ?? 0) - ($financialStats['outstanding_fees'] ?? 0)) / max(($financialStats['total_revenue'] ?? 0), 1) * 100), 1); ?>%</h4>
                        <small>Collection Rate</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4 mb-4">
        <div class="card system-health">
            <div class="card-body">
                <h5 class="card-title mb-3">
                    <i class="fas fa-server me-2"></i>System Health
                </h5>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span>Database Status</span>
                    <span class="badge bg-success">Healthy</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span>Active Users</span>
                    <span><?php echo $systemHealth['active_users']; ?></span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span>Last Backup</span>
                    <span><?php echo $systemHealth['last_backup']; ?></span>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span>Storage Used</span>
                    <span><?php echo $systemHealth['storage_used']; ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Analytics Charts -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-chart-line text-primary me-2"></i>Analytics & Trends</h5>
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-primary active" onclick="switchChart('revenue')">Revenue</button>
                    <button class="btn btn-outline-primary" onclick="switchChart('attendance')">Attendance</button>
                    <button class="btn btn-outline-primary" onclick="switchChart('performance')">Performance</button>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-8 mb-4">
                        <div class="chart-container">
                            <canvas id="mainChart"></canvas>
                        </div>
                    </div>
                    <div class="col-lg-4 mb-4">
                        <div class="row">
                            <div class="col-12 mb-3">
                                <div class="card performance-card">
                                    <div class="card-body text-center">
                                        <h6>Academic Performance</h6>
                                        <h3><?php echo number_format((float)($academicStats['pass_rate'] ?? 0), 1); ?>%</h3>
                                        <small>Pass Rate</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 mb-3">
                                <div class="card">
                                    <div class="card-body">
                                        <h6>Class Rankings</h6>
                                        <?php foreach (array_slice($classPerformance, 0, 3) as $class): ?>
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span><?php echo $class['class_name'] . ' ' . $class['section']; ?></span>
                                            <span class="badge bg-<?php echo ($class['pass_rate'] ?? 0) > 80 ? 'success' : (($class['pass_rate'] ?? 0) > 60 ? 'warning' : 'danger'); ?>">
                                                <?php echo number_format((float)($class['pass_rate'] ?? 0), 1); ?>%
                                            </span>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity & Quick Actions -->
<div class="row mb-4">
    <div class="col-lg-6 mb-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-users text-primary me-2"></i>Recent Students</h5>
                <a href="/admin/students" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body">
                <?php if (!empty($recentData['recent_students'])): ?>
                    <div class="row">
                        <?php foreach (array_slice($recentData['recent_students'], 0, 4) as $student): ?>
                            <div class="col-12 mb-3">
                                <div class="d-flex align-items-center p-3 bg-light rounded">
                                    <div class="flex-shrink-0 me-3">
                                        <?php if ($student['photo']): ?>
                                            <img src="/uploads/<?php echo $student['photo']; ?>" alt="Photo" class="rounded-circle" width="45" height="45">
                                        <?php else: ?>
                                            <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                                                <i class="fas fa-user text-white"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1"><?php echo $student['first_name'] . ' ' . $student['last_name']; ?></h6>
                                        <small class="text-muted">
                                            <i class="fas fa-id-card me-1"></i><?php echo $student['scholar_number']; ?> |
                                            <i class="fas fa-graduation-cap me-1"></i><?php echo $student['class_name'] ?? 'N/A'; ?>
                                        </small>
                                    </div>
                                    <div class="flex-shrink-0">
                                        <small class="text-muted"><?php echo date('M d', strtotime($student['created_at'])); ?></small>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-users fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No Students Yet</h5>
                        <p class="text-muted">Start by adding your first student to the system.</p>
                        <a href="/admin/students/create" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Add First Student
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-6 mb-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-rupee-sign text-success me-2"></i>Recent Payments</h5>
                <a href="/admin/fees" class="btn btn-sm btn-outline-success">View All</a>
            </div>
            <div class="card-body">
                <?php if (!empty($recentData['recent_payments'])): ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($recentData['recent_payments'] as $payment): ?>
                            <div class="list-group-item px-0 border-0">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0 me-3">
                                        <div class="bg-success rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                            <i class="fas fa-check text-white"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1"><?php echo $payment['first_name'] . ' ' . $payment['last_name']; ?></h6>
                                        <small class="text-muted">
                                            <i class="fas fa-id-card me-1"></i><?php echo $payment['scholar_number']; ?> |
                                            ₹<?php echo number_format((float)($payment['amount_paid'] ?? 0)); ?> |
                                            <?php echo $payment['fee_type']; ?>
                                        </small>
                                    </div>
                                    <div class="flex-shrink-0">
                                        <small class="text-muted"><?php echo date('M d', strtotime($payment['payment_date'])); ?></small>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-rupee-sign fa-2x text-muted mb-2"></i>
                        <p class="text-muted mb-0">No recent payments</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Activity Feed & Upcoming Events -->
<div class="row mb-4">
    <div class="col-lg-6 mb-4">
        <div class="card recent-activity">
            <div class="card-body">
                <h5 class="card-title mb-3">
                    <i class="fas fa-history me-2"></i>Recent Activities
                </h5>
                <?php if (!empty($recentData['recent_activities'])): ?>
                    <?php foreach (array_slice($recentData['recent_activities'], 0, 5) as $activity): ?>
                        <div class="activity-item">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0 me-3">
                                    <i class="fas fa-<?php
                                        switch ($activity['action']) {
                                            case 'student_created': echo 'user-plus'; break;
                                            case 'fee_payment': echo 'rupee-sign'; break;
                                            case 'exam_created': echo 'file-alt'; break;
                                            case 'attendance_marked': echo 'calendar-check'; break;
                                            default: echo 'circle';
                                        }
                                    ?> text-white"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <small class="text-white opacity-75">
                                        <?php echo ucfirst(str_replace('_', ' ', $activity['action'])); ?> by <?php echo $activity['first_name'] ?? 'System'; ?>
                                    </small>
                                </div>
                                <div class="flex-shrink-0">
                                    <small class="text-white opacity-75"><?php echo date('H:i', strtotime($activity['created_at'])); ?></small>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center py-3">
                        <small class="text-white opacity-75">No recent activities</small>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-6 mb-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-calendar-alt text-success me-2"></i>Upcoming Events & Deadlines</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($recentData['upcoming_events']) || !empty($alerts['upcoming_deadlines'])): ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($recentData['upcoming_events'] as $event): ?>
                            <div class="list-group-item px-0 border-0">
                                <div class="d-flex align-items-start">
                                    <div class="flex-shrink-0 me-3">
                                        <div class="bg-success rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
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

                        <?php foreach ($alerts['upcoming_deadlines'] as $deadline): ?>
                            <div class="list-group-item px-0 border-0">
                                <div class="d-flex align-items-start">
                                    <div class="flex-shrink-0 me-3">
                                        <div class="bg-warning rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                            <i class="fas fa-exclamation-triangle text-white"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1 text-warning"><?php echo $deadline['message']; ?></h6>
                                        <small class="text-muted">
                                            <i class="fas fa-calendar-times me-1"></i>Due: <?php echo date('M d, Y', strtotime($deadline['due_date'])); ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-calendar-alt fa-2x text-muted mb-2"></i>
                        <p class="text-muted mb-0">No upcoming events or deadlines</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>


<!-- Chart.js and Dashboard Scripts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let mainChart = null;
let currentChartType = 'revenue';

document.addEventListener('DOMContentLoaded', function() {
    initializeCharts();
    loadChartData('revenue');
});

function initializeCharts() {
    const ctx = document.getElementById('mainChart').getContext('2d');
    mainChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: [],
            datasets: []
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'Monthly Trends'
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

function switchChart(chartType) {
    currentChartType = chartType;

    // Update button states
    document.querySelectorAll('.btn-group .btn').forEach(btn => {
        btn.classList.remove('active');
    });
    event.target.classList.add('active');

    loadChartData(chartType);
}

function loadChartData(chartType) {
    // This would normally fetch data from the server
    // For now, we'll use the PHP data passed to the view

    const chartData = <?php echo json_encode($chartData); ?>;

    let labels = [];
    let datasets = [];
    let title = '';

    switch (chartType) {
        case 'revenue':
            if (chartData.fee_collection) {
                labels = chartData.fee_collection.map(item => item.month);
                datasets = [{
                    label: 'Revenue (₹)',
                    data: chartData.fee_collection.map(item => item.total),
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.1
                }];
                title = 'Monthly Fee Collection Trends';
            }
            break;

        case 'attendance':
            if (chartData.attendance_stats) {
                labels = chartData.attendance_stats.map(item => item.month);
                datasets = [{
                    label: 'Attendance Rate (%)',
                    data: chartData.attendance_stats.map(item => item.rate),
                    borderColor: 'rgb(54, 162, 235)',
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    tension: 0.1
                }];
                title = 'Monthly Attendance Trends';
            }
            break;

        case 'performance':
            if (chartData.academic_performance) {
                labels = chartData.academic_performance.map(item => item.month);
                datasets = [
                    {
                        label: 'Pass Rate (%)',
                        data: chartData.academic_performance.map(item => item.pass_rate),
                        borderColor: 'rgb(255, 205, 86)',
                        backgroundColor: 'rgba(255, 205, 86, 0.2)',
                        tension: 0.1
                    },
                    {
                        label: 'Average Grade',
                        data: chartData.academic_performance.map(item => item.avg_grade),
                        borderColor: 'rgb(153, 102, 255)',
                        backgroundColor: 'rgba(153, 102, 255, 0.2)',
                        tension: 0.1,
                        yAxisID: 'y1'
                    }
                ];
                title = 'Academic Performance Trends';
            }
            break;
    }

    mainChart.data.labels = labels;
    mainChart.data.datasets = datasets;
    mainChart.options.plugins.title.text = title;

    if (chartType === 'performance' && datasets.length > 1) {
        mainChart.options.scales.y1 = {
            type: 'linear',
            display: true,
            position: 'right',
            title: {
                display: true,
                text: 'Average Grade'
            }
        };
    } else {
        delete mainChart.options.scales.y1;
    }

    mainChart.update();
}


// Auto-refresh dashboard data every 5 minutes
setInterval(() => {
    // Could refresh specific data points without full page reload
    console.log('Dashboard auto-refresh would happen here');
}, 300000);
</script>

<?php
$content = ob_get_clean();
include 'layout.php';
?>