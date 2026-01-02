<?php
$active_page = 'gallery';
$page_title = 'Gallery Management';
ob_start();
?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><i class="fas fa-images text-primary me-2"></i>Gallery Management</h4>
        <p class="text-muted mb-0">Manage school photos and media</p>
    </div>
    <a href="/admin/gallery/upload" class="btn btn-primary btn-lg">
        <i class="fas fa-upload me-2"></i>Upload Photos
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

<!-- Gallery Grid -->
<div class="row">
    <?php if (!empty($gallery)): ?>
        <?php foreach ($gallery as $item): ?>
            <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                <div class="card h-100">
                    <div class="position-relative">
                        <?php if ($item['image_path']): ?>
                            <img src="/uploads/<?php echo $item['image_path']; ?>" class="card-img-top" alt="<?php echo $item['title']; ?>" style="height: 200px; object-fit: cover;">
                        <?php else: ?>
                            <div class="bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                <i class="fas fa-image fa-3x text-muted"></i>
                            </div>
                        <?php endif; ?>
                        <div class="position-absolute top-0 end-0 p-2">
                            <span class="badge <?php echo $item['is_active'] ? 'bg-success' : 'bg-secondary'; ?>">
                                <?php echo $item['is_active'] ? 'Active' : 'Inactive'; ?>
                            </span>
                        </div>
                    </div>
                    <div class="card-body">
                        <h6 class="card-title"><?php echo $item['title']; ?></h6>
                        <p class="card-text small text-muted"><?php echo $item['description']; ?></p>
                        <small class="text-muted d-block">
                            Uploaded: <?php echo date('M d, Y', strtotime($item['created_at'])); ?>
                        </small>
                        <div class="mt-3 d-flex justify-content-between">
                            <a href="/admin/gallery/edit/<?php echo $item['id']; ?>" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="/admin/gallery/delete/<?php echo $item['id']; ?>" class="btn btn-sm btn-outline-danger"
                               onclick="return confirm('Are you sure you want to delete this gallery item?')">
                                <i class="fas fa-trash"></i> Delete
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-images fa-4x text-muted mb-3"></i>
                    <h4 class="text-muted">No Gallery Items Found</h4>
                    <p class="text-muted">Start building your school gallery by uploading photos.</p>
                    <a href="/admin/gallery/upload" class="btn btn-primary btn-lg">
                        <i class="fas fa-upload me-2"></i>Upload Your First Photo
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
include dirname(__DIR__) . '/layout.php';
?>