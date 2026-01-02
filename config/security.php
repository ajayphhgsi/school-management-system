<?php
/**
 * Security Configuration
 */

return [
    'csrf_token_name' => 'csrf_token',
    'csrf_token_length' => 32,
    'password_min_length' => 8,
    'password_require_uppercase' => true,
    'password_require_lowercase' => true,
    'password_require_numbers' => true,
    'password_require_symbols' => false,
    'login_attempts_max' => 5,
    'login_lockout_time' => 900, // 15 minutes
    'session_regenerate_frequency' => 300, // 5 minutes
];