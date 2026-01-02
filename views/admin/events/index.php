<?php
$active_page = 'events';
$page_title = 'Events Management';
ob_start();
?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><i class="fas fa-calendar text-primary me-2"></i>Events Management</h4>
        <p class="text-muted mb-0">Organize and manage school events</p>
    </div>
    <a href="/admin/events/create" class="btn btn-primary btn-lg">
        <i class="fas fa-plus me-2"></i>Create New Event
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

<!-- Events Grid -->
<div class="row">
    <?php if (!empty($events)): ?>
        <?php foreach ($events as $event): ?>
            <div class="col-lg-6 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h5 class="card-title"><?php echo $event['title']; ?></h5>
                                <p class="text-muted mb-2"><?php echo $event['description']; ?></p>
                            </div>
                            <span class="badge <?php echo $event['is_active'] ? 'bg-success' : 'bg-secondary'; ?>">
                                <?php echo $event['is_active'] ? 'Active' : 'Inactive'; ?>
                            </span>
                        </div>

                        <div class="row mb-3">
                            <div class="col-sm-6">
                                <small class="text-muted d-block">Date & Time</small>
                                <strong><?php echo date('M d, Y', strtotime($event['event_date'])); ?> at <?php echo $event['event_time']; ?></strong>
                            </div>
                            <div class="col-sm-6">
                                <small class="text-muted d-block">Location</small>
                                <strong><?php echo $event['location'] ?? 'TBA'; ?></strong>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                Created: <?php echo date('M d, Y', strtotime($event['created_at'])); ?>
                            </small>
                            <div class="btn-group" role="group">
                                <a href="/admin/events/edit/<?php echo $event['id']; ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="/admin/events/delete/<?php echo $event['id']; ?>" class="btn btn-sm btn-outline-danger"
                                   onclick="return confirm('Are you sure you want to delete this event?')">
                                    <i class="fas fa-trash"></i> Delete
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-calendar fa-4x text-muted mb-3"></i>
                    <h4 class="text-muted">No Events Found</h4>
                    <p class="text-muted">Start by creating your first school event.</p>
                    <a href="/admin/events/create" class="btn btn-primary btn-lg">
                        <i class="fas fa-plus me-2"></i>Create Your First Event
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