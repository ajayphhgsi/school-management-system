<?php
$active_page = 'exams';
$page_title = 'View Exam';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><?php echo $page_title; ?></h4>
        <p class="text-muted mb-0">Exam details and subject schedule</p>
    </div>
    <div>
        <a href="/admin/exams/edit/<?php echo $exam['id']; ?>" class="btn btn-primary me-2">
            <i class="fas fa-edit"></i> Edit Exam
        </a>
        <a href="/admin/exams" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i>Back to Exams
        </a>
    </div>
</div>

<div class="row">
    <!-- Exam Details -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Exam Information</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Exam Name:</strong><br>
                        <?php echo htmlspecialchars($exam['exam_name']); ?>
                    </div>
                    <div class="col-md-6">
                        <strong>Exam Type:</strong><br>
                        <?php echo ucfirst(htmlspecialchars($exam['exam_type'])); ?>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Start Date:</strong><br>
                        <?php echo date('M d, Y', strtotime($exam['start_date'])); ?>
                    </div>
                    <div class="col-md-6">
                        <strong>End Date:</strong><br>
                        <?php echo date('M d, Y', strtotime($exam['end_date'])); ?>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Academic Year:</strong><br>
                        <?php echo htmlspecialchars($exam['year_name'] ?? 'N/A'); ?>
                    </div>
                    <div class="col-md-6">
                        <strong>Status:</strong><br>
                        <span class="badge <?php echo $exam['is_active'] ? 'bg-success' : 'bg-secondary'; ?>">
                            <?php echo $exam['is_active'] ? 'Active' : 'Inactive'; ?>
                        </span>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <strong>Classes:</strong><br>
                        <?php
                        $classes = [];
                        foreach ($exam_subjects as $subject) {
                            $classes[] = $subject['class_name'] . ' ' . $subject['section'];
                        }
                        $classes = array_unique($classes);
                        echo implode(', ', array_map('htmlspecialchars', $classes));
                        ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Subject Schedule -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Subject Schedule</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($exam_subjects)): ?>
                    <?php
                    // Group subjects by class
                    $subjectsByClass = [];
                    foreach ($exam_subjects as $subject) {
                        $classKey = $subject['class_id'];
                        if (!isset($subjectsByClass[$classKey])) {
                            $subjectsByClass[$classKey] = [
                                'class_name' => $subject['class_name'] . ' ' . $subject['section'],
                                'subjects' => []
                            ];
                        }
                        $subjectsByClass[$classKey]['subjects'][] = $subject;
                    }
                    ?>

                    <?php foreach ($subjectsByClass as $classData): ?>
                        <div class="mb-4">
                            <h6 class="text-primary"><?php echo htmlspecialchars($classData['class_name']); ?></h6>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Subject</th>
                                            <th>Date</th>
                                            <th>Time</th>
                                            <th>Max Marks</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($classData['subjects'] as $subject): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($subject['subject_name']); ?></td>
                                                <td><?php echo date('M d, Y', strtotime($subject['exam_date'])); ?></td>
                                                <td><?php echo date('H:i', strtotime($subject['start_time'])) . ' - ' . date('H:i', strtotime($subject['end_time'])); ?></td>
                                                <td><?php echo $subject['max_marks']; ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted">No subjects scheduled for this exam.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Results Summary -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Results Summary</h5>
            </div>
            <div class="card-body">
                <?php if ($results_summary): ?>
                    <div class="row text-center mb-3">
                        <div class="col-6">
                            <div class="h4 text-primary"><?php echo $results_summary['total_students']; ?></div>
                            <small class="text-muted">Total Students</small>
                        </div>
                        <div class="col-6">
                            <div class="h4 text-success"><?php echo $results_summary['total_results']; ?></div>
                            <small class="text-muted">Total Results</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <strong>Average Percentage:</strong><br>
                        <?php echo $results_summary['avg_percentage'] !== null ? number_format($results_summary['avg_percentage'], 2) : '0.00'; ?>%
                    </div>

                    <div class="mb-3">
                        <strong>Percentage Range:</strong><br>
                        <?php echo ($results_summary['min_percentage'] !== null ? number_format($results_summary['min_percentage'], 2) : '0.00') . '% - ' . ($results_summary['max_percentage'] !== null ? number_format($results_summary['max_percentage'], 2) : '0.00'); ?>%
                    </div>

                    <div class="mb-3">
                        <strong>Grade Distribution:</strong>
                        <div class="mt-2">
                            <div class="d-flex justify-content-between">
                                <span>A Grade:</span>
                                <span><?php echo $results_summary['a_grades']; ?></span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>B Grade:</span>
                                <span><?php echo $results_summary['b_grades']; ?></span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>C Grade:</span>
                                <span><?php echo $results_summary['c_grades']; ?></span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>F Grade:</span>
                                <span><?php echo $results_summary['f_grades']; ?></span>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <p class="text-muted">No results available yet.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="/admin/exams/enter-results/<?php echo $exam['id']; ?>" class="btn btn-outline-primary">
                        <i class="fas fa-edit"></i> Enter Results
                    </a>
                    <a href="/admin/exams/generate-admit-cards/<?php echo $exam['id']; ?>" class="btn btn-outline-info">
                        <i class="fas fa-id-card"></i> Generate Admit Cards
                    </a>
                    <a href="/admin/exams/marksheets" class="btn btn-outline-success">
                        <i class="fas fa-file-alt"></i> Generate Marksheets
                    </a>
                    <button type="button" class="btn btn-outline-danger" onclick="confirmDelete()">
                        <i class="fas fa-trash"></i> Delete Exam
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete() {
    if (confirm('Are you sure you want to delete this exam? This action cannot be undone.')) {
        // Create a form to submit delete request
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/admin/exams/delete/<?php echo $exam['id']; ?>';

        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = 'csrf_token';
        csrfInput.value = '<?php echo $csrf_token; ?>';

        form.appendChild(csrfInput);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php
$content = ob_get_clean();
include dirname(__DIR__) . '/layout.php';
?>