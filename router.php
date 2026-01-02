<?php
/**
 * PHP Built-in Server Router
 * This file handles routing for the PHP built-in development server
 */

if (php_sapi_name() === 'cli-server') {
    // Get the requested path
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

    // Remove query string
    $path = strtok($path, '?');

    // Handle static files
    $staticExtensions = ['css', 'js', 'png', 'jpg', 'jpeg', 'gif', 'ico', 'svg', 'woff', 'woff2', 'ttf', 'eot', 'html'];
    $pathInfo = pathinfo($path);

    if (isset($pathInfo['extension']) && in_array(strtolower($pathInfo['extension']), $staticExtensions)) {
        // Serve static files directly
        $file = __DIR__ . $path;
        if (file_exists($file)) {
            // Set appropriate content type
            $mimeTypes = [
                'css' => 'text/css',
                'js' => 'application/javascript',
                'png' => 'image/png',
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'gif' => 'image/gif',
                'ico' => 'image/x-icon',
                'svg' => 'image/svg+xml',
                'woff' => 'font/woff',
                'woff2' => 'font/woff2',
                'ttf' => 'font/ttf',
                'eot' => 'application/vnd.ms-fontobject',
                'html' => 'text/html'
            ];

            if (isset($mimeTypes[$pathInfo['extension']])) {
                header('Content-Type: ' . $mimeTypes[$pathInfo['extension']]);
            }

            readfile($file);
            exit;
        }
    }

    // For all other requests, route through index.php
    require __DIR__ . '/index.php';
    exit;
}

// If not running on CLI server, just include index.php
require __DIR__ . '/index.php';
?>