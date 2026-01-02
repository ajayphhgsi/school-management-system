<?php
$active_page = 'homepage';
$page_title = 'Homepage Management';
ob_start();
?>

<style>
:root {
    --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    --info-gradient: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
    --warning-gradient: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
    --secondary-gradient: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
    --dark-gradient: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
    --card-shadow: 0 10px 30px rgba(0,0,0,0.08);
    --card-shadow-hover: 0 20px 40px rgba(0,0,0,0.15);
    --border-radius: 20px;
    --transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
}

body {
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    min-height: 100vh;
}

.content-card {
    border: none;
    border-radius: var(--border-radius);
    box-shadow: var(--card-shadow);
    transition: var(--transition);
    overflow: hidden;
    position: relative;
    background: white;
}

.content-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: var(--primary-gradient);
    border-radius: var(--border-radius) var(--border-radius) 0 0;
}

.content-card:hover {
    transform: translateY(-8px) scale(1.02);
    box-shadow: var(--card-shadow-hover);
}

.section-icon {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.8rem;
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    transition: var(--transition);
}

.section-icon:hover {
    transform: scale(1.1) rotate(5deg);
}

.carousel-preview {
    max-height: 200px;
    object-fit: cover;
    border-radius: 12px;
    transition: var(--transition);
}

.carousel-preview:hover {
    transform: scale(1.05);
}

.content-preview {
    max-height: 100px;
    overflow: hidden;
    text-overflow: ellipsis;
    line-height: 1.6;
}

/* Statistics Overview */
.stats-overview {
    background: white;
    border-radius: var(--border-radius);
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: var(--card-shadow);
}

.stat-item {
    text-align: center;
    padding: 1rem;
    border-radius: 15px;
    transition: var(--transition);
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
}

.stat-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
}

.stat-number {
    font-size: 2.5rem;
    font-weight: 800;
    background: var(--primary-gradient);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 0.5rem;
}

.stat-label {
    font-size: 0.9rem;
    color: #6c757d;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Enhanced Card Headers */
.card-header {
    border: none;
    border-radius: var(--border-radius) var(--border-radius) 0 0 !important;
    padding: 1.5rem;
}

.card-header.bg-primary { background: var(--primary-gradient) !important; }
.card-header.bg-success { background: var(--success-gradient) !important; }
.card-header.bg-info { background: var(--info-gradient) !important; }
.card-header.bg-warning { background: var(--warning-gradient) !important; }
.card-header.bg-secondary { background: var(--secondary-gradient) !important; }
.card-header.bg-dark { background: var(--dark-gradient) !important; }

/* Progress Indicators */
.content-status {
    position: absolute;
    top: 10px;
    right: 10px;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
}

.content-status.has-content {
    background: #28a745;
    color: white;
}

.content-status.empty {
    background: #dc3545;
    color: white;
}

/* Quick Actions Enhancement */
.quick-action-card {
    background: white;
    border: none;
    border-radius: var(--border-radius);
    padding: 2rem 1.5rem;
    text-align: center;
    transition: var(--transition);
    box-shadow: var(--card-shadow);
    text-decoration: none;
    color: inherit;
    display: block;
    height: 100%;
}

.quick-action-card:hover {
    transform: translateY(-10px) scale(1.05);
    box-shadow: var(--card-shadow-hover);
    text-decoration: none;
    color: inherit;
}

.quick-action-icon {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    margin: 0 auto 1rem;
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    transition: var(--transition);
}

.quick-action-card:hover .quick-action-icon {
    transform: scale(1.1) rotate(5deg);
}

/* Animations */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.fade-in-up {
    animation: fadeInUp 0.6s ease-out;
}

/* Enhanced buttons */
.btn-enhanced {
    position: relative;
    overflow: hidden;
    z-index: 1;
}

.btn-enhanced::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    transition: left 0.5s;
    z-index: -1;
}

.btn-enhanced:hover::before {
    left: 100%;
}

/* Page Header Enhancement */
.page-header {
    background: white;
    border-radius: var(--border-radius);
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: var(--card-shadow);
    border-left: 5px solid #667eea;
}

.page-title {
    font-size: 2rem;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 0.5rem;
}

.page-subtitle {
    color: #6c757d;
    font-size: 1.1rem;
    margin-bottom: 0;
}

/* Responsive enhancements */
@media (max-width: 768px) {
    .stats-overview {
        padding: 1rem;
    }

    .stat-number {
        font-size: 2rem;
    }

    .section-icon {
        width: 60px;
        height: 60px;
        font-size: 1.5rem;
    }

    .quick-action-card {
        padding: 1.5rem 1rem;
    }

    .quick-action-icon {
        width: 60px;
        height: 60px;
        font-size: 1.5rem;
    }
}

/* Loading skeleton */
.skeleton {
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: loading 1.5s infinite;
    border-radius: 8px;
}

@keyframes loading {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}

.skeleton-text {
    height: 1.2em;
    margin-bottom: 0.5em;
}

.skeleton-image {
    height: 200px;
    width: 100%;
}
</style>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><i class="fas fa-home text-primary me-2"></i>Homepage Management</h4>
        <p class="text-muted mb-0">Manage homepage content and sections</p>
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

<!-- Homepage Sections -->
<div class="row">
    <!-- Carousel Management -->
    <div class="col-lg-6 mb-4">
        <div class="card content-card h-100">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <div class="section-icon bg-white text-primary me-3">
                        <i class="fas fa-images"></i>
                    </div>
                    <div>
                        <h5 class="mb-0">Image Carousel</h5>
                        <small>Manage homepage slider images</small>
                    </div>
                </div>
                <a href="/admin/homepage/carousel" class="btn btn-light btn-sm">
                    <i class="fas fa-edit me-1"></i>Manage
                </a>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php if (!empty($carousel)): ?>
                        <?php foreach (array_slice($carousel, 0, 3) as $slide): ?>
                            <div class="col-md-4 mb-3">
                                <img src="/uploads/<?php echo $slide['image_path']; ?>" alt="<?php echo $slide['title']; ?>" class="img-fluid carousel-preview">
                                <div class="mt-2">
                                    <h6 class="mb-1"><?php echo $slide['title']; ?></h6>
                                    <p class="text-muted small mb-0 content-preview"><?php echo $slide['content']; ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12 text-center py-4">
                            <i class="fas fa-images fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No carousel images added yet</p>
                        </div>
                    <?php endif; ?>
                </div>
                <?php if (!empty($carousel) && count($carousel) > 3): ?>
                    <div class="text-center mt-3">
                        <small class="text-muted">And <?php echo count($carousel) - 3; ?> more images...</small>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- About Section -->
    <div class="col-lg-6 mb-4">
        <div class="card content-card h-100">
            <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <div class="section-icon bg-white text-success me-3">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <div>
                        <h5 class="mb-0">About Section</h5>
                        <small>School information and description</small>
                    </div>
                </div>
                <a href="/admin/homepage/about" class="btn btn-light btn-sm">
                    <i class="fas fa-edit me-1"></i>Edit
                </a>
            </div>
            <div class="card-body">
                <?php if ($about): ?>
                    <div class="row">
                        <div class="col-md-4">
                            <?php if ($about['image_path']): ?>
                                <img src="/uploads/<?php echo $about['image_path']; ?>" alt="About" class="img-fluid rounded">
                            <?php else: ?>
                                <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 120px;">
                                    <i class="fas fa-image fa-2x text-muted"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-8">
                            <h6><?php echo $about['title']; ?></h6>
                            <p class="text-muted small content-preview"><?php echo $about['content']; ?></p>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-info-circle fa-3x text-muted mb-3"></i>
                        <p class="text-muted">About section not configured</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Courses Section -->
    <div class="col-lg-6 mb-4">
        <div class="card content-card h-100">
            <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <div class="section-icon bg-white text-info me-3">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <div>
                        <h5 class="mb-0">Courses</h5>
                        <small>Academic programs and courses</small>
                    </div>
                </div>
                <a href="/admin/homepage/courses" class="btn btn-light btn-sm">
                    <i class="fas fa-edit me-1"></i>Manage
                </a>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php if (!empty($courses)): ?>
                        <?php foreach (array_slice($courses, 0, 2) as $course): ?>
                            <div class="col-md-6 mb-3">
                                <div class="d-flex">
                                    <?php if ($course['image_path']): ?>
                                        <img src="/uploads/<?php echo $course['image_path']; ?>" alt="<?php echo $course['title']; ?>" class="rounded me-3" style="width: 60px; height: 60px; object-fit: cover;">
                                    <?php endif; ?>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1"><?php echo $course['title']; ?></h6>
                                        <p class="text-muted small mb-0 content-preview"><?php echo $course['content']; ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12 text-center py-3">
                            <i class="fas fa-graduation-cap fa-2x text-muted mb-2"></i>
                            <p class="text-muted small">No courses added yet</p>
                        </div>
                    <?php endif; ?>
                </div>
                <?php if (!empty($courses) && count($courses) > 2): ?>
                    <div class="text-center mt-2">
                        <small class="text-muted">And <?php echo count($courses) - 2; ?> more courses...</small>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Events Section -->
    <div class="col-lg-6 mb-4">
        <div class="card content-card h-100">
            <div class="card-header bg-warning text-white d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <div class="section-icon bg-white text-warning me-3">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div>
                        <h5 class="mb-0">Events</h5>
                        <small>School events and announcements</small>
                    </div>
                </div>
                <a href="/admin/events" class="btn btn-light btn-sm">
                    <i class="fas fa-edit me-1"></i>Manage
                </a>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php if (!empty($events)): ?>
                        <?php foreach (array_slice($events, 0, 2) as $event): ?>
                            <div class="col-md-6 mb-3">
                                <div class="border-start border-primary border-4 ps-3">
                                    <h6 class="mb-1"><?php echo $event['title']; ?></h6>
                                    <p class="text-muted small mb-1"><?php echo date('M d, Y', strtotime($event['event_date'])); ?> at <?php echo $event['event_time']; ?></p>
                                    <p class="text-muted small mb-0 content-preview"><?php echo $event['description']; ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12 text-center py-3">
                            <i class="fas fa-calendar-alt fa-2x text-muted mb-2"></i>
                            <p class="text-muted small">No events scheduled</p>
                        </div>
                    <?php endif; ?>
                </div>
                <?php if (!empty($events) && count($events) > 2): ?>
                    <div class="text-center mt-2">
                        <small class="text-muted">And <?php echo count($events) - 2; ?> more events...</small>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Gallery Section -->
    <div class="col-lg-6 mb-4">
        <div class="card content-card h-100">
            <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <div class="section-icon bg-white text-secondary me-3">
                        <i class="fas fa-images"></i>
                    </div>
                    <div>
                        <h5 class="mb-0">Gallery</h5>
                        <small>Photo gallery and media</small>
                    </div>
                </div>
                <a href="/admin/gallery" class="btn btn-light btn-sm">
                    <i class="fas fa-edit me-1"></i>Manage
                </a>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php if (!empty($gallery)): ?>
                        <?php foreach (array_slice($gallery, 0, 4) as $item): ?>
                            <div class="col-3 mb-2">
                                <?php if ($item['image_path']): ?>
                                    <img src="/uploads/<?php echo $item['image_path']; ?>" alt="<?php echo $item['title']; ?>" class="img-fluid rounded" style="height: 60px; object-fit: cover;">
                                <?php else: ?>
                                    <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 60px;">
                                        <i class="fas fa-image text-muted"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12 text-center py-3">
                            <i class="fas fa-images fa-2x text-muted mb-2"></i>
                            <p class="text-muted small">No gallery items</p>
                        </div>
                    <?php endif; ?>
                </div>
                <?php if (!empty($gallery) && count($gallery) > 4): ?>
                    <div class="text-center mt-2">
                        <small class="text-muted">And <?php echo count($gallery) - 4; ?> more photos...</small>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Testimonials & Contact -->
    <div class="col-lg-6 mb-4">
        <div class="card content-card h-100">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <div class="section-icon bg-white text-dark me-3">
                        <i class="fas fa-comments"></i>
                    </div>
                    <div>
                        <h5 class="mb-0">Testimonials & Contact</h5>
                        <small>Student testimonials and contact info</small>
                    </div>
                </div>
                <div>
                    <a href="/admin/homepage/testimonials" class="btn btn-light btn-sm me-1">
                        <i class="fas fa-comments me-1"></i>Testimonials
                    </a>
                    <a href="/admin/homepage/contact" class="btn btn-light btn-sm">
                        <i class="fas fa-address-book me-1"></i>Contact
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Testimonials</h6>
                        <?php if (!empty($testimonials)): ?>
                            <div class="mb-2">
                                <small class="text-muted"><?php echo count($testimonials); ?> testimonials added</small>
                            </div>
                            <div class="text-muted small content-preview">
                                "<?php echo substr($testimonials[0]['content'], 0, 80); ?>..."
                            </div>
                        <?php else: ?>
                            <div class="text-muted small">No testimonials added</div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <h6>Contact Information</h6>
                        <?php if ($contact): ?>
                            <div class="text-muted small">
                                <div><i class="fas fa-map-marker-alt me-1"></i><?php echo substr($contact['address'] ?? 'Not set', 0, 30); ?>...</div>
                                <div><i class="fas fa-phone me-1"></i><?php echo $contact['phone'] ?? 'Not set'; ?></div>
                                <div><i class="fas fa-envelope me-1"></i><?php echo $contact['email'] ?? 'Not set'; ?></div>
                            </div>
                        <?php else: ?>
                            <div class="text-muted small">Contact info not configured</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-2 col-sm-4">
                        <a href="/admin/homepage/carousel" class="btn btn-outline-primary w-100">
                            <i class="fas fa-images d-block mb-2 fa-2x"></i>
                            <small>Manage Carousel</small>
                        </a>
                    </div>
                    <div class="col-md-2 col-sm-4">
                        <a href="/admin/homepage/about" class="btn btn-outline-success w-100">
                            <i class="fas fa-info-circle d-block mb-2 fa-2x"></i>
                            <small>Edit About</small>
                        </a>
                    </div>
                    <div class="col-md-2 col-sm-4">
                        <a href="/admin/homepage/courses" class="btn btn-outline-info w-100">
                            <i class="fas fa-graduation-cap d-block mb-2 fa-2x"></i>
                            <small>Manage Courses</small>
                        </a>
                    </div>
                    <div class="col-md-2 col-sm-4">
                        <a href="/admin/events" class="btn btn-outline-warning w-100">
                            <i class="fas fa-calendar-alt d-block mb-2 fa-2x"></i>
                            <small>Manage Events</small>
                        </a>
                    </div>
                    <div class="col-md-2 col-sm-4">
                        <a href="/admin/gallery" class="btn btn-outline-secondary w-100">
                            <i class="fas fa-images d-block mb-2 fa-2x"></i>
                            <small>Manage Gallery</small>
                        </a>
                    </div>
                    <div class="col-md-2 col-sm-4">
                        <a href="/admin/homepage/testimonials" class="btn btn-outline-dark w-100">
                            <i class="fas fa-comments d-block mb-2 fa-2x"></i>
                            <small>Testimonials</small>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include dirname(__DIR__) . '/layout.php';
?>