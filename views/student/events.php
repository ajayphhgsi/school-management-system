<?php
$active_page = 'events';
$page_title = 'School Events';
ob_start();
?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><i class="fas fa-calendar-alt text-info me-2"></i>School Events</h4>
        <p class="text-muted mb-0">Stay updated with upcoming school events and activities</p>
    </div>
</div>

<!-- Events List -->
<?php if (!empty($events)): ?>
    <div class="row">
        <?php foreach ($events as $event): ?>
            <div class="col-lg-6 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-start">
                            <div class="flex-shrink-0 me-3">
                                <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                    <i class="fas fa-calendar text-white"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="card-title mb-2"><?php echo $event['title']; ?></h5>
                                <div class="mb-2">
                                    <small class="text-muted">
                                        <i class="fas fa-calendar-day me-1"></i><?php echo date('l, M d, Y', strtotime($event['event_date'])); ?>
                                        <br>
                                        <i class="fas fa-clock me-1"></i><?php echo date('h:i A', strtotime($event['event_time'])); ?>
                                        <?php if ($event['venue']): ?>
                                            <br>
                                            <i class="fas fa-map-marker-alt me-1"></i><?php echo $event['venue']; ?>
                                        <?php endif; ?>
                                    </small>
                                </div>
                                <?php if ($event['description']): ?>
                                    <p class="card-text"><?php echo nl2br($event['description']); ?></p>
                                <?php endif; ?>
                                <?php if ($event['organizer']): ?>
                                    <small class="text-muted">
                                        <i class="fas fa-user me-1"></i>Organized by: <?php echo $event['organizer']; ?>
                                    </small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php if ($event['image_path']): ?>
                        <img src="/uploads/<?php echo $event['image_path']; ?>" class="card-img-bottom" alt="<?php echo $event['title']; ?>" style="height: 200px; object-fit: cover;">
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="fas fa-calendar-alt fa-4x text-muted mb-3"></i>
            <h4 class="text-muted">No Events Scheduled</h4>
            <p class="text-muted">There are no upcoming events at the moment. Check back later for updates.</p>
        </div>
    </div>
<?php endif; ?>

<?php
$content = ob_get_clean();
include 'layout.php';
?>