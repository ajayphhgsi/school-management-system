<?php
$active_page = 'exams';
$page_title = 'View Exam Details';
ob_start();
?>

<style>
.exam-detail-card {
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
}

.subject-table th {
    background-color: #f8f9fa;
    font-weight: 600;
}

.results-summary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 20px;
}

.stat-card {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 8px;
    padding: 15px;
    text-align: center;
    margin-bottom: 10px;
}

.stat-number {
    font-size: 24px;
    font-weight: bold;
    display: block;
}

.stat-label {
    font-size: 12px;
    opacity: 0.9;
}
</style>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><i class="fas fa-eye text-info me-2"></i>Exam Details</h4>
        <p class="text-muted mb-0">View complete information about <?php echo htmlspecialchars($exam['exam_name']); ?></p>
    </div>
    <div>
        <a href="/admin/exams" class="btn btn-secondary me-2">
            <i class="fas fa-arrow-left me-1"></i>Back to Exams
        </a>
        <a href="/admin/exams/<?php echo $exam['id']; ?>/results" class="btn btn-success">
            <i class="fas fa-chart-line me-1"></i>Enter Results
        </a>
    </div>
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

<!-- Exam Information -->
<div class="row">
    <div class="col-lg-8">
        <div class="exam-detail-card">
            <h5 class="mb-3"><i class="fas fa-info-circle text-primary me-2"></i>Exam Information</h5>
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Exam Name:</strong> <?php echo htmlspecialchars($exam['exam_name']); ?></p>
                    <p><strong>Exam Type:</strong> <?php echo ucfirst($exam['exam_type']); ?></p>
                    <p><strong>Class:</strong> <?php echo htmlspecialchars($exam['class_name'] . ' ' . $exam['section']); ?></p>
                    <p><strong>Academic Year:</strong> <?php echo htmlspecialchars($exam['year_name'] ?? 'N/A'); ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong>Start Date:</strong> <?php echo date('M d, Y', strtotime($exam['start_date'])); ?></p>
                    <p><strong>End Date:</strong> <?php echo date('M d, Y', strtotime($exam['end_date'])); ?></p>
                    <p><strong>Status:</strong>
                        <span class="badge bg-<?php
                            $today = date('Y-m-d');
                            if ($exam['start_date'] <= $today && $exam['end_date'] >= $today) {
                                echo 'success';
                            } elseif ($exam['end_date'] < $today) {
                                echo 'secondary';
                            } else {
                                echo 'warning';
                            }
                        ?>">
                            <?php
                            if ($exam['start_date'] <= $today && $exam['end_date'] >= $today) {
                                echo 'Ongoing';
                            } elseif ($exam['end_date'] < $today) {
                                echo 'Completed';
                            } else {
                                echo 'Upcoming';
                            }
                            ?>
                        </span>
                    </p>
                    <p><strong>Created:</strong> <?php echo date('M d, Y H:i', strtotime($exam['created_at'])); ?></p>
                </div>
            </div>
        </div>

        <!-- Subject Schedule -->
        <div class="exam-detail-card">
            <h5 class="mb-3"><i class="fas fa-calendar-alt text-success me-2"></i>Subject Schedule</h5>
            <?php if (!empty($exam_subjects)): ?>
                <div class="table-responsive">
                    <table class="table table-striped subject-table">
                        <thead>
                            <tr>
                                <th>Subject</th>
                                <th>Exam Date</th>
                                <th>Time</th>
                                <th>Max Marks</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($exam_subjects as $subject): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($subject['subject_name']); ?> (<?php echo htmlspecialchars($subject['subject_code']); ?>)</td>
                                    <td><?php echo (!is_null($subject['exam_date']) && !empty($subject['exam_date'])) ? date('M d, Y', strtotime($subject['exam_date'])) : 'Not Set'; ?></td>
                                    <td><?php echo ((!is_null($subject['start_time']) && !empty($subject['start_time'])) ? date('H:i', strtotime($subject['start_time'])) : 'TBD') . ' - ' . ((!is_null($subject['end_time']) && !empty($subject['end_time'])) ? date('H:i', strtotime($subject['end_time'])) : 'TBD'); ?></td>
                                    <td><?php echo $subject['max_marks']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-muted">No subjects scheduled for this exam.</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Results Summary -->
        <?php if ($results_summary && $results_summary['total_students'] > 0): ?>
            <div class="results-summary">
                <h5 class="mb-3"><i class="fas fa-chart-bar me-2"></i>Results Summary</h5>
                <div class="row">
                    <div class="col-6">
                        <div class="stat-card">
                            <span class="stat-number"><?php echo $results_summary['total_students']; ?></span>
                            <span class="stat-label">Students</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="stat-card">
                            <span class="stat-number"><?php echo number_format($results_summary['avg_percentage'], 1); ?>%</span>
                            <span class="stat-label">Avg Score</span>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-3">
                        <div class="stat-card">
                            <span class="stat-number"><?php echo $results_summary['a_grades']; ?></span>
                            <span class="stat-label">A Grade</span>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="stat-card">
                            <span class="stat-number"><?php echo $results_summary['b_grades']; ?></span>
                            <span class="stat-label">B Grade</span>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="stat-card">
                            <span class="stat-number"><?php echo $results_summary['c_grades']; ?></span>
                            <span class="stat-label">C Grade</span>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="stat-card">
                            <span class="stat-number"><?php echo $results_summary['f_grades']; ?></span>
                            <span class="stat-label">F Grade</span>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Quick Actions -->
        <div class="exam-detail-card">
            <h5 class="mb-3"><i class="fas fa-bolt text-warning me-2"></i>Quick Actions</h5>
            <div class="d-grid gap-2">
                <a href="/admin/exams/admit-cards?exam_id=<?php echo $exam['id']; ?>" class="btn btn-outline-info">
                    <i class="fas fa-id-card me-1"></i>Generate Admit Cards
                </a>
                <a href="/admin/exams/marksheets?exam_id=<?php echo $exam['id']; ?>" class="btn btn-outline-warning">
                    <i class="fas fa-file-alt me-1"></i>Generate Marksheets
                </a>
                <a href="/admin/generate-academic-report?type=exam-results&exam_id=<?php echo $exam['id']; ?>" class="btn btn-outline-success">
                    <i class="fas fa-chart-line me-1"></i>View Results Report
                </a>
            </div>
        </div>
    </div>
</div>

<script>
// Any additional JavaScript can go here
</script>

<?php
unset($_SESSION['flash']['old'], $_SESSION['flash']['errors']);
$content = ob_get_clean();
include dirname(__DIR__) . '/layout.php';
?>