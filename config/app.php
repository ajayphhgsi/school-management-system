<?php
/**
 * Application Configuration
 */

return [
    'app' => [
        'name' => 'School Management System',
        'version' => '1.0.0',
        'url' => 'http://localhost:8000',
        'timezone' => 'UTC',
        'debug' => true,
        'log_level' => 'debug',
    ],
    'database' => [
        'host' => 'localhost',
        'name' => 'school_management',
        'user' => 'root',
        'password' => '',
        'charset' => 'utf8mb4',
    ],
    'session' => [
        'name' => 'school_session',
        'lifetime' => 3600, // 1 hour
        'path' => '/',
        'domain' => '',
        'secure' => false,
        'httponly' => true,
    ],
    'upload' => [
        'max_size' => 5242880, // 5MB
        'allowed_types' => ['jpg', 'jpeg', 'png', 'gif', 'pdf'],
        'path' => BASE_PATH . 'uploads/',
    ],
    'email' => [
        'smtp_host' => 'smtp.gmail.com',
        'smtp_port' => 587,
        'smtp_user' => '',
        'smtp_pass' => '',
        'from_email' => 'noreply@school.com',
        'from_name' => 'School Management System',
    ],
];