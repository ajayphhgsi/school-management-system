<?php
/**
 * Security Class - Security Utilities and CSRF Protection
 */

class Security {
    private $config;

    public function __construct() {
        $this->config = require CONFIG_PATH . 'security.php';
    }

    public function generateCSRFToken() {
        if (!isset($_SESSION[$this->config['csrf_token_name']])) {
            $_SESSION[$this->config['csrf_token_name']] = bin2hex(random_bytes($this->config['csrf_token_length']));
        }
        return $_SESSION[$this->config['csrf_token_name']];
    }

    public function validateCSRFToken($token) {
        if (!isset($_SESSION[$this->config['csrf_token_name']]) || $token !== $_SESSION[$this->config['csrf_token_name']]) {
            return false;
        }
        return true;
    }

    public function getCSRFToken() {
        return $_SESSION[$this->config['csrf_token_name']] ?? $this->generateCSRFToken();
    }

    public function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }

    public function sanitizeInput($input) {
        if (is_array($input)) {
            return array_map([$this, 'sanitizeInput'], $input);
        }
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    public function validatePassword($password) {
        $errors = [];

        if (strlen($password) < $this->config['password_min_length']) {
            $errors[] = "Password must be at least {$this->config['password_min_length']} characters long";
        }

        if ($this->config['password_require_uppercase'] && !preg_match('/[A-Z]/', $password)) {
            $errors[] = "Password must contain at least one uppercase letter";
        }

        if ($this->config['password_require_lowercase'] && !preg_match('/[a-z]/', $password)) {
            $errors[] = "Password must contain at least one lowercase letter";
        }

        if ($this->config['password_require_numbers'] && !preg_match('/[0-9]/', $password)) {
            $errors[] = "Password must contain at least one number";
        }

        if ($this->config['password_require_symbols'] && !preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = "Password must contain at least one special character";
        }

        return $errors;
    }

    public function generateRandomString($length = 32) {
        return bin2hex(random_bytes($length / 2));
    }

    public function checkRateLimit($key, $maxAttempts = 5, $timeWindow = 900) {
        $attempts = $_SESSION['rate_limit'][$key]['attempts'] ?? 0;
        $lastAttempt = $_SESSION['rate_limit'][$key]['last_attempt'] ?? 0;

        if (time() - $lastAttempt > $timeWindow) {
            $attempts = 0;
        }

        if ($attempts >= $maxAttempts) {
            return false;
        }

        $_SESSION['rate_limit'][$key]['attempts'] = $attempts + 1;
        $_SESSION['rate_limit'][$key]['last_attempt'] = time();

        return true;
    }

    public function escapeOutput($output) {
        return htmlspecialchars($output, ENT_QUOTES, 'UTF-8');
    }
}