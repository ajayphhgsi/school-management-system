<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Admin Panel'; ?> - School Management System</title>
    <link href="/assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            /* Modern Theme */
            --sidebar-bg: linear-gradient(180deg, #2c3e50 0%, #34495e 100%);
            --sidebar-hover: rgba(255,255,255,0.1);
            --sidebar-active: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            --navbar-bg: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            --card-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            --border-radius: 0.5rem;
            --primary: #667eea;
            --secondary: #764ba2;
        }

        /* Classic Theme */
        [data-theme="classic"] {
            --sidebar-bg: #2c3e50;
            --sidebar-hover: rgba(255,255,255,0.1);
            --sidebar-active: #3498db;
            --navbar-bg: #ffffff;
            --card-shadow: 0 2px 4px rgba(0,0,0,0.1);
            --border-radius: 0.25rem;
            --primary: #2c3e50;
            --secondary: #34495e;
        }

        /* Modern Sidebar Styles */
        .sidebar {
            width: 200px;
            min-height: 100vh;
            background: var(--sidebar-bg);
            color: white;
            position: fixed;
            left: 0;
            top: 0;
            z-index: 1000;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
        }

        .sidebar.collapsed {
            transform: translateX(-100%);
        }

        .sidebar-header {
            background: rgba(255,255,255,0.05);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255,255,255,0.1) !important;
        }

        .sidebar-logo {
            animation: logoPulse 2s ease-in-out infinite;
        }

        @keyframes logoPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .sidebar-brand h6 {
            color: #ecf0f1;
            margin-bottom: 2px;
        }

        .sidebar-brand small {
            color: rgba(255,255,255,0.7);
            font-size: 0.75rem;
        }

        .sidebar-nav {
            flex: 1;
            overflow-y: auto;
        }

        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 0.75rem 1rem;
            margin: 0.125rem 0.5rem;
            border-radius: 8px;
            transition: all 0.3s ease;
            text-decoration: none;
            position: relative;
            overflow: hidden;
        }

        .sidebar .nav-link::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 4px;
            background: #3498db;
            transform: scaleY(0);
            transition: transform 0.3s ease;
            border-radius: 0 4px 4px 0;
        }

        .sidebar .nav-link:hover {
            color: white;
            background: var(--sidebar-hover);
            transform: translateX(5px);
        }

        .sidebar .nav-link:hover::before {
            transform: scaleY(1);
        }

        .sidebar .nav-link.active {
            color: white;
            background: var(--sidebar-active);
            box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
        }

        .sidebar .nav-link.active::before {
            transform: scaleY(1);
            background: #ecf0f1;
        }

        .nav-icon {
            width: 20px;
            text-align: center;
            font-size: 1.1rem;
            opacity: 0.8;
        }

        .sidebar .nav-link.active .nav-icon {
            opacity: 1;
        }

        .sidebar-footer {
            background: rgba(255,255,255,0.05);
            border-top: 1px solid rgba(255,255,255,0.1) !important;
            backdrop-filter: blur(10px);
        }

        .sidebar .btn-light {
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.2);
            color: white;
        }

        .sidebar .btn-light:hover {
            background: rgba(255,255,255,0.2);
            border-color: rgba(255,255,255,0.3);
            color: white;
        }

        .sidebar .dropdown-menu {
            background: #34495e;
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
        }

        .sidebar .dropdown-item {
            color: rgba(255,255,255,0.8);
            padding: 0.5rem 1rem;
        }

        .sidebar .dropdown-item:hover {
            background: rgba(255,255,255,0.1);
            color: white;
        }

        .sidebar .dropdown-item.text-danger {
            color: #e74c3c !important;
        }

        .sidebar .dropdown-item.text-danger:hover {
            background: rgba(231, 76, 60, 0.1);
            color: #e74c3c !important;
        }

        .user-avatar {
            position: relative;
        }

        .user-avatar::after {
            content: '';
            position: absolute;
            bottom: 0;
            right: 0;
            width: 8px;
            height: 8px;
            background: #27ae60;
            border: 2px solid #34495e;
            border-radius: 50%;
        }
        .main-content {
            margin-left: 200px;
            min-height: 100vh;
            background: #f8f9fa;
        }
        .sidebar.collapsed {
            transform: translateX(-100%);
        }
        .content-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 999;
        }
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.show {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0;
            }
            .content-overlay.show {
                display: block;
            }
        }
        @media (min-width: 769px) {
            .sidebar {
                transform: translateX(0);
            }
        }

        /* Modern Navbar Styles */
        .navbar {
            background: var(--navbar-bg);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            position: relative;
            z-index: 1060;
        }

        .navbar-brand {
            color: #495057 !important;
            font-weight: 600;
        }

        .navbar .input-group-text {
            background: rgba(248, 249, 250, 0.8);
            border: 1px solid rgba(222, 226, 230, 0.8);
            color: #6c757d;
        }

        .navbar .form-control {
            background: rgba(248, 249, 250, 0.8);
            border: 1px solid rgba(222, 226, 230, 0.8);
            color: #495057;
        }

        .navbar .form-control:focus {
            background: white;
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15);
            color: #495057;
        }

        .navbar .btn-light {
            background: rgba(248, 249, 250, 0.8);
            border: 1px solid rgba(222, 226, 230, 0.8);
            color: #495057;
        }

        .navbar .btn-light:hover {
            background: rgba(222, 226, 230, 0.8);
            border-color: #adb5bd;
            color: #495057;
        }

        .dropdown-menu {
            border: none;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            border-radius: 0.5rem;
            z-index: 1050;
        }

        .dropdown-item {
            padding: 0.75rem 1rem;
            border-radius: 0.25rem;
            margin: 0.125rem 0.25rem;
            transition: all 0.15s ease;
        }

        .dropdown-item:hover {
            background: rgba(102, 126, 234, 0.1);
            color: #667eea;
        }

        .dropdown-header {
            background: rgba(248, 249, 250, 0.8);
            border-bottom: 1px solid rgba(222, 226, 230, 0.8);
            font-weight: 600;
            color: #495057;
        }

        /* Notification Badge Animation */
        .badge {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }

        /* Mobile Search */
        #mobileSearch .input-group {
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border-radius: 0.5rem;
            overflow: hidden;
        }

        /* Ultra Compact UI Styles */
        body {
            font-size: 0.8rem;
            line-height: 1.3;
        }

        .sidebar .nav-link {
            padding: 0.4rem 0.5rem;
            font-size: 0.75rem;
            margin: 0.1rem 0.25rem;
        }

        .sidebar-header {
            padding: 0.75rem !important;
        }

        .sidebar-nav {
            padding: 0.5rem 0.5rem;
        }

        .sidebar-footer {
            padding: 0.75rem 0.5rem;
        }

        .navbar {
            padding: 0.25rem 0.75rem;
        }

        .navbar-brand {
            font-size: 1.1rem;
        }

        main {
            padding: 0.75rem;
        }

        .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }

        .form-control, .form-select {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
            height: 2rem;
        }

        .card {
            margin-bottom: 0.75rem;
        }

        .card-body {
            padding: 0.75rem;
        }

        .table th, .table td {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }

        h1, h2, h3, h4, h5, h6 {
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
        }

        .dropdown-item {
            padding: 0.25rem 0.75rem;
            font-size: 0.75rem;
        }

        .nav-icon {
            width: 16px;
            font-size: 0.9rem;
        }

        .badge {
            font-size: 0.65rem;
            padding: 0.2rem 0.4rem;
        }
    </style>
</head>
<body>
    <!-- Content Overlay for Mobile -->
    <div class="content-overlay" id="contentOverlay" onclick="toggleSidebar()"></div>

    <!-- Modern Sidebar -->
    <nav class="sidebar d-flex flex-column" id="sidebar">
        <!-- Logo/Brand Section -->
        <div class="sidebar-header p-4 border-bottom">
            <div class="d-flex align-items-center">
                <div class="sidebar-logo me-3">
                    <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <i class="fas fa-school text-white"></i>
                    </div>
                </div>
                <div class="sidebar-brand">
                    <h6 class="mb-0 fw-bold text-primary">School Admin</h6>
                    <small class="text-muted">Management System</small>
                </div>
            </div>
        </div>

        <!-- Navigation Menu -->
        <div class="sidebar-nav flex-grow-1 p-3">
            <ul class="nav flex-column">
                <!-- Dashboard -->
                <li class="nav-item mb-2">
                    <a class="nav-link <?php echo $active_page === 'dashboard' ? 'active' : ''; ?>" href="/admin/dashboard">
                        <div class="d-flex align-items-center">
                            <div class="nav-icon me-3">
                                <i class="fas fa-tachometer-alt"></i>
                            </div>
                            <span>Dashboard</span>
                        </div>
                    </a>
                </li>

                <!-- Content Management -->
                <li class="nav-item mb-2">
                    <a class="nav-link <?php echo $active_page === 'homepage' ? 'active' : ''; ?>" href="/admin/homepage">
                        <div class="d-flex align-items-center">
                            <div class="nav-icon me-3">
                                <i class="fas fa-home"></i>
                            </div>
                            <span>Homepage</span>
                        </div>
                    </a>
                </li>

                <!-- Student Management -->
                <li class="nav-item mb-2">
                    <a class="nav-link <?php echo $active_page === 'students' ? 'active' : ''; ?>" href="/admin/students">
                        <div class="d-flex align-items-center">
                            <div class="nav-icon me-3">
                                <i class="fas fa-users"></i>
                            </div>
                            <span>Students</span>
                        </div>
                    </a>
                </li>

                <!-- Academic Management -->
                <li class="nav-item mb-2">
                    <a class="nav-link <?php echo $active_page === 'classes' ? 'active' : ''; ?>" href="/admin/classes">
                        <div class="d-flex align-items-center">
                            <div class="nav-icon me-3">
                                <i class="fas fa-chalkboard-teacher"></i>
                            </div>
                            <span>Classes & Subjects</span>
                        </div>
                    </a>
                </li>

                <li class="nav-item mb-2">
                    <a class="nav-link <?php echo $active_page === 'attendance' ? 'active' : ''; ?>" href="/admin/attendance">
                        <div class="d-flex align-items-center">
                            <div class="nav-icon me-3">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                            <span>Attendance</span>
                        </div>
                    </a>
                </li>

                <li class="nav-item mb-2">
                    <a class="nav-link <?php echo $active_page === 'exams' ? 'active' : ''; ?>" href="/admin/exams">
                        <div class="d-flex align-items-center">
                            <div class="nav-icon me-3">
                                <i class="fas fa-file-alt"></i>
                            </div>
                            <span>Exams</span>
                        </div>
                    </a>
                </li>

                <!-- Financial Management -->
                <li class="nav-item mb-2">
                    <a class="nav-link <?php echo $active_page === 'fees' ? 'active' : ''; ?>" href="/admin/fees">
                        <div class="d-flex align-items-center">
                            <div class="nav-icon me-3">
                                <i class="fas fa-money-bill-wave"></i>
                            </div>
                            <span>Fees</span>
                        </div>
                    </a>
                </li>

                <li class="nav-item mb-2">
                    <a class="nav-link <?php echo $active_page === 'expenses' ? 'active' : ''; ?>" href="/admin/expenses">
                        <div class="d-flex align-items-center">
                            <div class="nav-icon me-3">
                                <i class="fas fa-credit-card"></i>
                            </div>
                            <span>Expenses</span>
                        </div>
                    </a>
                </li>

                <!-- Other Features -->
                <li class="nav-item mb-2">
                    <a class="nav-link <?php echo $active_page === 'events' ? 'active' : ''; ?>" href="/admin/events">
                        <div class="d-flex align-items-center">
                            <div class="nav-icon me-3">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <span>Events</span>
                        </div>
                    </a>
                </li>

                <li class="nav-item mb-2">
                    <a class="nav-link <?php echo $active_page === 'gallery' ? 'active' : ''; ?>" href="/admin/gallery">
                        <div class="d-flex align-items-center">
                            <div class="nav-icon me-3">
                                <i class="fas fa-images"></i>
                            </div>
                            <span>Gallery</span>
                        </div>
                    </a>
                </li>

                <li class="nav-item mb-2">
                    <a class="nav-link <?php echo $active_page === 'notifications' ? 'active' : ''; ?>" href="/admin/notifications">
                        <div class="d-flex align-items-center">
                            <div class="nav-icon me-3">
                                <i class="fas fa-bell"></i>
                            </div>
                            <span>Notifications</span>
                        </div>
                    </a>
                </li>

                <li class="nav-item mb-2">
                    <a class="nav-link <?php echo $active_page === 'certificates' ? 'active' : ''; ?>" href="/admin/certificates">
                        <div class="d-flex align-items-center">
                            <div class="nav-icon me-3">
                                <i class="fas fa-certificate"></i>
                            </div>
                            <span>Certificates</span>
                        </div>
                    </a>
                </li>

                <li class="nav-item mb-2">
                    <a class="nav-link <?php echo $active_page === 'reports' ? 'active' : ''; ?>" href="/admin/reports">
                        <div class="d-flex align-items-center">
                            <div class="nav-icon me-3">
                                <i class="fas fa-chart-bar"></i>
                            </div>
                            <span>Reports</span>
                        </div>
                    </a>
                </li>

                <!-- Settings -->
                <li class="nav-item mb-2">
                    <a class="nav-link <?php echo $active_page === 'settings' ? 'active' : ''; ?>" href="/admin/settings">
                        <div class="d-flex align-items-center">
                            <div class="nav-icon me-3">
                                <i class="fas fa-cog"></i>
                            </div>
                            <span>Settings</span>
                        </div>
                    </a>
                </li>
            </ul>
        </div>

        <!-- User Section -->
        <div class="sidebar-footer p-3 border-top">
            <div class="dropdown">
                <button class="btn btn-light w-100 d-flex align-items-center text-start" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="user-avatar me-3">
                        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                            <i class="fas fa-user text-white" style="font-size: 0.8rem;"></i>
                        </div>
                    </div>
                    <div class="user-info flex-grow-1">
                        <div class="fw-semibold small"><?php echo $_SESSION['user']['first_name'] ?? 'Admin'; ?></div>
                        <small class="text-muted" style="font-size: 0.7rem;">Administrator</small>
                    </div>
                    <i class="fas fa-chevron-down text-muted ms-2" style="font-size: 0.8rem;"></i>
                </button>
                <ul class="dropdown-menu w-100" aria-labelledby="userDropdown">
                    <li><a class="dropdown-item py-2" href="/admin/profile">
                        <i class="fas fa-user-edit me-2"></i>Profile
                    </a></li>
                    <li><a class="dropdown-item py-2" href="/admin/change-password">
                        <i class="fas fa-key me-2"></i>Change Password
                    </a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item py-2 text-danger" href="/logout">
                        <i class="fas fa-sign-out-alt me-2"></i>Logout
                    </a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Modern Header -->
        <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm">
            <div class="container-fluid px-4">
                <!-- Mobile Sidebar Toggle -->
                <button class="btn btn-outline-secondary d-lg-none me-2" type="button" onclick="toggleSidebar()">
                    <i class="fas fa-bars"></i>
                </button>

                <!-- Page Title -->
                <div class="navbar-brand mb-0 h1 fw-bold text-primary">
                    <?php echo $page_title ?? 'Dashboard'; ?>
                </div>

                <!-- Quick Search (Desktop) -->
                <div class="d-none d-lg-flex flex-grow-1 justify-content-center">
                    <div class="input-group" style="max-width: 400px;">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text" class="form-control border-start-0 bg-light" placeholder="Search students, classes, exams..." id="quickSearch">
                        <button class="btn btn-outline-secondary border-start-0" type="button">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>

                <!-- Right Side Items -->
                <div class="d-flex align-items-center">
                    <!-- Theme Toggle -->
                    <button class="btn btn-outline-secondary me-2" id="themeToggle" title="Toggle Theme">
                        <i class="fas fa-palette"></i>
                    </button>

                    <!-- Quick Search (Mobile) -->
                    <div class="d-lg-none me-2">
                        <button class="btn btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#mobileSearch" aria-expanded="false">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>

                    <!-- Notifications -->
                    <div class="dropdown me-3">
                        <button class="btn btn-light position-relative rounded-pill" type="button" id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-bell text-muted"></i>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem;">3</span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="notificationDropdown" style="width: 320px; max-height: 400px; overflow-y: auto;">
                            <div class="dropdown-header d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">Notifications</h6>
                                <span class="badge bg-primary rounded-pill">3</span>
                            </div>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item py-3" href="#">
                                <div class="d-flex align-items-start">
                                    <div class="flex-shrink-0 me-3">
                                        <div class="bg-success bg-opacity-10 rounded-circle p-2">
                                            <i class="fas fa-user-plus text-success"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="fw-semibold">New student registered</div>
                                        <small class="text-muted">John Doe joined Class 10A</small>
                                        <div class="text-muted mt-1" style="font-size: 0.75rem;">2 minutes ago</div>
                                    </div>
                                </div>
                            </a>
                            <a class="dropdown-item py-3" href="#">
                                <div class="d-flex align-items-start">
                                    <div class="flex-shrink-0 me-3">
                                        <div class="bg-info bg-opacity-10 rounded-circle p-2">
                                            <i class="fas fa-calendar-check text-info"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="fw-semibold">Attendance marked</div>
                                        <small class="text-muted">Class 10A attendance completed</small>
                                        <div class="text-muted mt-1" style="font-size: 0.75rem;">1 hour ago</div>
                                    </div>
                                </div>
                            </a>
                            <a class="dropdown-item py-3" href="#">
                                <div class="d-flex align-items-start">
                                    <div class="flex-shrink-0 me-3">
                                        <div class="bg-warning bg-opacity-10 rounded-circle p-2">
                                            <i class="fas fa-exclamation-triangle text-warning"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="fw-semibold">Fee payment overdue</div>
                                        <small class="text-muted">3 students have pending fees</small>
                                        <div class="text-muted mt-1" style="font-size: 0.75rem;">3 hours ago</div>
                                    </div>
                                </div>
                            </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item text-center text-primary fw-semibold" href="/admin/notifications/view">
                                View All Notifications
                            </a>
                        </div>
                    </div>

                    <!-- User Menu -->
                    <div class="dropdown">
                        <button class="btn btn-light d-flex align-items-center rounded-pill px-3 py-2" type="button" id="userMenuDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                                <i class="fas fa-user text-white" style="font-size: 0.8rem;"></i>
                            </div>
                            <div class="d-none d-lg-block text-start me-2">
                                <div class="fw-semibold" style="font-size: 0.85rem; line-height: 1;"><?php echo $_SESSION['user']['first_name'] ?? 'Admin'; ?></div>
                                <small class="text-muted" style="font-size: 0.7rem;"><?php echo date('M d, Y'); ?></small>
                            </div>
                            <i class="fas fa-chevron-down text-muted" style="font-size: 0.8rem;"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="userMenuDropdown">
                            <li><h6 class="dropdown-header">Welcome back!</h6></li>
                            <li><a class="dropdown-item" href="/admin/profile"><i class="fas fa-user-edit me-2"></i>Profile</a></li>
                            <li><a class="dropdown-item" href="/admin/change-password"><i class="fas fa-key me-2"></i>Change Password</a></li>
                            <li><a class="dropdown-item" href="/admin/settings"><i class="fas fa-cog me-2"></i>Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="/logout"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Mobile Search Collapse -->
            <div class="collapse" id="mobileSearch">
                <div class="container-fluid px-4 pb-3">
                    <div class="input-group">
                        <span class="input-group-text bg-light">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text" class="form-control" placeholder="Search..." id="mobileQuickSearch">
                        <button class="btn btn-outline-secondary" type="button">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </div>

        </nav>

        <!-- Page Content -->
        <main class="p-2">
            <?php echo $content; ?>
        </main>
    </div>

    <script src="/assets/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('contentOverlay');

            sidebar.classList.toggle('show');
            overlay.classList.toggle('show');
        }

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('contentOverlay');
            const toggleBtn = event.target.closest('.navbar-toggler');

            if (!sidebar.contains(event.target) && !toggleBtn && window.innerWidth <= 768) {
                sidebar.classList.remove('show');
                overlay.classList.remove('show');
            }
        });

        // Handle window resize
        window.addEventListener('resize', function() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('contentOverlay');

            if (window.innerWidth > 768) {
                sidebar.classList.remove('show');
                overlay.classList.remove('show');
            }
        });

        // Quick search functionality
        function setupSearch(inputId) {
            const searchInput = document.getElementById(inputId);
            if (searchInput) {
                searchInput.addEventListener('input', function(e) {
                    const query = e.target.value.toLowerCase().trim();
                    if (query.length > 1) {
                        // Simple search suggestions (can be enhanced with actual search)
                        const suggestions = [
                            { text: 'Students', url: '/admin/students', icon: 'fas fa-users' },
                            { text: 'Classes', url: '/admin/classes', icon: 'fas fa-chalkboard' },
                            { text: 'Attendance', url: '/admin/attendance', icon: 'fas fa-calendar-check' },
                            { text: 'Exams', url: '/admin/exams', icon: 'fas fa-file-alt' },
                            { text: 'Fees', url: '/admin/fees', icon: 'fas fa-money-bill-wave' },
                            { text: 'Events', url: '/admin/events', icon: 'fas fa-calendar' },
                            { text: 'Gallery', url: '/admin/gallery', icon: 'fas fa-images' }
                        ].filter(item => item.text.toLowerCase().includes(query));

                        // For now, just log suggestions (can be enhanced to show actual search results)
                        console.log('Search suggestions:', suggestions);
                    }
                });

                searchInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        const query = e.target.value.toLowerCase().trim();
                        if (query) {
                            // Redirect to search results or first matching page
                            const searchMap = {
                                'students': '/admin/students',
                                'classes': '/admin/classes',
                                'attendance': '/admin/attendance',
                                'exams': '/admin/exams',
                                'fees': '/admin/fees',
                                'events': '/admin/events',
                                'gallery': '/admin/gallery'
                            };

                            for (const [key, url] of Object.entries(searchMap)) {
                                if (query.includes(key)) {
                                    window.location.href = url;
                                    return;
                                }
                            }

                            // Default to students if no match
                            window.location.href = '/admin/students';
                        }
                    }
                });
            }
        }

        // Setup search for both desktop and mobile
        setupSearch('quickSearch');
        setupSearch('mobileQuickSearch');

        // Theme switching functionality
        const themeToggle = document.getElementById('themeToggle');
        const html = document.documentElement;

        // Load saved theme
        const savedTheme = localStorage.getItem('theme') || 'modern';
        html.setAttribute('data-theme', savedTheme);

        // Update toggle icon based on theme
        function updateToggleIcon() {
            const icon = themeToggle.querySelector('i');
            if (html.getAttribute('data-theme') === 'classic') {
                icon.className = 'fas fa-sun';
            } else {
                icon.className = 'fas fa-moon';
            }
        }

        updateToggleIcon();

        // Toggle theme on button click
        themeToggle.addEventListener('click', () => {
            const currentTheme = html.getAttribute('data-theme');
            const newTheme = currentTheme === 'classic' ? 'modern' : 'classic';

            html.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateToggleIcon();
        });
    </script>
</body>
</html>