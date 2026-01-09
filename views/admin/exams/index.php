<?php
$active_page = 'exams';
$page_title = 'Exam Management';
ob_start();
?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><i class="fas fa-graduation-cap text-primary me-2"></i>Exam Management</h4>
        <p class="text-muted mb-0">Manage examinations, results, and certificates</p>
    </div>
    <a href="/admin/exams/create" class="btn btn-primary btn-lg">
        <i class="fas fa-plus me-2"></i>Create Exam
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

<!-- Quick Actions -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card h-100 border-primary">
            <div class="card-body text-center">
                <i class="fas fa-plus-circle fa-3x text-primary mb-3"></i>
                <h5 class="card-title">Create Exam</h5>
                <p class="card-text text-muted">Set up new examinations with subjects and schedules</p>
                <a href="/admin/exams/create" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus me-1"></i>Create Exam
                </a>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card h-100 border-success">
            <div class="card-body text-center">
                <i class="fas fa-id-card fa-3x text-success mb-3"></i>
                <h5 class="card-title">Admit Cards</h5>
                <p class="card-text text-muted">Generate and print admit cards for students</p>
                <a href="/admin/exams/admit-cards" class="btn btn-success btn-sm">
                    <i class="fas fa-id-card me-1"></i>Generate Cards
                </a>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card h-100 border-warning">
            <div class="card-body text-center">
                <i class="fas fa-edit fa-3x text-warning mb-3"></i>
                <h5 class="card-title">Marks Entry</h5>
                <p class="card-text text-muted">Enter and manage student marks for exams</p>
                <a href="/admin/exams/marks-entry" class="btn btn-warning btn-sm">
                    <i class="fas fa-edit me-1"></i>Enter Marks
                </a>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card h-100 border-info">
            <div class="card-body text-center">
                <i class="fas fa-file-alt fa-3x text-info mb-3"></i>
                <h5 class="card-title">Marksheets</h5>
                <p class="card-text text-muted">Generate and print marksheets for students</p>
                <a href="/admin/exams/marksheets" class="btn btn-info btn-sm">
                    <i class="fas fa-file-alt me-1"></i>Generate Sheets
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Exams Table -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">All Exams</h5>
    </div>
    <div class="card-body">
        <?php if (!empty($exams)): ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Exam Name</th>
                            <th>Type</th>
                            <th>Classes</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($exams as $exam): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($exam['exam_name']); ?></td>
                                <td>
                                    <span class="badge bg-info"><?php echo ucfirst($exam['exam_type']); ?></span>
                                </td>
                                <td>
                                    <div class="d-flex flex-wrap gap-1">
                                        <?php
                                        $classes = explode(', ', $exam['class_names']);
                                        foreach ($classes as $className) {
                                            echo '<span class="badge bg-light text-dark">' . htmlspecialchars(trim($className)) . '</span>';
                                        }
                                        ?>
                                    </div>
                                    <?php if ($exam['class_count'] > 3): ?>
                                        <small class="text-muted">+<?php echo $exam['class_count'] - 3; ?> more</small>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($exam['start_date'])); ?></td>
                                <td><?php echo date('M d, Y', strtotime($exam['end_date'])); ?></td>
                                <td>
                                    <span class="badge <?php echo $exam['is_active'] ? 'bg-success' : 'bg-secondary'; ?>">
                                        <?php echo $exam['is_active'] ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="/admin/exams/view/<?php echo $exam['id']; ?>" class="btn btn-sm btn-outline-primary" title="View Exam">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="/admin/exams/edit/<?php echo $exam['id']; ?>" class="btn btn-sm btn-outline-secondary" title="Edit Exam">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="/admin/exams/results/<?php echo $exam['id']; ?>" class="btn btn-sm btn-outline-success" title="Enter Results">
                                            <i class="fas fa-clipboard-list"></i>
                                        </a>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-info dropdown-toggle" type="button" data-bs-toggle="dropdown" title="More Actions">
                                                <i class="fas fa-ellipsis-h"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="/admin/exams/admit-cards/<?php echo $exam['id']; ?>"><i class="fas fa-id-card me-2"></i>Admit Cards</a></li>
                                                <li><a class="dropdown-item" href="/admin/exams/marksheets/<?php echo $exam['id']; ?>"><i class="fas fa-file-alt me-2"></i>Marksheets</a></li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li><a class="dropdown-item text-danger" href="/admin/exams/delete/<?php echo $exam['id']; ?>" onclick="return confirm('Are you sure you want to delete this exam?')"><i class="fas fa-trash me-2"></i>Delete</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-graduation-cap fa-4x text-muted mb-3"></i>
                <h4 class="text-muted">No Exams Found</h4>
                <p class="text-muted">Start by creating your first examination.</p>
                <a href="/admin/exams/create" class="btn btn-primary btn-lg">
                    <i class="fas fa-plus me-2"></i>Create Your First Exam
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
include dirname(__DIR__) . '/layout.php';
?>