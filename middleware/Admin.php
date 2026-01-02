<?php
/**
 * Admin Middleware - Admin Role Check
 */

class Admin {
    private $session;

    public function __construct() {
        $this->session = new Session();
    }

    public function handle() {
        $this->check();
    }

    public function check() {
        // First check if logged in
        if (!$this->session->isLoggedIn()) {
            header('Location: /login');
            exit;
        }

        // Then check if admin or superadmin
        $userRole = $this->session->getUserRole();
        if ($userRole !== 'admin' && $userRole !== 'superadmin') {
            http_response_code(403);
            echo "Access Denied - Admin privileges required";
            exit;
        }
    }
}
?>