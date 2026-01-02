<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'SuperAdmin Panel'; ?> - School Management System</title>
    <link href="/assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --sidebar-bg: linear-gradient(180deg, #8e44ad 0%, #9b59b6 100%);
            --sidebar-hover: rgba(255,255,255,0.1);
            --sidebar-active: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            --navbar-bg: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            --card-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            --border-radius: 0.5rem;
            --primary: #8e44ad;
            --secondary: #9b59b6;
        }

        .sidebar {
            width: 280px;
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
            border-bottom: 1px solid rgba(255,255,255,0.1);
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
            background: #e74c3c;
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
            box-shadow: 0 4px 15px rgba(231, 76, 60, 0.3);
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
            border-top: 1px solid rgba(255,255,255,0.1);
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
            border: 2px solid #9b59b6;
            border-radius: 50%;
        }

        .main-content {
            margin-left: 280px;
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

        .navbar {
            background: var(--navbar-bg);
            backdrop-filter: blur(10px);
        }

        .navbar-brand {
            color: #495057 !important;
            font-weight: 600;
        }

        .dropdown-menu {
            border: none;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            border-radius: 0.5rem;
        }

        .dropdown-item {
            padding: 0.75rem 1rem;
            border-radius: 0.25rem;
            margin: 0.125rem 0.25rem;
            transition: all 0.15s ease;
        }

        .dropdown-item:hover {
            background: rgba(142, 68, 173, 0.1);
            color: #8e44ad;
        }

        .dropdown-header {
            background: rgba(248, 249, 250, 0.8);
            border-bottom: 1px solid rgba(222, 226, 230, 0.8);
            font-weight: 600;
            color: #495057;
        }
    </style>
</head>
<body>
    <div class="content-overlay" id="contentOverlay" onclick="toggleSidebar()"></div>

    <nav class="sidebar d-flex flex-column" id="sidebar">
        <div class="sidebar-header p-4 border-bottom">
            <div class="d-flex align-items-center">
                <div class="sidebar-logo me-3">
                    <div class="bg-danger rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <i class="fas fa-crown text-white"></i>
                    </div>
                </div>
                <div class="sidebar-brand">
                    <h6 class="mb-0 fw-bold text-danger">Super Admin</h6>
                    <small class="text-muted">Management System</small>
                </div>
            </div>
        </div>

        <div class="sidebar-nav flex-grow-1 p-3">
            <ul class="nav flex-column">
                <li class="nav-item mb-2">
                    <a class="nav-link <?php echo $active_page === 'dashboard' ? 'active' : ''; ?>" href="/superadmin/dashboard">
                        <div class="d-flex align-items-center">
                            <div class="nav-icon me-3">
                                <i class="fas fa-tachometer-alt"></i>
                            </div>
                            <span>Dashboard</span>
                        </div>
                    </a>
                </li>

                <li class="nav-item mb-2">
                    <a class="nav-link <?php echo $active_page === 'admins' ? 'active' : ''; ?>" href="/superadmin/admins">
                        <div class="d-flex align-items-center">
                            <div class="nav-icon me-3">
                                <i class="fas fa-user-shield"></i>
                            </div>
                            <span>Manage Admins</span>
                        </div>
                    </a>
                </li>

                <li class="nav-item mb-2">
                    <a class="nav-link <?php echo $active_page === 'academic_years' ? 'active' : ''; ?>" href="/superadmin/academic-years">
                        <div class="d-flex align-items-center">
                            <div class="nav-icon me-3">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <span>Academic Years</span>
                        </div>
                    </a>
                </li>
            </ul>
        </div>

        <div class="sidebar-footer p-3 border-top">
            <div class="dropdown">
                <button class="btn btn-light w-100 d-flex align-items-center text-start" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="user-avatar me-3">
                        <div class="bg-danger rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                            <i class="fas fa-user text-white" style="font-size: 0.8rem;"></i>
                        </div>
                    </div>
                    <div class="user-info flex-grow-1">
                        <div class="fw-semibold small"><?php echo $_SESSION['user']['first_name'] ?? 'SuperAdmin'; ?></div>
                        <small class="text-muted" style="font-size: 0.7rem;">Super Administrator</small>
                    </div>
                    <i class="fas fa-chevron-down text-muted ms-2" style="font-size: 0.8rem;"></i>
                </button>
                <ul class="dropdown-menu w-100" aria-labelledby="userDropdown">
                    <li><a class="dropdown-item py-2" href="/superadmin/profile">
                        <i class="fas fa-user-edit me-2"></i>Profile
                    </a></li>
                    <li><a class="dropdown-item py-2" href="/superadmin/change-password">
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

    <div class="main-content">
        <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm">
            <div class="container-fluid px-4">
                <button class="btn btn-outline-secondary d-lg-none me-2" type="button" onclick="toggleSidebar()">
                    <i class="fas fa-bars"></i>
                </button>

                <div class="navbar-brand mb-0 h1 fw-bold text-danger">
                    <?php echo $page_title ?? 'Dashboard'; ?>
                </div>

                <div class="d-flex align-items-center">
                    <div class="dropdown">
                        <button class="btn btn-light d-flex align-items-center rounded-pill px-3 py-2" type="button" id="userMenuDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="bg-danger rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                                <i class="fas fa-user text-white" style="font-size: 0.8rem;"></i>
                            </div>
                            <div class="d-none d-lg-block text-start me-2">
                                <div class="fw-semibold" style="font-size: 0.85rem; line-height: 1;"><?php echo $_SESSION['user']['first_name'] ?? 'SuperAdmin'; ?></div>
                                <small class="text-muted" style="font-size: 0.7rem;"><?php echo date('M d, Y'); ?></small>
                            </div>
                            <i class="fas fa-chevron-down text-muted" style="font-size: 0.8rem;"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="userMenuDropdown">
                            <li><h6 class="dropdown-header">Welcome back!</h6></li>
                            <li><a class="dropdown-item" href="/superadmin/profile"><i class="fas fa-user-edit me-2"></i>Profile</a></li>
                            <li><a class="dropdown-item" href="/superadmin/change-password"><i class="fas fa-key me-2"></i>Change Password</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="/logout"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="container-fluid px-4 py-2 border-top bg-light">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 py-1">
                        <li class="breadcrumb-item">
                            <a href="/superadmin/dashboard"><i class="fas fa-home"></i> Dashboard</a>
                        </li>
                        <?php if ($active_page !== 'dashboard'): ?>
                            <li class="breadcrumb-item active" aria-current="page">
                                <?php
                                $page_names = [
                                    'admins' => 'Manage Admins',
                                    'academic_years' => 'Academic Years'
                                ];
                                echo $page_names[$active_page] ?? ucfirst(str_replace('_', ' ', $active_page));
                                ?>
                            </li>
                        <?php endif; ?>
                    </ol>
                </nav>
            </div>
        </nav>

        <main class="p-4">
            <?php echo $content; ?>
        </main>
    </div>

    <script src="/assets/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('contentOverlay');

            sidebar.classList.toggle('show');
            overlay.classList.toggle('show');
        }

        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('contentOverlay');
            const toggleBtn = event.target.closest('.navbar-toggler');

            if (!sidebar.contains(event.target) && !toggleBtn && window.innerWidth <= 768) {
                sidebar.classList.remove('show');
                overlay.classList.remove('show');
            }
        });

        window.addEventListener('resize', function() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('contentOverlay');

            if (window.innerWidth > 768) {
                sidebar.classList.remove('show');
                overlay.classList.remove('show');
            }
        });
    </script>
</body>
</html>