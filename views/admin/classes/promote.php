<?php
$active_page = 'classes';
$page_title = 'Student Promotion';
ob_start();
?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><i class="fas fa-graduation-cap text-success me-2"></i>Student Promotion</h4>
        <p class="text-muted mb-0">Promote students to next class based on academic performance</p>
    </div>
    <a href="/admin/classes" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-1"></i>Back to Classes
    </a>
</div>

<!-- Flash Messages -->
<?php if (isset($_SESSION['flash']['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i><?php echo $_SESSION['flash']['success']; unset($_SESSION['flash']['success']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['flash']['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i><?php echo $_SESSION['flash']['error']; unset($_SESSION['flash']['error']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row">
    <!-- Promotion Form -->
    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Promotion Settings</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="/admin/classes/promote" id="promotionForm">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="from_class_id" class="form-label">From Class *</label>
                            <select class="form-select" id="from_class_id" name="from_class_id" required>
                                <option value="">Select Source Class</option>
                                <?php foreach ($classes as $class): ?>
                                    <option value="<?php echo $class['id']; ?>">
                                        <?php echo $class['class_name'] . ' ' . $class['section']; ?>
                                        (<?php echo $class['student_count']; ?> students)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="to_class_id" class="form-label">To Class *</label>
                            <select class="form-select" id="to_class_id" name="to_class_id" required>
                                <option value="">Select Target Class</option>
                                <?php foreach ($classes as $class): ?>
                                    <option value="<?php echo $class['id']; ?>">
                                        <?php echo $class['class_name'] . ' ' . $class['section']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="academic_year" class="form-label">Academic Year</label>
                        <input type="text" class="form-control" id="academic_year" name="academic_year"
                               value="<?php echo date('Y') . '-' . (date('Y') + 1); ?>" readonly>
                        <div class="form-text">Current academic year for promotion</div>
                    </div>

                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle me-2"></i>Promotion Criteria:</h6>
                        <ul class="mb-0">
                            <li>Attendance rate must be above 75%</li>
                            <li>No failing grades in final examinations</li>
                            <li>Student must be currently active</li>
                        </ul>
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <button type="button" class="btn btn-outline-primary" onclick="previewPromotion()">
                            <i class="fas fa-eye me-1"></i>Preview Promotion
                        </button>
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="fas fa-graduation-cap me-2"></i>Process Promotion
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Promotion Preview -->
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Promotion Preview</h5>
            </div>
            <div class="card-body">
                <div id="previewContent">
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-users fa-3x mb-3"></i>
                        <p>Select classes to preview promotion</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0">Class Statistics</h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <div class="mb-2">
                            <i class="fas fa-school fa-2x text-primary"></i>
                        </div>
                        <h5><?php echo count($classes); ?></h5>
                        <small class="text-muted">Total Classes</small>
                    </div>
                    <div class="col-6">
                        <div class="mb-2">
                            <i class="fas fa-users fa-2x text-success"></i>
                        </div>
                        <h5><?php echo array_sum(array_column($classes, 'student_count')); ?></h5>
                        <small class="text-muted">Total Students</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Promotions History -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Recent Promotion History</h5>
    </div>
    <div class="card-body">
        <?php
        // Get recent promotion logs
        $promotionLogs = $this->db->select("
            SELECT al.*, u.first_name, u.last_name, s.first_name as student_first, s.last_name as student_last,
                   c1.class_name as from_class, c2.class_name as to_class
            FROM audit_logs al
            LEFT JOIN users u ON al.user_id = u.id
            LEFT JOIN students s ON al.record_id = s.id
            LEFT JOIN classes c1 ON JSON_EXTRACT(al.old_values, '$.class_id') = c1.id
            LEFT JOIN classes c2 ON JSON_EXTRACT(al.new_values, '$.class_id') = c2.id
            WHERE al.action = 'student_promotion'
            ORDER BY al.created_at DESC
            LIMIT 10
        ");
        ?>

        <?php if (!empty($promotionLogs)): ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Student</th>
                            <th>From Class</th>
                            <th>To Class</th>
                            <th>Promoted By</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($promotionLogs as $log): ?>
                            <tr>
                                <td><?php echo date('M d, Y H:i', strtotime($log['created_at'])); ?></td>
                                <td><?php echo htmlspecialchars($log['student_first'] . ' ' . $log['student_last']); ?></td>
                                <td><?php echo htmlspecialchars($log['from_class'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($log['to_class'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($log['first_name'] . ' ' . $log['last_name']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-4">
                <i class="fas fa-history fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No Promotion History</h5>
                <p class="text-muted">Promotion records will appear here after processing student promotions.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function previewPromotion() {
    const fromClassId = document.getElementById('from_class_id').value;
    const toClassId = document.getElementById('to_class_id').value;

    if (!fromClassId || !toClassId) {
        alert('Please select both source and target classes');
        return;
    }

    // Show loading
    document.getElementById('previewContent').innerHTML = `
        <div class="text-center py-4">
            <i class="fas fa-spinner fa-spin fa-2x mb-3"></i>
            <p>Loading preview...</p>
        </div>
    `;

    // In a real implementation, this would make an AJAX call to get promotion preview
    // For now, we'll show a static preview
    setTimeout(() => {
        document.getElementById('previewContent').innerHTML = `
            <div class="alert alert-info">
                <h6>Promotion Preview</h6>
                <p><strong>From:</strong> Selected source class</p>
                <p><strong>To:</strong> Selected target class</p>
                <p><strong>Eligible Students:</strong> Will be calculated based on criteria</p>
                <p><small class="text-muted">Click "Process Promotion" to execute the promotion</small></p>
            </div>
        `;
    }, 1000);
}

// Form validation
document.getElementById('promotionForm').addEventListener('submit', function(e) {
    const fromClass = document.getElementById('from_class_id').value;
    const toClass = document.getElementById('to_class_id').value;

    if (fromClass === toClass) {
        e.preventDefault();
        alert('Source and target classes cannot be the same');
        return;
    }

    if (!confirm('Are you sure you want to process this promotion? This action cannot be undone.')) {
        e.preventDefault();
    }
});
</script>

<?php
$content = ob_get_clean();
include dirname(__DIR__) . '/layout.php';
?>