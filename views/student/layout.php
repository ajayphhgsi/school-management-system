<?php
// Get student info for header
$student_info = null;
if (isset($_SESSION['user']['id'])) {
    require_once __DIR__ . '/../../core/Database.php';
    $db = new Database();
    $student_info = $db->selectOne("SELECT s.first_name, s.last_name, s.middle_name, c.class_name, c.section FROM students s LEFT JOIN classes c ON s.class_id = c.id WHERE s.id = ?", [$_SESSION['user']['id']]);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'Student Portal'; ?> - School Management System</title>
    <link href="/assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            /* Modern Theme */
            --sidebar-bg: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --sidebar-hover: rgba(255,255,255,0.1);
            --sidebar-active: rgba(255,255,255,0.2);
            --header-bg: white;
            --card-shadow: 0 2px 4px rgba(0,0,0,.1);
            --border: 1px solid #e9ecef;
        }

        /* Classic Theme */
        [data-theme="classic"] {
            --sidebar-bg: #2c3e50;
            --sidebar-hover: rgba(255,255,255,0.1);
            --sidebar-active: rgba(255,255,255,0.2);
            --header-bg: #f8f9fa;
            --card-shadow: 0 1px 3px rgba(0,0,0,.1);
            --border: 1px solid #dee2e6;
        }

        .sidebar {
            width: 180px;
            min-height: 100vh;
            background: var(--sidebar-bg);
            color: white;
            position: fixed;
            left: 0;
            top: 0;
            z-index: 1000;
            transition: transform 0.3s ease;
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,.75);
            padding: 0.75rem 1rem;
            border: none;
            border-radius: 0;
            text-align: left;
        }
        .sidebar .nav-link:hover {
            color: white;
            background: var(--sidebar-hover);
        }
        .sidebar .nav-link.active {
            color: white;
            background: var(--sidebar-active);
        }
        .main-content {
            margin-left: 180px;
            min-height: 100vh;
        }
        .header {
            background: var(--header-bg);
            box-shadow: var(--card-shadow);
            padding: 1rem;
            border-bottom: var(--border);
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

        /* Ultra Compact UI Styles */
        body {
            font-size: 0.8rem;
            line-height: 1.3;
        }

        .sidebar .nav-link {
            padding: 0.4rem 0.5rem;
            font-size: 0.75rem;
        }

        .sidebar {
            padding: 0.75rem 0.5rem;
        }

        .header {
            padding: 0.5rem;
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

        .breadcrumb {
            font-size: 0.75rem;
        }
    </style>
</head>
<body>
    <!-- Content Overlay for Mobile -->
    <div class="content-overlay" id="contentOverlay" onclick="toggleSidebar()"></div>

    <!-- Sidebar -->
    <nav class="sidebar d-flex flex-column p-3" id="sidebar">
        <div class="mb-4">
            <h5 class="text-center">
                <i class="fas fa-graduation-cap"></i><br>
                Student Portal
            </h5>
        </div>

        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo $active_page === 'dashboard' ? 'active' : ''; ?>" href="/student/dashboard">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $active_page === 'profile' ? 'active' : ''; ?>" href="/student/profile">
                    <i class="fas fa-user"></i> My Profile
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $active_page === 'attendance' ? 'active' : ''; ?>" href="/student/attendance">
                    <i class="fas fa-calendar-check"></i> Attendance
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $active_page === 'results' ? 'active' : ''; ?>" href="/student/results">
                    <i class="fas fa-chart-line"></i> Exam Results
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $active_page === 'fees' ? 'active' : ''; ?>" href="/student/fees">
                    <i class="fas fa-money-bill-wave"></i> Fees
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $active_page === 'events' ? 'active' : ''; ?>" href="/student/events">
                    <i class="fas fa-calendar"></i> Events
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $active_page === 'resources' ? 'active' : ''; ?>" href="/student/resources">
                    <i class="fas fa-book"></i> Resources
                </a>
            </li>
        </ul>

        <div class="mt-auto">
            <div class="dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                    <i class="fas fa-user"></i> <?php echo $student_info ? $student_info['first_name'] . ' ' . $student_info['last_name'] : 'Student'; ?>
                </a>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="/student/change-password"><i class="fas fa-key"></i> Change Password</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="/logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <header class="header">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div>
                    <button class="btn btn-outline-secondary d-md-none" type="button" onclick="toggleSidebar()">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h4 class="mb-0 d-inline ms-2"><?php echo $page_title ?? 'Dashboard'; ?></h4>
                </div>
                <div class="d-flex align-items-center">
                    <button class="btn btn-outline-secondary me-2" id="themeToggle" title="Toggle Theme">
                        <i class="fas fa-palette"></i>
                    </button>
                    <span class="text-muted">
                        Welcome, <?php echo $student_info ? $student_info['first_name'] . ' ' . ($student_info['middle_name'] ?? '') . ' ' . $student_info['last_name'] . ' (' . $student_info['class_name'] . ' ' . $student_info['section'] . ')' : 'Student'; ?> |
                        <?php echo date('M d, Y'); ?>
                    </span>
                </div>
            </div>

        </header>

        <!-- Page Content -->
        <main class="p-2">
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
    </script>
</body>
</html></content>
</xai:function_call"> 

<xai:function_call name="apply_diff">
<parameter name="path">views/student/dashboard.php