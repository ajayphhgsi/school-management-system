<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Academic Year - School Management System</title>
    <link href="/assets/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 400px;
            width: 100%;
        }
        .login-header {
            background: #667eea;
            color: white;
            padding: 2rem;
            text-align: center;
        }
        .login-body {
            padding: 2rem;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-login {
            background: #667eea;
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 25px;
            font-weight: 600;
        }
        .btn-login:hover {
            background: #5a67d8;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="login-card">
                    <div class="login-header">
                        <h3>School Management System</h3>
                        <p>Please select the current academic year</p>
                    </div>
                    <div class="login-body">
                        <?php if (isset($_SESSION['flash']['error'])): ?>
                            <div class="alert alert-danger">
                                <?php echo $_SESSION['flash']['error']; unset($_SESSION['flash']['error']); ?>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($_SESSION['flash']['success'])): ?>
                            <div class="alert alert-success">
                                <?php echo $_SESSION['flash']['success']; unset($_SESSION['flash']['success']); ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="/select-academic-year">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                            <div class="mb-3">
                                <label for="academic_year_id" class="form-label">Academic Year</label>
                                <select class="form-control" id="academic_year_id" name="academic_year_id" required>
                                    <option value="">Select Academic Year</option>
                                    <?php foreach ($academic_years as $year): ?>
                                        <option value="<?php echo $year['id']; ?>" <?php echo (isset($_SESSION['flash']['old']['academic_year_id']) && $_SESSION['flash']['old']['academic_year_id'] == $year['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($year['year_name']); ?> (<?php echo date('M Y', strtotime($year['start_date'])); ?> - <?php echo date('M Y', strtotime($year['end_date'])); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (isset($_SESSION['flash']['errors']['academic_year_id'])): ?>
                                    <div class="text-danger small"><?php echo $_SESSION['flash']['errors']['academic_year_id'][0]; ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-login">Continue</button>
                            </div>
                        </form>

                        <div class="text-center mt-3">
                            <a href="/logout" class="text-decoration-none">Logout</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="/assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>