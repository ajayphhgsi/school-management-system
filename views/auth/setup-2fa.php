<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup 2FA - School Management System</title>
    <link href="/assets/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 500px;
            width: 100%;
        }
        .card-header {
            background: #667eea;
            color: white;
            padding: 2rem;
            text-align: center;
        }
        .card-body {
            padding: 2rem;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-primary {
            background: #667eea;
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 25px;
            font-weight: 600;
        }
        .btn-primary:hover {
            background: #5a67d8;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3>Setup Two-Factor Authentication</h3>
                    </div>
                    <div class="card-body">
                        <p>Scan the QR code with your authenticator app (e.g., Google Authenticator).</p>

                        <div class="text-center mb-3">
                            <img src="data:image/svg+xml;base64,<?= base64_encode($qr_code) ?>" alt="QR Code" class="img-fluid">
                        </div>

                        <p>If you can't scan, enter this secret manually: <code><?= htmlspecialchars($secret) ?></code></p>

                        <form method="post">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

                            <div class="mb-3">
                                <label for="code" class="form-label">Enter the 6-digit code from your app:</label>
                                <input type="text" class="form-control" id="code" name="code" required maxlength="6" pattern="[0-9]{6}">
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Enable 2FA</button>
                            </div>
                        </form>

                        <div class="text-center mt-3">
                            <a href="/admin/settings" class="text-decoration-none">Back to Settings</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="/assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>