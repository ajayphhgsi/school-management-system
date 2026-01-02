<?php
/**
 * SMS Configuration
 */

return [
    'provider' => 'twilio', // 'twilio', 'nexmo', 'aws_sns', etc.
    'twilio' => [
        'account_sid' => '',
        'auth_token' => '',
        'from_number' => '', // Twilio phone number
    ],
    'nexmo' => [
        'api_key' => '',
        'api_secret' => '',
        'from_number' => '',
    ],
    'enabled' => false, // Set to true to enable SMS notifications
    'debug' => false, // Set to true for debugging SMS issues
];