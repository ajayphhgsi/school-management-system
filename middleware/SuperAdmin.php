<?php
/**
 * SuperAdmin Middleware - SuperAdmin Role Check
 */

class SuperAdmin {
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

        // Then check if superadmin
        $userRole = $this->session->getUserRole();
        if ($userRole !== 'superadmin') {
            http_response_code(403);
            echo "Access Denied - SuperAdmin privileges required";
            exit;
        }
    }
}
?>