<?php
/**
 * Session Class - Session Management
 */

class Session {
    private $config;

    public function __construct() {
        $this->config = require CONFIG_PATH . 'app.php';
        $this->start();
    }

    private function start() {
        if (session_status() === PHP_SESSION_NONE) {
            session_name($this->config['session']['name']);
            session_set_cookie_params(
                $this->config['session']['lifetime'],
                $this->config['session']['path'],
                $this->config['session']['domain'],
                $this->config['session']['secure'],
                $this->config['session']['httponly']
            );
            session_start();
        }
    }

    public function set($key, $value) {
        $_SESSION[$key] = $value;
    }

    public function get($key, $default = null) {
        return $_SESSION[$key] ?? $default;
    }

    public function has($key) {
        return isset($_SESSION[$key]);
    }

    public function remove($key) {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }

    public function destroy() {
        session_destroy();
        $_SESSION = [];
    }

    public function regenerate() {
        session_regenerate_id(true);
    }

    public function setFlash($key, $value) {
        $_SESSION['flash'][$key] = $value;
    }

    public function getFlash($key, $default = null) {
        $value = $_SESSION['flash'][$key] ?? $default;
        if (isset($_SESSION['flash'][$key])) {
            unset($_SESSION['flash'][$key]);
        }
        return $value;
    }

    public function hasFlash($key) {
        return isset($_SESSION['flash'][$key]);
    }

    public function setUser($user) {
        $_SESSION['user'] = $user;
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['logged_in'] = true;
    }

    public function getUser() {
        return $_SESSION['user'] ?? null;
    }

    public function getUserId() {
        return $_SESSION['user_id'] ?? null;
    }

    public function getUserRole() {
        return $_SESSION['user_role'] ?? null;
    }

    public function isLoggedIn() {
        return $_SESSION['logged_in'] ?? false;
    }

    public function logout() {
        $this->remove('user');
        $this->remove('user_id');
        $this->remove('user_role');
        $this->remove('logged_in');
        $this->regenerate();
    }

    public function checkTimeout() {
        $lastActivity = $this->get('last_activity', 0);
        $timeout = $this->config['session']['lifetime'];

        if (time() - $lastActivity > $timeout) {
            $this->logout();
            return false;
        }

        $this->set('last_activity', time());
        return true;
    }
}