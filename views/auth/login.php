<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - School Management System</title>
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
                        <p>Please sign in to continue</p>
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

                        <form method="POST" action="/login">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                            <div class="mb-3">
                                <label for="username" class="form-label">Username or Email</label>
                                <input type="text" class="form-control" id="username" name="username"
                                       value="<?php echo $_SESSION['flash']['old']['username'] ?? ''; ?>" required>
                                <?php if (isset($_SESSION['flash']['errors']['username'])): ?>
                                    <div class="text-danger small"><?php echo $_SESSION['flash']['errors']['username'][0]; ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                                <?php if (isset($_SESSION['flash']['errors']['password'])): ?>
                                    <div class="text-danger small"><?php echo $_SESSION['flash']['errors']['password'][0]; ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="remember_me" name="remember_me" <?php echo (isset($_SESSION['flash']['old']['remember_me']) && $_SESSION['flash']['old']['remember_me']) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="remember_me">Remember me</label>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-login">Sign In</button>
                            </div>
                        </form>

                        <div class="text-center mt-3">
                            <a href="/forgot-password" class="text-decoration-none">Forgot your password?</a>
                        </div>

                        <hr class="my-4">

                        <div class="text-center text-muted">
                            <small>Demo Credentials:<br>
                            Admin: admin / admin123<br>
                            Student: student1 / student123</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="/assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>