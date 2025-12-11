<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Email Server Configurations
    |--------------------------------------------------------------------------
    |
    | Configure multiple email server settings for different domains.
    | These settings can be used for both sending (SMTP) and receiving (IMAP/POP3).
    |
    */

    'servers' => [
        'absa' => [
            'name' => 'ABSA SMTP Server',
            'domain' => 'absa.co.za',

            // SMTP Settings for sending emails
            'smtp' => [
                'host' => env('MAIL_HOST', 'smtp.absa.co.za'),
                'port' => env('MAIL_PORT', 25),
                'encryption' => env('MAIL_ENCRYPTION', null),
                'username' => env('MAIL_USERNAME', null),
                'password' => env('MAIL_PASSWORD', null),
                'timeout' => 60,
                'auth_mode' => null,
                'verify_peer' => env('MAIL_VERIFY_PEER', false),
            ],

            // IMAP Settings (if needed)
            'imap' => [
                'host' => null,
                'port' => null,
                'encryption' => null,
                'username' => null,
                'password' => null,
                'validate_cert' => false,
                'protocol' => 'imap',
            ],
        ],

        'zima' => [
            'name' => 'Zima Email Server (DISABLED - Connection Timeout)',
            'domain' => 'zima.co.tz',

            // SMTP Settings for sending emails
            'smtp' => [
                'host' => 'server354.web-hosting.com',
                'port' => 465,
                'encryption' => 'ssl',
                'username' => env('ZIMA_EMAIL_USERNAME', 'andrew.mashamba@zima.co.tz'),
                'password' => env('ZIMA_EMAIL_PASSWORD', ''),
                'timeout' => 60,
                'auth_mode' => null,
                'verify_peer' => true,
            ],
            
            // IMAP Settings for receiving emails
            'imap' => [
                'host' => 'zima.co.tz',
                'port' => 993,
                'encryption' => 'ssl',
                'username' => env('ZIMA_EMAIL_USERNAME', 'andrew.mashamba@zima.co.tz'),
                'password' => env('ZIMA_EMAIL_PASSWORD', ''),
                'validate_cert' => true,
                'protocol' => 'imap',
            ],
            
            // POP3 Settings (alternative to IMAP)
            'pop3' => [
                'host' => 'zima.co.tz',
                'port' => 995,
                'encryption' => 'ssl',
                'username' => env('ZIMA_EMAIL_USERNAME', 'andrew.mashamba@zima.co.tz'),
                'password' => env('ZIMA_EMAIL_PASSWORD', ''),
                'validate_cert' => true,
                'protocol' => 'pop3',
            ],
            
            // CalDAV Settings for calendar
            'caldav' => [
                'url' => 'https://zima.co.tz:2080',
                'port' => 2080,
                'calendar_path' => '/calendars/%s/calendar',
                'username' => env('ZIMA_EMAIL_USERNAME', 'andrew.mashamba@zima.co.tz'),
                'password' => env('ZIMA_EMAIL_PASSWORD', ''),
            ],
            
            // CardDAV Settings for contacts
            'carddav' => [
                'url' => 'https://zima.co.tz:2080',
                'port' => 2080,
                'addressbook_path' => '/addressbooks/%s/addressbook',
                'username' => env('ZIMA_EMAIL_USERNAME', 'andrew.mashamba@zima.co.tz'),
                'password' => env('ZIMA_EMAIL_PASSWORD', ''),
            ],
        ],
        
        // Non-SSL configuration (not recommended)
        'zima_insecure' => [
            'name' => 'Zima Email Server (Non-SSL)',
            'domain' => 'zima.co.tz',
            'warning' => 'This configuration is NOT recommended for security reasons',
            
            'smtp' => [
                'host' => 'mail.zima.co.tz',
                'port' => 25,
                'encryption' => null,
                'username' => env('ZIMA_EMAIL_USERNAME', 'andrew.mashamba@zima.co.tz'),
                'password' => env('ZIMA_EMAIL_PASSWORD', ''),
            ],
            
            'imap' => [
                'host' => 'mail.zima.co.tz',
                'port' => 143,
                'encryption' => null,
                'username' => env('ZIMA_EMAIL_USERNAME', 'andrew.mashamba@zima.co.tz'),
                'password' => env('ZIMA_EMAIL_PASSWORD', ''),
            ],
            
            'caldav' => [
                'url' => 'http://mail.zima.co.tz:2079',
                'port' => 2079,
                'calendar_path' => '/calendars/%s/calendar',
            ],
            
            'carddav' => [
                'url' => 'http://mail.zima.co.tz:2079',
                'port' => 2079,
                'addressbook_path' => '/addressbooks/%s/addressbook',
            ],
        ],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Default Email Server
    |--------------------------------------------------------------------------
    |
    | Specify which server configuration to use by default.
    | Changed from 'zima' to 'absa' due to connection timeout issues.
    |
    */

    'default' => env('EMAIL_SERVER', 'absa'),
    
    /*
    |--------------------------------------------------------------------------
    | Email Sync Settings
    |--------------------------------------------------------------------------
    |
    | Configure how emails are synchronized from external servers.
    |
    */
    
    'sync' => [
        'enabled' => env('EMAIL_SYNC_ENABLED', true),
        'interval' => env('EMAIL_SYNC_INTERVAL', 5), // minutes
        'batch_size' => env('EMAIL_SYNC_BATCH_SIZE', 50),
        'folders' => ['INBOX', 'Sent', 'Drafts', 'Trash', 'Spam'],
        'days_to_sync' => env('EMAIL_SYNC_DAYS', 30),
    ],
];