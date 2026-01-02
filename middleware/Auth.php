<?php
/**
 * Auth Middleware - Authentication Check
 */

class Auth {
    private $session;

    public function __construct() {
        $this->session = new Session();
    }

    public function handle() {
        $this->check();
    }

    public function check() {
        if (!$this->session->isLoggedIn()) {
            header('Location: /login');
            exit;
        }
    }

    public function guest() {
        if ($this->session->isLoggedIn()) {
            $role = $this->session->getUserRole();
            if ($role === 'admin') {
                header('Location: /admin/dashboard');
            } else {
                header('Location: /student/dashboard');
            }
            exit;
        }
    }

    public function role($requiredRole) {
        $this->check();
        $userRole = $this->session->getUserRole();
        if ($userRole !== $requiredRole) {
            http_response_code(403);
            echo "Access Denied";
            exit;
        }
    }

    public function admin() {
        $this->role('admin');
    }

    public function student() {
        $this->role('student');
    }
}