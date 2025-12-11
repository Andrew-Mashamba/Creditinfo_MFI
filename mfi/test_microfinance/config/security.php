<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Malware Scanning Configuration
    |--------------------------------------------------------------------------
    |
    | Configure malware scanning for uploaded files
    |
    */

    'malware_scanning' => [
        // Enable/disable malware scanning
        'enabled' => env('MALWARE_SCANNING_ENABLED', true),

        // Use ClamAV if available
        'use_clamav' => env('MALWARE_SCANNING_USE_CLAMAV', true),

        // Fallback to heuristic scanning
        'use_heuristic' => env('MALWARE_SCANNING_USE_HEURISTIC', true),

        // Quarantine infected files
        'quarantine_infected' => env('MALWARE_SCANNING_QUARANTINE', true),

        // Quarantine directory
        'quarantine_path' => storage_path('app/quarantine'),

        // Maximum scan timeout (seconds)
        'scan_timeout' => env('MALWARE_SCANNING_TIMEOUT', 120),
    ],

    /*
    |--------------------------------------------------------------------------
    | File Upload Configuration
    |--------------------------------------------------------------------------
    |
    | Configure file upload security settings
    |
    */

    'file_upload' => [
        // Maximum file size in bytes (20MB default)
        'max_file_size' => env('MAX_FILE_UPLOAD_SIZE', 20971520),

        // Allowed file extensions
        'allowed_extensions' => [
            // Documents
            'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx',
            'txt', 'rtf', 'odt', 'ods', 'odp', 'csv',

            // Images
            'jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg',

            // Archives
            'zip', 'rar', '7z', 'tar', 'gz',
        ],

        // Dangerous file extensions (always blocked)
        'dangerous_extensions' => [
            'php', 'php3', 'php4', 'php5', 'phtml', 'phar',
            'exe', 'com', 'bat', 'cmd', 'sh', 'bash',
            'js', 'jar', 'app', 'dmg', 'vbs', 'wsf',
            'asp', 'aspx', 'jsp', 'cgi', 'pl', 'py',
            'rb', 'ps1', 'msi', 'scr', 'dll', 'so',
        ],

        // Upload directories
        'upload_directories' => [
            'loan_applications' => public_path('loan_applications/documents'),
            'loan_documents' => public_path('loan_documents'),
            'client_documents' => public_path('client-documents'),
            'member_images' => public_path('member_images'),
        ],

        // Sanitize filenames
        'sanitize_filenames' => env('SANITIZE_FILENAMES', true),

        // Add timestamp to filenames
        'add_timestamp' => env('ADD_TIMESTAMP_TO_FILENAMES', true),

        // Add random string to filenames
        'add_random_string' => env('ADD_RANDOM_STRING_TO_FILENAMES', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Secure File Serving Configuration
    |--------------------------------------------------------------------------
    |
    | Configure secure file serving settings
    |
    */

    'file_serving' => [
        // Require authentication for all files
        'require_authentication' => env('FILE_SERVING_REQUIRE_AUTH', true),

        // Log file access
        'log_access' => env('FILE_SERVING_LOG_ACCESS', true),

        // Allowed disks for file serving
        'allowed_disks' => ['local', 'public', 'member_images'],

        // MIME types allowed for inline display
        'inline_mime_types' => [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
            'application/pdf',
        ],

        // Cache control headers
        'cache_control' => 'no-cache, no-store, must-revalidate',
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting for File Operations
    |--------------------------------------------------------------------------
    |
    | Rate limit file uploads and downloads to prevent abuse
    |
    */

    'rate_limiting' => [
        // Maximum uploads per minute
        'max_uploads_per_minute' => env('MAX_UPLOADS_PER_MINUTE', 10),

        // Maximum downloads per minute
        'max_downloads_per_minute' => env('MAX_DOWNLOADS_PER_MINUTE', 30),
    ],

];
