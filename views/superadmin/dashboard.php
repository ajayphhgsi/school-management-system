<?php
$active_page = 'dashboard';
$page_title = 'Dashboard';
ob_start();
?>

<style>
.stats-card {
    background: linear-gradient(135deg, #8e44ad 0%, #9b59b6 100%);
    border: none;
    border-radius: 0.5rem;
    color: white;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    position: relative;
    overflow: hidden;
}

.stats-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.stats-card::before {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    width: 100px;
    height: 100px;
    background: rgba(255,255,255,0.1);
    border-radius: 50%;
    transform: translate(30px, -30px);
}

.stats-card .card-body {
    position: relative;
    z-index: 2;
}

.stats-icon {
    opacity: 0.8;
    font-size: 2.5rem;
}

.quick-action-card {
    background: white;
    border: 1px solid #e9ecef;
    border-radius: 0.5rem;
    transition: all 0.3s ease;
    cursor: pointer;
    text-decoration: none;
    color: inherit;
}

.quick-action-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    text-decoration: none;
    color: inherit;
}

.quick-action-card .card-body {
    padding: 2rem;
    text-align: center;
}

.quick-action-icon {
    font-size: 3rem;
    margin-bottom: 1rem;
    opacity: 0.7;
}

.welcome-section {
    background: linear-gradient(135deg, #8e44ad 0%, #9b59b6 100%);
    border-radius: 0.5rem;
    color: white;
    padding: 2rem;
    margin-bottom: 2rem;
}
</style>

<!-- Welcome Section -->
<div class="welcome-section">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h2>Welcome back, <?php echo $_SESSION['user']['first_name'] ?? 'SuperAdmin'; ?>! 👑</h2>
            <p class="mb-0">Super Administrator Dashboard - Manage system-wide settings and administrators.</p>
        </div>
        <div class="col-md-4 text-end">
            <div class="d-flex align-items-center justify-content-end">
                <div class="me-3">
                    <small class="d-block opacity-75">Today's Date</small>
                    <strong><?php echo date('F j, Y'); ?></strong>
                </div>
                <i class="fas fa-crown fa-2x opacity-75"></i>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stats-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-1">Total Admins</h6>
                        <h2 class="mb-0"><?php echo number_format($stats['total_admins']); ?></h2>
                        <small class="opacity-75">Active Administrators</small>
                    </div>
                    <div class="stats-icon">
                        <i class="fas fa-user-shield"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stats-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-1">Super Admins</h6>
                        <h2 class="mb-0"><?php echo number_format($stats['total_superadmins']); ?></h2>
                        <small class="opacity-75">System Super Admins</small>
                    </div>
                    <div class="stats-icon">
                        <i class="fas fa-crown"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stats-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-1">Academic Years</h6>
                        <h2 class="mb-0"><?php echo number_format($stats['total_academic_years']); ?></h2>
                        <small class="opacity-75">Configured</small>
                    </div>
                    <div class="stats-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stats-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-1">Active Year</h6>
                        <h4 class="mb-0"><?php echo htmlspecialchars($stats['active_academic_year']); ?></h4>
                        <small class="opacity-75">Current Academic Year</small>
                    </div>
                    <div class="stats-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-bolt text-warning me-2"></i>Super Admin Actions</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-xl-3 col-lg-4 col-md-6">
                        <a href="/superadmin/admins/create" class="quick-action-card card h-100">
                            <div class="card-body">
                                <div class="quick-action-icon text-primary">
                                    <i class="fas fa-user-plus"></i>
                                </div>
                                <h6 class="card-title mb-0">Add Admin</h6>
                                <small class="text-muted">Create new administrator</small>
                            </div>
                        </a>
                    </div>
                    <div class="col-xl-3 col-lg-4 col-md-6">
                        <a href="/superadmin/admins" class="quick-action-card card h-100">
                            <div class="card-body">
                                <div class="quick-action-icon text-success">
                                    <i class="fas fa-users-cog"></i>
                                </div>
                                <h6 class="card-title mb-0">Manage Admins</h6>
                                <small class="text-muted">View & edit administrators</small>
                            </div>
                        </a>
                    </div>
                    <div class="col-xl-3 col-lg-4 col-md-6">
                        <a href="/superadmin/academic-years/create" class="quick-action-card card h-100">
                            <div class="card-body">
                                <div class="quick-action-icon text-info">
                                    <i class="fas fa-calendar-plus"></i>
                                </div>
                                <h6 class="card-title mb-0">Add Academic Year</h6>
                                <small class="text-muted">Create new academic year</small>
                            </div>
                        </a>
                    </div>
                    <div class="col-xl-3 col-lg-4 col-md-6">
                        <a href="/superadmin/academic-years" class="quick-action-card card h-100">
                            <div class="card-body">
                                <div class="quick-action-icon text-warning">
                                    <i class="fas fa-calendar-alt"></i>
                                </div>
                                <h6 class="card-title mb-0">Manage Years</h6>
                                <small class="text-muted">Configure academic years</small>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include 'layout.php';
?>