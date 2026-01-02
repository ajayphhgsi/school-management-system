<?php
/**
 * Email Configuration
 */

return [
    'smtp_host' => 'smtp.gmail.com',
    'smtp_port' => 587,
    'smtp_encryption' => 'tls',
    'smtp_username' => '',
    'smtp_password' => '',
    'from_email' => 'noreply@school.com',
    'from_name' => 'School Management System',
    'reply_to_email' => 'admin@school.com',
    'reply_to_name' => 'School Admin',
    'use_smtp' => false, // Set to true to use SMTP, false for PHP mail()
    'debug' => false, // Set to true for debugging email issues
];