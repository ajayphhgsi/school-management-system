<?php
/**
 * Student Middleware - Ensures user is authenticated and has student role
 */

class Student {
    public function handle() {
        // Check if user is logged in
        if (!isset($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }

        // Check if user has student role
        if ($_SESSION['user']['role'] !== 'student') {
            header('Location: /admin/dashboard');
            exit;
        }

        return true;
    }
}