<?php
$active_page = 'homepage';
$page_title = 'Manage Carousel';
ob_start();
?>

<style>
.carousel-item {
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 15px;
    background: white;
}

.carousel-preview {
    max-height: 150px;
    object-fit: cover;
    border-radius: 4px;
    width: 100%;
}

.sort-handle {
    cursor: move;
    color: #6c757d;
}

.sort-handle:hover {
    color: #495057;
}
</style>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><i class="fas fa-images text-primary me-2"></i>Manage Homepage Carousel</h4>
        <p class="text-muted mb-0">Add, edit, and reorder carousel images</p>
    </div>
    <div>
        <a href="/admin/homepage" class="btn btn-secondary me-2">
            <i class="fas fa-arrow-left me-1"></i>Back to Homepage
        </a>
        <button type="submit" form="carouselForm" class="btn btn-primary">
            <i class="fas fa-save me-1"></i>Save Changes
        </button>
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

<form id="carouselForm" method="POST" action="/admin/homepage/carousel" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

    <!-- Existing Carousel Items -->
    <div id="carouselItems">
        <?php if (!empty($carousel)): ?>
            <?php foreach ($carousel as $index => $item): ?>
                <div class="carousel-item" data-id="<?php echo $item['id']; ?>">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Current Image</label>
                                <?php if ($item['image_path']): ?>
                                    <img src="/uploads/<?php echo $item['image_path']; ?>" alt="Carousel" class="carousel-preview">
                                <?php else: ?>
                                    <div class="bg-light d-flex align-items-center justify-content-center carousel-preview">
                                        <i class="fas fa-image fa-2x text-muted"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Replace Image</label>
                                <input type="file" class="form-control" name="carousel[<?php echo $item['id']; ?>][image]" accept="image/*">
                            </div>
                        </div>
                        <div class="col-md-7">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Title *</label>
                                    <input type="text" class="form-control" name="carousel[<?php echo $item['id']; ?>][title]" value="<?php echo htmlspecialchars($item['title']); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Sort Order</label>
                                    <input type="number" class="form-control" name="carousel[<?php echo $item['id']; ?>][sort_order]" value="<?php echo $item['sort_order']; ?>" min="0">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Content</label>
                                <textarea class="form-control" name="carousel[<?php echo $item['id']; ?>][content]" rows="3"><?php echo htmlspecialchars($item['content']); ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Link (Optional)</label>
                                <input type="url" class="form-control" name="carousel[<?php echo $item['id']; ?>][link]" value="<?php echo htmlspecialchars($item['link']); ?>" placeholder="https://example.com">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="d-flex flex-column h-100 justify-content-between">
                                <div>
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" name="carousel[<?php echo $item['id']; ?>][is_active]" value="1" <?php echo $item['is_active'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label">Active</label>
                                    </div>
                                    <div class="sort-handle mb-3">
                                        <i class="fas fa-grip-vertical me-2"></i>Drag to reorder
                                    </div>
                                </div>
                                <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeCarouselItem(<?php echo $item['id']; ?>)">
                                    <i class="fas fa-trash me-1"></i>Remove
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-images fa-4x text-muted mb-3"></i>
                <h5 class="text-muted">No carousel items yet</h5>
                <p class="text-muted">Add your first carousel image below</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Add New Carousel Item -->
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-plus text-success me-2"></i>Add New Carousel Item</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Image *</label>
                    <input type="file" class="form-control" name="new_image" accept="image/*" required>
                    <div class="form-text">Upload a high-quality image (JPG, PNG, GIF)</div>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Title *</label>
                    <input type="text" class="form-control" name="new_title" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Sort Order</label>
                    <input type="number" class="form-control" name="new_sort_order" value="0" min="0">
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Content</label>
                    <textarea class="form-control" name="new_content" rows="3" placeholder="Describe the carousel item..."></textarea>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Link (Optional)</label>
                    <input type="url" class="form-control" name="new_link" placeholder="https://example.com">
                    <div class="form-text">Link to redirect when clicked</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Form Actions -->
    <div class="d-flex justify-content-between align-items-center mt-4">
        <a href="/admin/homepage" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i>Back to Homepage
        </a>
        <button type="submit" class="btn btn-primary btn-lg">
            <i class="fas fa-save me-2"></i>Save All Changes
        </button>
    </div>
</form>

<script>
// Drag and drop reordering
document.addEventListener('DOMContentLoaded', function() {
    const carouselItems = document.getElementById('carouselItems');

    // Make items sortable (you would need SortableJS library for full functionality)
    // For now, we'll just update sort orders on form submission
});

function removeCarouselItem(itemId) {
    if (confirm('Are you sure you want to remove this carousel item? This action cannot be undone.')) {
        // In a real implementation, you might want to mark as deleted or actually delete
        // For now, we'll just hide it and let the backend handle it
        const item = document.querySelector(`[data-id="${itemId}"]`);
        if (item) {
            item.style.display = 'none';
            // Add a hidden input to mark for deletion
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = `carousel[${itemId}][delete]`;
            hiddenInput.value = '1';
            document.getElementById('carouselForm').appendChild(hiddenInput);
        }
    }
}

// Auto-update sort orders based on current order
function updateSortOrders() {
    const items = document.querySelectorAll('.carousel-item[style*="display: block"], .carousel-item:not([style*="display: none"])');
    items.forEach((item, index) => {
        const id = item.dataset.id;
        const sortInput = document.querySelector(`input[name="carousel[${id}][sort_order]"]`);
        if (sortInput) {
            sortInput.value = index;
        }
    });
}

// Update sort orders before form submission
document.getElementById('carouselForm').addEventListener('submit', function() {
    updateSortOrders();
});
</script>

<?php
$content = ob_get_clean();
include dirname(__DIR__) . '/layout.php';
?>