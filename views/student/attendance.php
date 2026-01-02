<?php
$active_page = 'attendance';
$page_title = 'My Attendance';
ob_start();
?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><i class="fas fa-calendar-check text-success me-2"></i>My Attendance</h4>
        <p class="text-muted mb-0">View your attendance history and records</p>
    </div>
</div>

<!-- Attendance Summary -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card bg-success text-white">
            <div class="card-body text-center">
                <div class="mb-2">
                    <i class="fas fa-check-circle fa-2x opacity-75"></i>
                </div>
                <h4><?php
                    $totalPresent = array_sum(array_map(function($status) { return $status === 'present' ? 1 : 0; }, array_column($attendance, 'status')));
                    echo $totalPresent;
                ?></h4>
                <p class="mb-0 opacity-75 small">Present Days</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card bg-danger text-white">
            <div class="card-body text-center">
                <div class="mb-2">
                    <i class="fas fa-times-circle fa-2x opacity-75"></i>
                </div>
                <h4><?php
                    $totalAbsent = array_sum(array_map(function($status) { return $status === 'absent' ? 1 : 0; }, array_column($attendance, 'status')));
                    echo $totalAbsent;
                ?></h4>
                <p class="mb-0 opacity-75 small">Absent Days</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card bg-warning text-white">
            <div class="card-body text-center">
                <div class="mb-2">
                    <i class="fas fa-clock fa-2x opacity-75"></i>
                </div>
                <h4><?php
                    $totalLate = array_sum(array_map(function($status) { return $status === 'late' ? 1 : 0; }, array_column($attendance, 'status')));
                    echo $totalLate;
                ?></h4>
                <p class="mb-0 opacity-75 small">Late Days</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card bg-info text-white">
            <div class="card-body text-center">
                <div class="mb-2">
                    <i class="fas fa-percentage fa-2x opacity-75"></i>
                </div>
                <h4><?php
                    $totalDays = count($attendance);
                    $percentage = $totalDays > 0 ? round(($totalPresent / $totalDays) * 100, 1) : 0;
                    echo $percentage . '%';
                ?></h4>
                <p class="mb-0 opacity-75 small">Attendance Rate</p>
            </div>
        </div>
    </div>
</div>

<!-- Attendance Records -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Attendance History</h5>
    </div>
    <div class="card-body">
        <?php if (!empty($attendance)): ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Class</th>
                            <th>Status</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($attendance as $record): ?>
                            <tr>
                                <td><?php echo date('M d, Y', strtotime($record['attendance_date'])); ?></td>
                                <td><?php echo $record['class_name'] . ' ' . $record['section']; ?></td>
                                <td>
                                    <?php
                                    $statusClass = '';
                                    $statusIcon = '';
                                    switch ($record['status']) {
                                        case 'present':
                                            $statusClass = 'success';
                                            $statusIcon = 'check-circle';
                                            break;
                                        case 'absent':
                                            $statusClass = 'danger';
                                            $statusIcon = 'times-circle';
                                            break;
                                        case 'late':
                                            $statusClass = 'warning';
                                            $statusIcon = 'clock';
                                            break;
                                    }
                                    ?>
                                    <span class="badge bg-<?php echo $statusClass; ?>">
                                        <i class="fas fa-<?php echo $statusIcon; ?> me-1"></i>
                                        <?php echo ucfirst($record['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo $record['remarks'] ?? '-'; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <nav aria-label="Attendance pagination" class="mt-3">
                    <ul class="pagination justify-content-center">
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php echo $i == $current_page ? 'active' : ''; ?>">
                                <a class="page-link" href="/student/attendance?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            <?php endif; ?>

        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-calendar-check fa-4x text-muted mb-3"></i>
                <h4 class="text-muted">No Attendance Records</h4>
                <p class="text-muted">Your attendance records will appear here once they are marked.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
include 'layout.php';
?>