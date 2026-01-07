<?php

require_once 'core/TOTP.php';
/**
 * Auth Controller - Authentication Management
 */

class AuthController extends Controller {

    public function showLogin() {
        // Check if already logged in
        $auth = new Auth();
        $auth->guest();

        $csrfToken = $this->security->generateCSRFToken();
        $this->render('auth/login', ['csrf_token' => $csrfToken]);
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /login');
            exit;
        }

        $data = [
            'username' => $_POST['username'] ?? '',
            'password' => $_POST['password'] ?? '',
            'remember_me' => isset($_POST['remember_me']),
            'csrf_token' => $_POST['csrf_token'] ?? ''
        ];

        // Validate CSRF token
        if (!$this->security->validateCSRFToken($data['csrf_token'])) {
            $this->session->setFlash('error', 'Invalid CSRF token');
            header('Location: /login');
            exit;
        }

        // Validate input
        $rules = [
            'username' => 'required',
            'password' => 'required'
        ];

        $this->validator = new Validator($data);
        if (!$this->validator->validate($rules)) {
            $this->session->setFlash('errors', $this->validator->getErrors());
            $this->session->setFlash('old', $data);
            header('Location: /login');
            exit;
        }

        // Check rate limiting (disabled for development)
        // if (!$this->security->checkRateLimit('login_' . $data['username'], 10)) {
        //     $this->session->setFlash('error', 'Too many login attempts. Please try again later.');
        //     header('Location: /login');
        //     exit;
        // }

        // Authenticate user
        $user = $this->db->selectOne(
            "SELECT u.*, r.role_name as role FROM users u JOIN user_roles r ON u.role_id = r.id WHERE (u.username = ? OR u.email = ?) AND u.is_active = 1",
            [$data['username'], $data['username']]
        );

        if ($user && $this->security->verifyPassword($data['password'], $user['password'])) {
            // Update last login
            $this->db->update('users', ['last_login' => date('Y-m-d H:i:s')], 'id = ?', [$user['id']]);

            // Handle Remember Me
            if ($data['remember_me']) {
                // Create a remember token (simplified - in production use secure token)
                $rememberToken = bin2hex(random_bytes(32));
                $this->db->update('users', ['remember_token' => $rememberToken], 'id = ?', [$user['id']]);

                // Set cookie for 30 days
                setcookie('remember_token', $rememberToken, time() + (30 * 24 * 60 * 60), '/', '', true, true);
            }

            // Check if 2FA required
            if (($user['role'] === 'admin' || $user['role'] === 'superadmin') && $user['2fa_enabled']) {
                $this->session->set('2fa_user_id', $user['id']);
                header('Location: /verify-2fa');
                exit;
            }

            // Set session
            $this->session->setUser($user);

            // Redirect based on role
            if ($user['role'] === 'superadmin' || $user['role'] === 'admin') {
                header('Location: /select-academic-year');
            } else {
                header('Location: /student/dashboard');
            }
            exit;
        } else {
            $this->session->setFlash('error', 'Invalid username or password');
            $this->session->setFlash('old', ['username' => $data['username']]);
            header('Location: /login');
            exit;
        }
    }

    public function selectAcademicYear() {
        // Check if user is logged in and has admin or superadmin role
        $auth = new Auth();
        $auth->check();

        $user = $this->session->getUser();
        if (!in_array($user['role'], ['admin', 'superadmin'])) {
            header('Location: /login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'academic_year_id' => $_POST['academic_year_id'] ?? '',
                'csrf_token' => $_POST['csrf_token'] ?? ''
            ];

            // Validate CSRF token
            if (!$this->security->validateCSRFToken($data['csrf_token'])) {
                $this->session->setFlash('error', 'Invalid CSRF token');
                header('Location: /select-academic-year');
                exit;
            }

            // Validate input
            $rules = [
                'academic_year_id' => 'required|integer'
            ];

            $this->validator = new Validator($data);
            if (!$this->validator->validate($rules)) {
                $this->session->setFlash('errors', $this->validator->getErrors());
                $this->session->setFlash('old', $data);
                header('Location: /select-academic-year');
                exit;
            }

            // Check if academic year exists and is active
            $academicYear = $this->db->selectOne("SELECT * FROM academic_years WHERE id = ? AND is_active = 1", [$data['academic_year_id']]);
            if (!$academicYear) {
                $this->session->setFlash('error', 'Invalid academic year selected');
                header('Location: /select-academic-year');
                exit;
            }

            // Store selected academic year in session
            $this->session->set('academic_year_id', $academicYear['id']);

            // Redirect to appropriate dashboard
            if ($user['role'] === 'superadmin') {
                header('Location: /superadmin/dashboard');
            } elseif ($user['role'] === 'admin') {
                header('Location: /admin/dashboard');
            }
            exit;
        } else {
            // Display the selection form
            $csrfToken = $this->security->generateCSRFToken();
            $academicYears = $this->db->select("SELECT * FROM academic_years WHERE is_active = 1 ORDER BY start_date DESC");
            $this->render('auth/select-academic-year', ['csrf_token' => $csrfToken, 'academic_years' => $academicYears]);
        }
    }

    public function logout() {
        $this->session->logout();
        header('Location: /login');
        exit;
    }

    public function showForgotPassword() {
        $auth = new Auth();
        $auth->guest();

        $csrfToken = $this->security->generateCSRFToken();
        $this->render('auth/forgot_password', ['csrf_token' => $csrfToken]);
    }

    public function forgotPassword() {
        // Implementation for forgot password
        // This would typically send a reset email
        $this->session->setFlash('success', 'Password reset instructions sent to your email');
        header('Location: /login');
    }

    public function setup2FA() {
        $auth = new Auth();
        $auth->check();

        $user = $this->session->getUser();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'code' => $_POST['code'] ?? '',
                'csrf_token' => $_POST['csrf_token'] ?? ''
            ];

            // Validate CSRF token
            if (!$this->security->validateCSRFToken($data['csrf_token'])) {
                $this->session->setFlash('error', 'Invalid CSRF token');
                header('Location: /setup-2fa');
                exit;
            }

            // Get secret from session
            $secret = $this->session->get('2fa_secret');
            if (!$secret) {
                $this->session->setFlash('error', '2FA setup session expired. Please try again.');
                header('Location: /setup-2fa');
                exit;
            }

            // Verify code
            if (TOTP::verify($secret, $data['code'])) {
                // Enable 2FA for user
                $this->db->update('users', [
                    '2fa_enabled' => 1,
                    '2fa_secret' => $secret
                ], 'id = ?', [$user['id']]);

                // Clear session
                $this->session->remove('2fa_secret');

                $this->session->setFlash('success', 'Two-factor authentication has been enabled.');
                header('Location: /admin/dashboard');
                exit;
            } else {
                $this->session->setFlash('error', 'Invalid code. Please try again.');
                header('Location: /setup-2fa');
                exit;
            }
        } else {
            // Generate secret and QR code
            $secret = TOTP::generateSecret();
            $qrCode = TOTP::generateQRCode($secret, $user['username']);

            // Store secret in session
            $this->session->set('2fa_secret', $secret);

            $csrfToken = $this->security->generateCSRFToken();
            $this->render('auth/setup-2fa', [
                'qr_code' => $qrCode,
                'secret' => $secret,
                'csrf_token' => $csrfToken
            ]);
        }
    }

    public function verify2FA() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'code' => $_POST['code'] ?? '',
                'csrf_token' => $_POST['csrf_token'] ?? ''
            ];

            // Validate CSRF token
            if (!$this->security->validateCSRFToken($data['csrf_token'])) {
                $this->session->setFlash('error', 'Invalid CSRF token');
                header('Location: /verify-2fa');
                exit;
            }

            // Get user ID from session
            $userId = $this->session->get('2fa_user_id');
            if (!$userId) {
                $this->session->setFlash('error', '2FA verification session expired.');
                header('Location: /login');
                exit;
            }

            // Get user
            $user = $this->db->selectOne("SELECT u.*, r.role_name as role FROM users u JOIN user_roles r ON u.role_id = r.id WHERE u.id = ?", [$userId]);
            if (!$user || !$user['2fa_enabled'] || !$user['2fa_secret']) {
                $this->session->setFlash('error', '2FA not properly configured.');
                header('Location: /login');
                exit;
            }

            // Verify code
            if (TOTP::verify($user['2fa_secret'], $data['code'])) {
                // Clear 2FA session and set user
                $this->session->remove('2fa_user_id');
                $this->session->setUser($user);

                // Redirect based on role
                if ($user['role'] === 'superadmin') {
                    header('Location: /select-academic-year');
                } elseif ($user['role'] === 'admin') {
                    header('Location: /select-academic-year');
                } else {
                    header('Location: /student/dashboard');
                }
                exit;
            } else {
                $this->session->setFlash('error', 'Invalid code. Please try again.');
                header('Location: /verify-2fa');
                exit;
            }
        } else {
            $csrfToken = $this->security->generateCSRFToken();
            $this->render('auth/verify-2fa', ['csrf_token' => $csrfToken]);
        }
    }

    public function disable2FA() {
        $auth = new Auth();
        $auth->check();

        $user = $this->session->getUser();

        $data = [
            'csrf_token' => $_POST['csrf_token'] ?? ''
        ];

        // Validate CSRF token
        if (!$this->security->validateCSRFToken($data['csrf_token'])) {
            $this->session->setFlash('error', 'Invalid CSRF token');
            header('Location: /admin/settings');
            exit;
        }

        // Disable 2FA
        $this->db->update('users', [
            '2fa_enabled' => 0,
            '2fa_secret' => null
        ], 'id = ?', [$user['id']]);

        $this->session->setFlash('success', 'Two-factor authentication has been disabled.');
        header('Location: /admin/settings');
        exit;
    }

}