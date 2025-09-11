<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Application Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the core configuration for the Pos application.
    | Values can be overridden using environment variables.
    |
    */

    'timezone' => getenv('TIMEZONE') ?: 'UTC',
    
    // Communication provider configuration
    'communication_provider' => getenv('COMMUNICATION_PROVIDER') ?: 'socketio',
    
    // Socket.IO configuration
    'feedserver_host' => getenv('FEED_SERVER_HOST') ?: '127.0.0.1',
    'feedserver_port' => getenv('FEED_SERVER_PORT') ?: 3000,
    'feedserver_key' => getenv('FEED_SERVER_KEY') ?: 'supersecretkey',
    
    // Pusher configuration
    'pusher_app_id' => getenv('PUSHER_APP_ID') ?: '',
    'pusher_app_key' => getenv('PUSHER_APP_KEY') ?: '',
    'pusher_app_secret' => getenv('PUSHER_APP_SECRET') ?: '',
    'pusher_app_cluster' => getenv('PUSHER_APP_CLUSTER') ?: 'us2',
    
    // Ably configuration
    'ably_api_key' => getenv('ABLY_API_KEY') ?: '',

    /*
    |--------------------------------------------------------------------------
    | Database Configuration
    |--------------------------------------------------------------------------
    |
    | Database settings are handled by the DbConfig class but can be
    | referenced here for consistency.
    |
    */

    'database' => [
        'host' => getenv('DATABASE_HOST') ?: 'localhost',
        'port' => getenv('DATABASE_PORT') ?: '3306',
        'name' => getenv('DATABASE_NAME') ?: '',
        'user' => getenv('DATABASE_USER') ?: '',
        'password' => getenv('DATABASE_PASSWORD') ?: '',
    ],

    /*
    |--------------------------------------------------------------------------
    | Email Configuration
    |--------------------------------------------------------------------------
    |
    | Email settings for notifications and system emails.
    |
    */

    'email' => [
        'host' => getenv('EMAIL_HOST') ?: '',
        'port' => getenv('EMAIL_PORT') ?: 587,
        'username' => getenv('EMAIL_USERNAME') ?: '',
        'password' => getenv('EMAIL_PASSWORD') ?: '',
        'encryption' => getenv('EMAIL_ENCRYPTION') ?: 'tls',
    ],

    /*
    |--------------------------------------------------------------------------
    | Application Base Path
    |--------------------------------------------------------------------------
    |
    | This value is set automatically by the bootstrap process.
    |
    */

    'base_path' => defined('APP_BASE_PATH') ? APP_BASE_PATH : dirname(__DIR__),
];
