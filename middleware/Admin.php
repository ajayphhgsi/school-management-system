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
            error_log("Admin middleware: User not logged in");
            header('Location: /login');
            exit;
        }

        // Get user data
        $user = $this->session->getUser();
        error_log("Admin middleware: User data: " . json_encode($user));

        // Check if admin or superadmin by role_id (1 = admin, 3 = superadmin)
        $roleId = $user['role_id'] ?? null;
        error_log("Admin middleware: Role ID: " . $roleId);
        if ($roleId !== 1 && $roleId !== 3) {
            error_log("Admin middleware: Access denied for role_id: " . $roleId);
            http_response_code(403);
            echo "Access Denied - Admin privileges required";
            exit;
        }
    }
}
?>