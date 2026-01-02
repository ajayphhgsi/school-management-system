<?php
$active_page = 'classes';
$page_title = 'Add New Subject';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><?php echo $page_title; ?></h4>
        <p class="text-muted mb-0">Add a new subject to the system</p>
    </div>
    <div>
        <a href="/admin/classes" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i>Back to Classes & Subjects
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

<?php if (isset($_SESSION['flash']['errors'])): ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <?php foreach ($_SESSION['flash']['errors'] as $error): ?>
                <li><?php echo $error[0]; ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form method="POST" action="/admin/subjects">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="subject_name" class="form-label">Subject Name *</label>
                    <input type="text" class="form-control" id="subject_name" name="subject_name"
                           value="<?php echo $_SESSION['flash']['old']['subject_name'] ?? ''; ?>" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="subject_code" class="form-label">Subject Code *</label>
                    <input type="text" class="form-control" id="subject_code" name="subject_code"
                           value="<?php echo $_SESSION['flash']['old']['subject_code'] ?? ''; ?>" required>
                </div>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" rows="3"><?php echo $_SESSION['flash']['old']['description'] ?? ''; ?></textarea>
            </div>

            <div class="d-flex justify-content-end">
                <a href="/admin/classes" class="btn btn-secondary me-2">Cancel</a>
                <button type="submit" class="btn btn-primary">Save Subject</button>
            </div>
        </form>
    </div>
</div>

<?php
unset($_SESSION['flash']['old'], $_SESSION['flash']['errors']);
$content = ob_get_clean();
include dirname(__DIR__) . '/layout.php';
?>