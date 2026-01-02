<?php
$active_page = 'homepage';
$page_title = 'Edit About Section';
ob_start();
?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><i class="fas fa-info-circle text-success me-2"></i>Edit About Section</h4>
        <p class="text-muted mb-0">Manage the homepage about section content</p>
    </div>
    <a href="/admin/homepage" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-1"></i>Back to Homepage
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

<form method="POST" action="/admin/homepage/about" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">About Section Content</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="title" class="form-label">Title *</label>
                        <input type="text" class="form-control" id="title" name="title"
                               value="<?php echo htmlspecialchars($about['title'] ?? ''); ?>" required>
                        <div class="form-text">The main heading for the about section</div>
                    </div>

                    <div class="mb-3">
                        <label for="content" class="form-label">Content *</label>
                        <textarea class="form-control" id="content" name="content" rows="8" required><?php echo htmlspecialchars($about['content'] ?? ''); ?></textarea>
                        <div class="form-text">Detailed description of your school</div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                                   <?php echo ($about['is_active'] ?? 1) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="is_active">
                                Display this section on homepage
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">About Image</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Current Image</label>
                        <?php if (!empty($about['image_path'])): ?>
                            <div class="mb-3">
                                <img src="/uploads/<?php echo $about['image_path']; ?>" alt="About" class="img-fluid rounded" style="max-height: 200px; width: 100%; object-fit: cover;">
                            </div>
                        <?php else: ?>
                            <div class="bg-light rounded d-flex align-items-center justify-content-center mb-3" style="height: 150px;">
                                <i class="fas fa-image fa-3x text-muted"></i>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label for="image" class="form-label">Replace Image</label>
                        <input type="file" class="form-control" id="image" name="image" accept="image/*">
                        <div class="form-text">Upload a new image (JPG, PNG, GIF). Leave empty to keep current image.</div>
                    </div>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Recommended:</strong> Use high-quality images with a 16:9 aspect ratio for best display.
                    </div>
                </div>
            </div>

            <!-- Preview Card -->
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="mb-0">Live Preview</h6>
                </div>
                <div class="card-body">
                    <div class="border rounded p-3 bg-light">
                        <h5 id="preview-title"><?php echo htmlspecialchars($about['title'] ?? 'About Our School'); ?></h5>
                        <p id="preview-content" class="text-muted small">
                            <?php echo htmlspecialchars(substr($about['content'] ?? 'School description will appear here...', 0, 150)); ?>...
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Form Actions -->
    <div class="d-flex justify-content-between align-items-center mt-4">
        <a href="/admin/homepage" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i>Back to Homepage
        </a>
        <div>
            <button type="reset" class="btn btn-outline-secondary me-2">
                <i class="fas fa-undo me-1"></i>Reset
            </button>
            <button type="submit" class="btn btn-success btn-lg">
                <i class="fas fa-save me-2"></i>Save About Section
            </button>
        </div>
    </div>
</form>

<script>
// Live preview functionality
document.addEventListener('DOMContentLoaded', function() {
    const titleInput = document.getElementById('title');
    const contentInput = document.getElementById('content');
    const previewTitle = document.getElementById('preview-title');
    const previewContent = document.getElementById('preview-content');

    function updatePreview() {
        previewTitle.textContent = titleInput.value || 'About Our School';
        const content = contentInput.value || 'School description will appear here...';
        previewContent.textContent = content.length > 150 ? content.substring(0, 150) + '...' : content;
    }

    titleInput.addEventListener('input', updatePreview);
    contentInput.addEventListener('input', updatePreview);

    // Image preview
    document.getElementById('image').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            // You could add image preview here
            console.log('Image selected:', file.name);
        }
    });
});
</script>

<?php
$content = ob_get_clean();
include dirname(__DIR__) . '/layout.php';
?>