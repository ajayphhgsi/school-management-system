<?php
$active_page = 'students';
$page_title = 'Students Management';
ob_start();
?>

<style>
.student-card {
    background: white;
    border: 1px solid #e9ecef;
    border-radius: 12px;
    transition: all 0.3s ease;
    cursor: pointer;
    text-decoration: none;
    color: inherit;
}

.student-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    text-decoration: none;
    color: inherit;
}

.student-avatar {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #f8f9fa;
}

.status-badge {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
    border-radius: 20px;
}

.search-input {
    border-radius: 25px;
    border: 2px solid #e9ecef;
    padding: 0.5rem 1rem;
    transition: all 0.3s ease;
}

.search-input:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

.stats-summary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 2rem;
}

</style>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><i class="fas fa-users text-primary me-2"></i>Students Management</h4>
        <p class="text-muted mb-0">Manage student records and information</p>
    </div>
    <div>
        <div class="btn-group me-2" role="group">
            <a href="/admin/students/bulk-import" class="btn btn-outline-success">
                <i class="fas fa-upload me-1"></i>Bulk Import
            </a>
            <a href="/admin/students/bulk-export" class="btn btn-outline-info">
                <i class="fas fa-download me-1"></i>Bulk Export
            </a>
        </div>
        <a href="/admin/students/print" target="_blank" class="btn btn-outline-secondary me-2">
            <i class="fas fa-print me-1"></i>Print List
        </a>
        <a href="/admin/students/create" class="btn btn-primary btn-lg">
            <i class="fas fa-plus me-2"></i>Add New Student
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

<!-- Stats Summary -->
<div class="stats-summary">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h5 class="mb-1">Student Overview</h5>
            <p class="mb-0 opacity-75" id="statsText">Total <?php echo count($students); ?> students registered in the system</p>
        </div>
        <div class="col-md-4 text-end">
            <div class="d-flex justify-content-end">
                <div class="me-4">
                    <div class="h4 mb-0" id="activeCount"><?php echo count(array_filter($students, fn($s) => $s['is_active'])); ?></div>
                    <small class="opacity-75">Active</small>
                </div>
                <div>
                    <div class="h4 mb-0" id="inactiveCount"><?php echo count(array_filter($students, fn($s) => !$s['is_active'])); ?></div>
                    <small class="opacity-75">Inactive</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Search and Filters -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" class="form-control search-input" id="searchInput" placeholder="Search students by name, scholar number, or class...">
                </div>
            </div>
            <div class="col-md-6">
                <div class="d-flex justify-content-end">
                    <select class="form-select me-2" id="academicYearFilter" style="width: auto;">
                        <option value="current_academic_year" <?php echo ($current_filter ?? 'current_academic_year') === 'current_academic_year' ? 'selected' : ''; ?>>Current Academic Year</option>
                        <option value="all" <?php echo ($current_filter ?? 'current_academic_year') === 'all' ? 'selected' : ''; ?>>All Students</option>
                    </select>
                    <select class="form-select me-2" id="classFilter" style="width: auto;">
                        <option value="">All Classes</option>
                        <?php
                        $classes = array_unique(array_column($students, 'class_name'));
                        foreach ($classes as $class):
                            if ($class):
                        ?>
                            <option value="<?php echo $class; ?>"><?php echo $class; ?></option>
                        <?php endif; endforeach; ?>
                    </select>
                    <select class="form-select" id="statusFilter" style="width: auto;">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Students Table -->
<div class="card">
    <div class="card-body">
        <?php if (!empty($students)): ?>
            <div class="table-responsive">
                <table class="table table-hover" id="studentsTable">
                    <thead class="table-light">
                        <tr>
                            <th>Photo</th>
                            <th>Name</th>
                            <th>Scholar No.</th>
                            <th>Admission Date</th>
                            <th>Class</th>
                            <th>Contact</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student): ?>
                            <tr class="student-item"
                                data-name="<?php echo strtolower($student['first_name'] . ' ' . $student['last_name']); ?>"
                                data-scholar="<?php echo strtolower($student['scholar_number']); ?>"
                                data-class="<?php echo strtolower($student['class_name'] ?? ''); ?>"
                                data-status="<?php echo $student['is_active'] ? 'active' : 'inactive'; ?>">
                                <td>
                                    <?php if ($student['photo']): ?>
                                        <img src="/uploads/<?php echo $student['photo']; ?>" alt="Photo" class="rounded-circle" width="40" height="40" style="object-fit: cover;">
                                    <?php else: ?>
                                        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                            <i class="fas fa-user text-white" style="font-size: 0.8rem;"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="fw-semibold"><?php echo $student['first_name'] . ' ' . $student['last_name']; ?></div>
                                    <small class="text-muted"><?php echo $student['scholar_number']; ?></small>
                                </td>
                                <td><?php echo $student['scholar_number']; ?></td>
                                <td><?php echo $student['admission_date'] ? date('d M Y', strtotime($student['admission_date'])) : 'N/A'; ?></td>
                                <td>
                                    <span class="badge bg-light text-dark">
                                        <i class="fas fa-graduation-cap me-1"></i><?php echo $student['class_name'] ? $student['class_name'] . ' ' . $student['section'] : 'No Class'; ?>
                                    </span>
                                </td>
                                <td>
                                    <div><i class="fas fa-phone me-1"></i><?php echo $student['mobile']; ?></div>
                                    <?php if ($student['email']): ?>
                                        <div><i class="fas fa-envelope me-1"></i><?php echo $student['email']; ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge <?php echo $student['tc_issued'] ? 'bg-warning text-dark' : ($student['is_active'] ? 'bg-success' : 'bg-danger'); ?>">
                                        <?php echo $student['tc_issued'] ? 'TC Issued' : ($student['is_active'] ? 'Active' : 'Inactive'); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="/admin/students/edit/<?php echo $student['id']; ?>" class="btn btn-sm btn-outline-primary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button class="btn btn-sm btn-outline-info" title="View Details" onclick="viewStudent(<?php echo $student['id']; ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <a href="/admin/students/delete/<?php echo $student['id']; ?>" class="btn btn-sm btn-outline-danger"
                                           onclick="return confirm('Are you sure you want to delete this student?')" title="Delete">
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
                <i class="fas fa-users fa-4x text-muted mb-3"></i>
                <h4 class="text-muted">No Students Found</h4>
                <p class="text-muted">Start building your student database by adding the first student.</p>
                <a href="/admin/students/create" class="btn btn-primary btn-lg">
                    <i class="fas fa-plus me-2"></i>Add Your First Student
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Search and Filter Functionality
document.getElementById('searchInput').addEventListener('input', filterStudents);
document.getElementById('classFilter').addEventListener('change', filterStudents);
document.getElementById('statusFilter').addEventListener('change', filterStudents);
document.getElementById('academicYearFilter').addEventListener('change', function() {
    const filter = this.value;
    const url = new URL(window.location);
    url.searchParams.set('filter', filter);
    window.location.href = url.toString();
});

function filterStudents() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const classFilter = document.getElementById('classFilter').value.toLowerCase();
    const statusFilter = document.getElementById('statusFilter').value;

    const students = document.querySelectorAll('.student-item');
    let visibleCount = 0;
    let activeCount = 0;
    let inactiveCount = 0;

    students.forEach(student => {
        const name = student.dataset.name;
        const scholar = student.dataset.scholar;
        const className = student.dataset.class;
        const status = student.dataset.status;

        const matchesSearch = name.includes(searchTerm) || scholar.includes(searchTerm);
        const matchesClass = !classFilter || className.includes(classFilter);
        const matchesStatus = !statusFilter || status === statusFilter;

        if (matchesSearch && matchesClass && matchesStatus) {
            student.style.display = '';
            visibleCount++;
            if (status === 'active') activeCount++;
            else inactiveCount++;
        } else {
            student.style.display = 'none';
        }
    });

    // Update stats
    const statsText = document.getElementById('statsText');
    const activeCountEl = document.getElementById('activeCount');
    const inactiveCountEl = document.getElementById('inactiveCount');

    if (statsText) {
        const totalStudents = students.length;
        if (visibleCount === totalStudents) {
            statsText.textContent = `Total ${totalStudents} students registered in the system`;
        } else {
            statsText.textContent = `Showing ${visibleCount} of ${totalStudents} students`;
        }
    }

    if (activeCountEl) activeCountEl.textContent = activeCount;
    if (inactiveCountEl) inactiveCountEl.textContent = inactiveCount;
}

function viewStudent(studentId) {
    // Redirect to student view page
    window.location.href = '/admin/students/view/' + studentId;
}
</script>

<?php
$content = ob_get_clean();
include dirname(__DIR__) . '/layout.php';
?>