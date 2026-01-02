<?php
/**
 * Base Controller Class
 */

class Controller {
    protected $db;
    protected $session;
    protected $security;
    protected $validator;

    public function __construct() {
        $this->db = new Database();
        $this->session = new Session();
        $this->security = new Security();
        $this->validator = new Validator();
    }

    protected function render($view, $data = []) {
        extract($data);
        $viewFile = VIEWS_PATH . $view . '.php';
        if (file_exists($viewFile)) {
            require $viewFile;
        } else {
            echo "View not found: {$view}";
        }
    }

    protected function json($data) {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    protected function redirect($url) {
        header("Location: {$url}");
        exit;
    }

    protected function back() {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        $this->redirect($referer);
    }

    protected function middleware($middleware) {
        $middlewareClass = ucfirst($middleware);
        $middlewareFile = BASE_PATH . 'middleware/' . $middlewareClass . '.php';

        if (file_exists($middlewareFile)) {
            require_once $middlewareFile;
            $middlewareInstance = new $middlewareClass();
            $middlewareInstance->handle();
        }
    }

    protected function validate($data, $rules) {
        $this->validator = new Validator($data, $this->db);
        return $this->validator->validate($rules);
    }

    protected function getValidationErrors() {
        return $this->validator->getErrors();
    }

    protected function csrfToken() {
        return $this->security->generateCSRFToken();
    }

    protected function checkCsrfToken($token) {
        return $this->security->validateCSRFToken($token);
    }
}