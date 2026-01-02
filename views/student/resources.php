<?php
$active_page = 'resources';
$page_title = 'Study Resources';
ob_start();
?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><i class="fas fa-book text-info me-2"></i>Study Resources</h4>
        <p class="text-muted mb-0">Access study materials, assignments, and library resources</p>
    </div>
</div>

<!-- Quick Stats -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card bg-primary text-white">
            <div class="card-body text-center">
                <div class="mb-2">
                    <i class="fas fa-book-open fa-2x opacity-75"></i>
                </div>
                <h5><?php echo count($study_materials); ?></h5>
                <p class="mb-0 opacity-75 small">Study Materials</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card bg-warning text-white">
            <div class="card-body text-center">
                <div class="mb-2">
                    <i class="fas fa-tasks fa-2x opacity-75"></i>
                </div>
                <h5><?php echo count($assignments); ?></h5>
                <p class="mb-0 opacity-75 small">Assignments</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card bg-success text-white">
            <div class="card-body text-center">
                <div class="mb-2">
                    <i class="fas fa-graduation-cap fa-2x opacity-75"></i>
                </div>
                <h5><?php echo (is_array($student) ? $student['class_name'] . ' ' . $student['section'] : 'N/A'); ?></h5>
                <p class="mb-0 opacity-75 small">Your Class</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card bg-info text-white">
            <div class="card-body text-center">
                <div class="mb-2">
                    <i class="fas fa-calendar fa-2x opacity-75"></i>
                </div>
                <h5><?php echo date('M Y'); ?></h5>
                <p class="mb-0 opacity-75 small">Current Month</p>
            </div>
        </div>
    </div>
</div>

<!-- Study Materials -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-book-open text-primary me-2"></i>Study Materials</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($study_materials)): ?>
                    <div class="row">
                        <?php foreach ($study_materials as $material): ?>
                            <div class="col-md-6 mb-3">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <div class="d-flex align-items-start">
                                            <div class="flex-shrink-0 me-3">
                                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                    <i class="fas fa-file-alt"></i>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="card-title mb-1"><?php echo htmlspecialchars($material['title']); ?></h6>
                                                <p class="card-text small text-muted mb-2"><?php echo htmlspecialchars(substr($material['description'], 0, 100)) . '...'; ?></p>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <small class="text-muted">
                                                        <i class="fas fa-calendar me-1"></i><?php echo date('M d, Y', strtotime($material['event_date'])); ?>
                                                    </small>
                                                    <a href="#" class="btn btn-sm btn-outline-primary">Download</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-book-open fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No Study Materials Available</h5>
                        <p class="text-muted">Study materials will be uploaded by your teachers.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Assignments -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-tasks text-warning me-2"></i>Assignments & Homework</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($assignments)): ?>
                    <div class="row">
                        <?php foreach ($assignments as $assignment): ?>
                            <div class="col-md-6 mb-3">
                                <div class="card h-100 border-warning">
                                    <div class="card-body">
                                        <div class="d-flex align-items-start">
                                            <div class="flex-shrink-0 me-3">
                                                <div class="bg-warning text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                    <i class="fas fa-clipboard-list"></i>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="card-title mb-1"><?php echo htmlspecialchars($assignment['title']); ?></h6>
                                                <p class="card-text small text-muted mb-2"><?php echo htmlspecialchars(substr($assignment['description'], 0, 100)) . '...'; ?></p>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <small class="text-muted">
                                                        <i class="fas fa-calendar me-1"></i>Due: <?php echo date('M d, Y', strtotime($assignment['event_date'])); ?>
                                                        <span class="badge bg-danger ms-2">Pending</span>
                                                    </small>
                                                    <a href="#" class="btn btn-sm btn-warning">Submit</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No Assignments Available</h5>
                        <p class="text-muted">Assignments will be posted by your teachers.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Library Resources -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-university text-success me-2"></i>Library Resources</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php foreach ($library_resources as $resource): ?>
                        <div class="col-md-3 mb-3">
                            <div class="card h-100 text-center">
                                <div class="card-body">
                                    <div class="mb-3">
                                        <i class="fas fa-book fa-2x text-success"></i>
                                    </div>
                                    <h6 class="card-title"><?php echo htmlspecialchars($resource['title']); ?></h6>
                                    <p class="card-text small text-muted"><?php echo htmlspecialchars($resource['description']); ?></p>
                                    <a href="<?php echo $resource['link']; ?>" class="btn btn-success btn-sm">Access</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Subject-wise Resources -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-graduation-cap text-info me-2"></i>Subject-wise Resources</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Subject-specific resources for <?php echo (is_array($student) ? $student['class_name'] . ' ' . $student['section'] : 'N/A'); ?>:</strong>
                    <ul class="mb-0 mt-2">
                        <li>Mathematics: Formulas, practice problems, video tutorials</li>
                        <li>Science: Lab manuals, experiment guides, interactive simulations</li>
                        <li>Languages: Grammar exercises, vocabulary builders, reading comprehension</li>
                        <li>Social Studies: Maps, timelines, historical documents</li>
                        <li>Computer Science: Programming exercises, coding challenges</li>
                    </ul>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <i class="fas fa-video fa-2x text-danger mb-2"></i>
                                <h6>Video Lectures</h6>
                                <p class="small text-muted">Watch recorded lectures and explanations</p>
                                <a href="#" class="btn btn-outline-danger btn-sm">Watch Now</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <i class="fas fa-file-pdf fa-2x text-danger mb-2"></i>
                                <h6>PDF Notes</h6>
                                <p class="small text-muted">Download comprehensive study notes</p>
                                <a href="#" class="btn btn-outline-danger btn-sm">Download</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <i class="fas fa-brain fa-2x text-primary mb-2"></i>
                                <h6>Practice Tests</h6>
                                <p class="small text-muted">Take practice tests and quizzes</p>
                                <a href="#" class="btn btn-outline-primary btn-sm">Start Test</a>
                            </div>
                        </div>
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