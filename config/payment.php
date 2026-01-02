<?php
/**
 * Payment Gateway Configuration
 */

return [
    'default_gateway' => 'razorpay', // 'razorpay' or 'stripe'
    'razorpay' => [
        'key_id' => '', // Your Razorpay Key ID
        'key_secret' => '', // Your Razorpay Key Secret
        'environment' => 'test', // 'test' or 'live'
    ],
    'stripe' => [
        'publishable_key' => '', // Your Stripe Publishable Key
        'secret_key' => '', // Your Stripe Secret Key
        'webhook_secret' => '', // Your Stripe Webhook Secret
        'environment' => 'test', // 'test' or 'live'
    ],
    'enabled' => false, // Set to true to enable payment processing
    'debug' => false, // Set to true for debugging payment issues
];