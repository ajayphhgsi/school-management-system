<?php
/**
 * School Management System - Installation Wizard
 * Version 1.0.0
 */

session_start();

// Define constants
define('BASE_PATH', __DIR__ . '/');
define('CONFIG_PATH', BASE_PATH . 'config/');
define('DATABASE_PATH', BASE_PATH . 'database/');

// Check if already installed
if (file_exists(CONFIG_PATH . 'installed.php')) {
    header('Location: /');
    exit;
}

// Handle installation steps
$step = $_GET['step'] ?? 1;
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($step) {
        case 1:
            // System requirements check
            $requirements = checkRequirements();
            if ($requirements['passed']) {
                header('Location: install.php?step=2');
                exit;
            }
            break;

        case 2:
            // Create database and tables
            if (isset($_POST['create_db'])) {
                if (createDatabase()) {
                    $success = 'Database and tables created successfully!';
                    header('Location: install.php?step=3');
                    exit;
                } else {
                    $errors[] = 'Failed to create database and tables.';
                }
            }
            break;

        case 3:
            // Administrator account setup
            $adminData = [
                'username' => $_POST['admin_username'] ?? '',
                'email' => $_POST['admin_email'] ?? '',
                'password' => $_POST['admin_password'] ?? '',
                'confirm_password' => $_POST['admin_confirm_password'] ?? '',
                'first_name' => $_POST['admin_first_name'] ?? '',
                'last_name' => $_POST['admin_last_name'] ?? ''
            ];

            $validationErrors = validateAdminData($adminData);
            if (empty($validationErrors)) {
                if (createAdminAccount($adminData)) {
                    // Mark as installed
                    markAsInstalled();
                    header('Location: install.php?step=4');
                    exit;
                } else {
                    $errors[] = 'Failed to create administrator account.';
                }
            } else {
                $errors = array_merge($errors, $validationErrors);
            }
            break;
    }
}

function checkRequirements() {
    $requirements = [
        'php_version' => version_compare(PHP_VERSION, '8.1.0', '>='),
        'pdo' => extension_loaded('pdo'),
        'pdo_sqlite' => extension_loaded('pdo_sqlite'),
        'mbstring' => extension_loaded('mbstring'),
        'curl' => extension_loaded('curl'),
        'json' => extension_loaded('json'),
        'session' => extension_loaded('session'),
        'openssl' => extension_loaded('openssl'),
        'gd' => extension_loaded('gd'), // Optional for image processing
        'zip' => extension_loaded('zip'), // Optional for file compression
        'writable_config' => is_writable(CONFIG_PATH),
        'writable_uploads' => is_writable(BASE_PATH . 'uploads/'),
        'writable_logs' => is_writable(BASE_PATH . 'logs/'),
        'writable_database' => is_writable(BASE_PATH . 'database/')
    ];

    // Core requirements (required for basic functionality)
    $coreRequirements = [
        'php_version', 'pdo', 'pdo_sqlite', 'mbstring', 'json', 'session',
        'writable_config', 'writable_uploads', 'writable_database'
    ];

    $corePassed = true;
    foreach ($coreRequirements as $req) {
        if (!$requirements[$req]) {
            $corePassed = false;
            break;
        }
    }

    $requirements['passed'] = $corePassed;

    return $requirements;
}

function createDatabase() {
    try {
        $dbPath = BASE_PATH . 'database/school_management.db';
        $dsn = "sqlite:{$dbPath}";
        $pdo = new PDO($dsn);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Import SQLite schema
        $schema = file_get_contents(DATABASE_PATH . 'schema_sqlite.sql');
        $pdo->exec($schema);

        return true;
    } catch (PDOException $e) {
        return false;
    }
}

function validateAdminData($data) {
    $errors = [];

    if (empty($data['username'])) {
        $errors[] = 'Username is required.';
    }

    if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Valid email is required.';
    }

    if (empty($data['password']) || strlen($data['password']) < 8) {
        $errors[] = 'Password must be at least 8 characters long.';
    }

    if ($data['password'] !== $data['confirm_password']) {
        $errors[] = 'Passwords do not match.';
    }

    if (empty($data['first_name'])) {
        $errors[] = 'First name is required.';
    }

    if (empty($data['last_name'])) {
        $errors[] = 'Last name is required.';
    }

    return $errors;
}

function createAdminAccount($data) {
    try {
        $dbPath = BASE_PATH . 'database/school_management.db';
        $dsn = "sqlite:{$dbPath}";
        $pdo = new PDO($dsn);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role, first_name, last_name) VALUES (?, ?, ?, 'admin', ?, ?)");
        $stmt->execute([
            $data['username'],
            $data['email'],
            $hashedPassword,
            $data['first_name'],
            $data['last_name']
        ]);

        return true;
    } catch (PDOException $e) {
        return false;
    }
}

function markAsInstalled() {
    $content = "<?php\n// Installation completed on " . date('Y-m-d H:i:s') . "\ndefine('INSTALLED', true);\n";
    file_put_contents(CONFIG_PATH . 'installed.php', $content);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Management System - Installation</title>
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .install-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 600px;
            width: 100%;
        }
        .install-header {
            background: #667eea;
            color: white;
            padding: 2rem;
            text-align: center;
        }
        .install-body {
            padding: 2rem;
        }
        .step-indicator {
            display: flex;
            justify-content: center;
            margin-bottom: 2rem;
        }
        .step {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e9ecef;
            color: #6c757d;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 0.5rem;
            font-weight: bold;
        }
        .step.active {
            background: #667eea;
            color: white;
        }
        .step.completed {
            background: #28a745;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="install-card">
                    <div class="install-header">
                        <h3>School Management System</h3>
                        <p>Installation Wizard</p>
                    </div>
                    <div class="install-body">
                        <!-- Step Indicator -->
                        <div class="step-indicator">
                            <div class="step <?php echo $step >= 1 ? 'active' : ''; ?> <?php echo $step > 1 ? 'completed' : ''; ?>">1</div>
                            <div class="step <?php echo $step >= 2 ? 'active' : ''; ?> <?php echo $step > 2 ? 'completed' : ''; ?>">2</div>
                            <div class="step <?php echo $step >= 3 ? 'active' : ''; ?> <?php echo $step > 3 ? 'completed' : ''; ?>">3</div>
                            <div class="step <?php echo $step >= 4 ? 'completed' : ''; ?>">4</div>
                        </div>

                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo $error; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($success)): ?>
                            <div class="alert alert-success">
                                <?php echo $success; ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($step == 1): ?>
                            <!-- Step 1: Requirements Check -->
                            <h4>Step 1: System Requirements</h4>
                            <p>Checking if your server meets the minimum requirements...</p>

                            <?php $requirements = checkRequirements(); ?>
                            <div class="table-responsive">
                               <table class="table table-striped">
                                   <thead>
                                       <tr>
                                           <th>Requirement</th>
                                           <th>Status</th>
                                           <th>Type</th>
                                           <th>Current</th>
                                       </tr>
                                   </thead>
                                   <tbody>
                                       <tr>
                                           <td>PHP Version 8.1+</td>
                                           <td><?php echo $requirements['php_version'] ? '<span class="text-success">✓</span>' : '<span class="text-danger">✗</span>'; ?></td>
                                           <td><span class="badge bg-danger">Required</span></td>
                                           <td><?php echo PHP_VERSION; ?></td>
                                       </tr>
                                       <tr>
                                           <td>PDO Extension</td>
                                           <td><?php echo $requirements['pdo'] ? '<span class="text-success">✓</span>' : '<span class="text-danger">✗</span>'; ?></td>
                                           <td><span class="badge bg-danger">Required</span></td>
                                           <td><?php echo $requirements['pdo'] ? 'Enabled' : 'Disabled'; ?></td>
                                       </tr>
                                       <tr>
                                           <td>SQLite PDO Driver</td>
                                           <td><?php echo $requirements['pdo_sqlite'] ? '<span class="text-success">✓</span>' : '<span class="text-danger">✗</span>'; ?></td>
                                           <td><span class="badge bg-danger">Required</span></td>
                                           <td><?php echo $requirements['pdo_sqlite'] ? 'Enabled' : 'Disabled'; ?></td>
                                       </tr>
                                       <tr>
                                           <td>MBString Extension</td>
                                           <td><?php echo $requirements['mbstring'] ? '<span class="text-success">✓</span>' : '<span class="text-danger">✗</span>'; ?></td>
                                           <td><span class="badge bg-danger">Required</span></td>
                                           <td><?php echo $requirements['mbstring'] ? 'Enabled' : 'Disabled'; ?></td>
                                       </tr>
                                       <tr>
                                           <td>JSON Extension</td>
                                           <td><?php echo $requirements['json'] ? '<span class="text-success">✓</span>' : '<span class="text-danger">✗</span>'; ?></td>
                                           <td><span class="badge bg-danger">Required</span></td>
                                           <td><?php echo $requirements['json'] ? 'Enabled' : 'Disabled'; ?></td>
                                       </tr>
                                       <tr>
                                           <td>Session Extension</td>
                                           <td><?php echo $requirements['session'] ? '<span class="text-success">✓</span>' : '<span class="text-danger">✗</span>'; ?></td>
                                           <td><span class="badge bg-danger">Required</span></td>
                                           <td><?php echo $requirements['session'] ? 'Enabled' : 'Disabled'; ?></td>
                                       </tr>
                                       <tr>
                                           <td>GD Extension (Image Processing)</td>
                                           <td><?php echo $requirements['gd'] ? '<span class="text-success">✓</span>' : '<span class="text-warning">⚠</span>'; ?></td>
                                           <td><span class="badge bg-warning">Optional</span></td>
                                           <td><?php echo $requirements['gd'] ? 'Enabled' : 'Disabled'; ?></td>
                                       </tr>
                                       <tr>
                                           <td>ZIP Extension (File Compression)</td>
                                           <td><?php echo $requirements['zip'] ? '<span class="text-success">✓</span>' : '<span class="text-warning">⚠</span>'; ?></td>
                                           <td><span class="badge bg-warning">Optional</span></td>
                                           <td><?php echo $requirements['zip'] ? 'Enabled' : 'Disabled'; ?></td>
                                       </tr>
                                       <tr>
                                           <td>Config Directory Writable</td>
                                           <td><?php echo $requirements['writable_config'] ? '<span class="text-success">✓</span>' : '<span class="text-danger">✗</span>'; ?></td>
                                           <td><span class="badge bg-danger">Required</span></td>
                                           <td><?php echo $requirements['writable_config'] ? 'Writable' : 'Not Writable'; ?></td>
                                       </tr>
                                       <tr>
                                           <td>Uploads Directory Writable</td>
                                           <td><?php echo $requirements['writable_uploads'] ? '<span class="text-success">✓</span>' : '<span class="text-danger">✗</span>'; ?></td>
                                           <td><span class="badge bg-danger">Required</span></td>
                                           <td><?php echo $requirements['writable_uploads'] ? 'Writable' : 'Not Writable'; ?></td>
                                       </tr>
                                       <tr>
                                           <td>Database Directory Writable</td>
                                           <td><?php echo $requirements['writable_database'] ? '<span class="text-success">✓</span>' : '<span class="text-danger">✗</span>'; ?></td>
                                           <td><span class="badge bg-danger">Required</span></td>
                                           <td><?php echo $requirements['writable_database'] ? 'Writable' : 'Not Writable'; ?></td>
                                       </tr>
                                   </tbody>
                               </table>
                           </div>

                            <?php if ($requirements['passed']): ?>
                                <form method="POST">
                                    <button type="submit" class="btn btn-primary">Continue to Database Setup</button>
                                </form>
                                <?php if (!$requirements['gd'] || !$requirements['zip']): ?>
                                    <div class="alert alert-info mt-3">
                                        <strong>Note:</strong> Some optional extensions are missing. The system will work without them, but image processing and file compression features may be limited.
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="alert alert-danger">
                                    <strong>Required requirements failed!</strong> Please fix the failed requirements marked as "Required" before continuing with the installation.
                                </div>
                            <?php endif; ?>

                        <?php elseif ($step == 2): ?>
                            <!-- Step 2: Database Creation -->
                            <h4>Step 2: Database Setup</h4>
                            <p>Create SQLite database and tables.</p>

                            <form method="POST">
                                <input type="hidden" name="create_db" value="1">
                                <button type="submit" class="btn btn-primary">Create Database & Tables</button>
                            </form>

                        <?php elseif ($step == 3): ?>
                            <!-- Step 3: Administrator Account -->
                            <h4>Step 3: Administrator Account</h4>
                            <p>Create the main administrator account.</p>

                            <form method="POST">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="admin_first_name" class="form-label">First Name</label>
                                        <input type="text" class="form-control" id="admin_first_name" name="admin_first_name" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="admin_last_name" class="form-label">Last Name</label>
                                        <input type="text" class="form-control" id="admin_last_name" name="admin_last_name" required>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="admin_username" class="form-label">Username</label>
                                    <input type="text" class="form-control" id="admin_username" name="admin_username" value="admin" required>
                                </div>
                                <div class="mb-3">
                                    <label for="admin_email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="admin_email" name="admin_email" required>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="admin_password" class="form-label">Password</label>
                                        <input type="password" class="form-control" id="admin_password" name="admin_password" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="admin_confirm_password" class="form-label">Confirm Password</label>
                                        <input type="password" class="form-control" id="admin_confirm_password" name="admin_confirm_password" required>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary">Create Administrator Account</button>
                            </form>

                        <?php elseif ($step == 4): ?>
                            <!-- Step 4: Installation Complete -->
                            <h4>Installation Complete!</h4>
                            <div class="alert alert-success">
                                <h5>✓ Installation Successful</h5>
                                <p>Your School Management System has been successfully installed.</p>
                            </div>

                            <div class="card">
                                <div class="card-body">
                                    <h5>Default Login Credentials</h5>
                                    <table class="table table-sm">
                                        <tr>
                                            <td><strong>Username:</strong></td>
                                            <td>admin</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Password:</strong></td>
                                            <td>As set during installation</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            <div class="mt-4">
                                <a href="/" class="btn btn-primary">Go to Homepage</a>
                                <a href="/login" class="btn btn-secondary">Go to Login</a>
                            </div>

                            <div class="alert alert-info mt-3">
                                <strong>Important:</strong> Please change the default password after first login for security.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>