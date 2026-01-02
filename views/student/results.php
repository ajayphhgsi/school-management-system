<?php
$active_page = 'results';
$page_title = 'My Exam Results';
ob_start();
?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><i class="fas fa-chart-line text-success me-2"></i>My Exam Results</h4>
        <p class="text-muted mb-0">View your examination results and grades</p>
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

<?php if (!empty($results)): ?>
    <?php foreach ($results as $examId => $exam): ?>
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-1"><?php echo $exam['exam_name']; ?></h5>
                    <small class="text-muted">
                        <i class="fas fa-calendar me-1"></i><?php echo date('M d, Y', strtotime($exam['start_date'])); ?> •
                        <i class="fas fa-tag me-1"></i><?php echo ucfirst($exam['exam_type']); ?>
                    </small>
                </div>
                <span class="badge bg-primary"><?php echo count($exam['subjects']); ?> Subjects</span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Subject</th>
                                <th>Marks Obtained</th>
                                <th>Max Marks</th>
                                <th>Percentage</th>
                                <th>Grade</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $totalMarks = 0;
                            $totalMaxMarks = 0;
                            foreach ($exam['subjects'] as $subject):
                                $totalMarks += $subject['marks_obtained'];
                                $totalMaxMarks += $subject['max_marks'];
                                $percentage = $subject['max_marks'] > 0 ? round(($subject['marks_obtained'] / $subject['max_marks']) * 100, 2) : 0;
                            ?>
                                <tr>
                                    <td>
                                        <strong><?php echo $subject['subject_name']; ?></strong>
                                        <?php if ($subject['subject_code']): ?>
                                            <br><small class="text-muted"><?php echo $subject['subject_code']; ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $subject['marks_obtained']; ?></td>
                                    <td><?php echo $subject['max_marks']; ?></td>
                                    <td><?php echo $percentage; ?>%</td>
                                    <td>
                                        <?php if ($subject['grade']): ?>
                                            <span class="badge bg-<?php echo $subject['grade'] >= 'A' ? 'success' : ($subject['grade'] >= 'C' ? 'warning' : 'danger'); ?>">
                                                <?php echo $subject['grade']; ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($percentage >= 60): ?>
                                            <span class="badge bg-success">Pass</span>
                                        <?php elseif ($percentage >= 40): ?>
                                            <span class="badge bg-warning">Pass</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Fail</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>

                            <!-- Total Row -->
                            <tr class="table-primary">
                                <th colspan="2">Total</th>
                                <th><?php echo $totalMarks; ?>/<?php echo $totalMaxMarks; ?></th>
                                <th><?php echo $totalMaxMarks > 0 ? round(($totalMarks / $totalMaxMarks) * 100, 2) : 0; ?>%</th>
                                <th>
                                    <?php
                                    $overallPercentage = $totalMaxMarks > 0 ? ($totalMarks / $totalMaxMarks) * 100 : 0;
                                    if ($overallPercentage >= 90) echo 'A+';
                                    elseif ($overallPercentage >= 80) echo 'A';
                                    elseif ($overallPercentage >= 70) echo 'B+';
                                    elseif ($overallPercentage >= 60) echo 'B';
                                    elseif ($overallPercentage >= 50) echo 'C';
                                    elseif ($overallPercentage >= 40) echo 'D';
                                    else echo 'F';
                                    ?>
                                </th>
                                <th>
                                    <?php if ($overallPercentage >= 40): ?>
                                        <span class="badge bg-success">Pass</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Fail</span>
                                    <?php endif; ?>
                                </th>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

<?php else: ?>
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="fas fa-chart-line fa-4x text-muted mb-3"></i>
            <h4 class="text-muted">No Exam Results Yet</h4>
            <p class="text-muted">Your exam results will appear here once they are published by your teachers.</p>
        </div>
    </div>
<?php endif; ?>

<?php
$content = ob_get_clean();
include 'layout.php';
?>