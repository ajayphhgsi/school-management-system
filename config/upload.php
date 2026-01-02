<?php
/**
 * Upload Configuration
 */

return [
    'max_file_size' => 5242880, // 5MB
    'allowed_extensions' => ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx'],
    'upload_path' => BASE_PATH . 'uploads/',
    'image_max_width' => 1920,
    'image_max_height' => 1080,
    'image_quality' => 85,
    'create_thumbnails' => true,
    'thumbnail_width' => 300,
    'thumbnail_height' => 300,
];