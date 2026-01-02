<?php
$active_page = 'exams';
$page_title = 'Exams & Results Management';
ob_start();
?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><i class="fas fa-file-alt text-primary me-2"></i>Exams & Results Management</h4>
        <p class="text-muted mb-0">Create exams, manage results, and track academic performance</p>
    </div>
    <a href="/admin/exams/create" class="btn btn-primary btn-lg">
        <i class="fas fa-plus me-2"></i>Create New Exam
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
        <div class="card text-center h-100">
            <div class="card-body">
                <i class="fas fa-plus-circle fa-2x text-primary mb-3"></i>
                <h6 class="card-title">Create Exam</h6>
                <a href="/admin/exams/create" class="btn btn-primary btn-sm">Create New</a>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card text-center h-100">
            <div class="card-body">
                <i class="fas fa-calendar-alt fa-2x text-success mb-3"></i>
                <h6 class="card-title">Schedule Subjects</h6>
                <a href="/admin/exams/schedule" class="btn btn-success btn-sm">Schedule</a>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card text-center h-100">
            <div class="card-body">
                <i class="fas fa-id-card fa-2x text-info mb-3"></i>
                <h6 class="card-title">Admit Cards</h6>
                <a href="/admin/exams/admit-cards" class="btn btn-info btn-sm">Generate</a>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card text-center h-100">
            <div class="card-body">
                <i class="fas fa-file-alt fa-2x text-warning mb-3"></i>
                <h6 class="card-title">Marksheets</h6>
                <a href="/admin/exams/marksheets" class="btn btn-warning btn-sm">Generate</a>
            </div>
        </div>
    </div>
</div>

<!-- Exams Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">All Exams</h5>
        <div class="d-flex gap-2">
            <select class="form-select form-select-sm" id="classFilter">
                <option value="">All Classes</option>
                <?php
                $classes = array_unique(array_column($exams, 'class_name'));
                foreach ($classes as $class):
                    if ($class):
                ?>
                    <option value="<?php echo $class; ?>"><?php echo $class; ?></option>
                <?php endif; endforeach; ?>
            </select>
            <select class="form-select form-select-sm" id="statusFilter">
                <option value="">All Status</option>
                <option value="upcoming">Upcoming</option>
                <option value="ongoing">Ongoing</option>
                <option value="completed">Completed</option>
            </select>
        </div>
    </div>
    <div class="card-body">
        <?php if (!empty($exams)): ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Exam Name</th>
                            <th>Type</th>
                            <th>Class</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="examsTableBody">
                        <?php foreach ($exams as $exam): ?>
                            <?php
                            $today = date('Y-m-d');
                            $status = 'upcoming';
                            $statusClass = 'secondary';
                            if ($exam['start_date'] <= $today && $exam['end_date'] >= $today) {
                                $status = 'ongoing';
                                $statusClass = 'success';
                            } elseif ($exam['end_date'] < $today) {
                                $status = 'completed';
                                $statusClass = 'info';
                            }
                            ?>
                            <tr data-class="<?php echo $exam['class_name']; ?>" data-status="<?php echo $status; ?>">
                                <td><?php echo $exam['exam_name']; ?></td>
                                <td><?php echo ucfirst($exam['exam_type']); ?></td>
                                <td><?php echo $exam['class_name']; ?></td>
                                <td><?php echo date('M d, Y', strtotime($exam['start_date'])); ?></td>
                                <td><?php echo date('M d, Y', strtotime($exam['end_date'])); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $statusClass; ?>"><?php echo ucfirst($status); ?></span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="/admin/exams/view/<?php echo $exam['id']; ?>" class="btn btn-sm btn-outline-info" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="/admin/exams/edit/<?php echo $exam['id']; ?>" class="btn btn-sm btn-outline-primary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="/admin/exams/results/<?php echo $exam['id']; ?>" class="btn btn-sm btn-outline-success" title="Enter Results">
                                            <i class="fas fa-chart-line"></i>
                                        </a>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                <i class="fas fa-print"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="/admin/exams/admit-cards/<?php echo $exam['id']; ?>">Admit Cards</a></li>
                                                <li><a class="dropdown-item" href="/admin/exams/marksheets?exam_id=<?php echo $exam['id']; ?>">Marksheets</a></li>
                                                <li><a class="dropdown-item" href="/admin/exams/certificates/<?php echo $exam['id']; ?>">Certificates</a></li>
                                            </ul>
                                        </div>
                                        <a href="/admin/exams/delete/<?php echo $exam['id']; ?>" class="btn btn-sm btn-outline-danger"
                                            onclick="return confirm('Are you sure you want to delete this exam?')" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-file-alt fa-4x text-muted mb-3"></i>
                <h4 class="text-muted">No Exams Found</h4>
                <p class="text-muted">Start by creating your first exam.</p>
                <a href="/admin/exams/create" class="btn btn-primary btn-lg">
                    <i class="fas fa-plus me-2"></i>Create Your First Exam
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Filter functionality
document.getElementById('classFilter').addEventListener('change', filterExams);
document.getElementById('statusFilter').addEventListener('change', filterExams);

function filterExams() {
    const classFilter = document.getElementById('classFilter').value.toLowerCase();
    const statusFilter = document.getElementById('statusFilter').value;

    const rows = document.querySelectorAll('#examsTableBody tr');

    rows.forEach(row => {
        const className = row.dataset.class.toLowerCase();
        const status = row.dataset.status;

        const matchesClass = !classFilter || className.includes(classFilter);
        const matchesStatus = !statusFilter || status === statusFilter;

        if (matchesClass && matchesStatus) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}
</script>

<?php
$content = ob_get_clean();
include dirname(__DIR__) . '/layout.php';
?>