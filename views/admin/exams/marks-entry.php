<?php
$active_page = 'exams';
$page_title = 'Marks Entry';
ob_start();
?>

<style>
.marks-entry-card {
    transition: all 0.3s ease;
}

.marks-entry-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.class-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    border-radius: 12px;
}

.class-card .card-body {
    padding: 2rem;
}

.class-stats {
    display: flex;
    justify-content: space-between;
    margin-top: 1rem;
}

.class-stat {
    text-align: center;
    flex: 1;
}

.class-stat h4 {
    font-size: 1.5rem;
    margin-bottom: 0.25rem;
}

.class-stat small {
    opacity: 0.8;
    font-size: 0.8rem;
}
</style>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><i class="fas fa-edit text-success me-2"></i>Class-wise Marks Entry</h4>
        <p class="text-muted mb-0">Select a class to enter marks for all students and subjects</p>
    </div>
    <div>
        <a href="/admin/exams" class="btn btn-secondary me-2">
            <i class="fas fa-arrow-left me-1"></i>Back to Exams
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

<!-- Classes Grid -->
<div class="row">
    <?php if (!empty($classes)): ?>
        <?php foreach ($classes as $class): ?>
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card marks-entry-card class-card h-100">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h5 class="card-title mb-1"><?php echo htmlspecialchars($class['class_name']); ?> <?php echo htmlspecialchars($class['section']); ?></h5>
                                <small class="opacity-75">Academic Year: <?php echo htmlspecialchars($class['academic_year'] ?? 'Current'); ?></small>
                            </div>
                            <div class="bg-white bg-opacity-20 rounded-circle p-2">
                                <i class="fas fa-users fa-lg text-white"></i>
                            </div>
                        </div>

                        <?php
                        // Get student count and subjects for this class
                        $studentCount = $this->db->selectOne("SELECT COUNT(*) as count FROM students WHERE class_id = ? AND is_active = 1", [$class['id']])['count'];
                        $subjectCount = $this->db->selectOne("SELECT COUNT(*) as count FROM class_subjects WHERE class_id = ?", [$class['id']])['count'];
                        ?>

                        <div class="class-stats mb-3">
                            <div class="class-stat">
                                <h4><?php echo $studentCount; ?></h4>
                                <small>Students</small>
                            </div>
                            <div class="class-stat">
                                <h4><?php echo $subjectCount; ?></h4>
                                <small>Subjects</small>
                            </div>
                        </div>

                        <div class="mt-auto">
                            <a href="/admin/exams/class-marks/<?php echo $class['id']; ?>" class="btn btn-light btn-lg w-100">
                                <i class="fas fa-edit me-2"></i>Enter Marks
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-12">
            <div class="text-center py-5">
                <i class="fas fa-chalkboard fa-4x text-muted mb-3"></i>
                <h4 class="text-muted">No Classes Found</h4>
                <p class="text-muted">No active classes available for marks entry.</p>
                <a href="/admin/classes" class="btn btn-primary">Manage Classes</a>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
// Add some interactive effects
document.addEventListener('DOMContentLoaded', function() {
    // Add hover effects to cards
    const cards = document.querySelectorAll('.marks-entry-card');
    cards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
        });

        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
});
</script>

<?php
$content = ob_get_clean();
include dirname(__DIR__) . '/layout.php';
?>