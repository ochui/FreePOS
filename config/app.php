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
    | Application Base Path
    |--------------------------------------------------------------------------
    |
    | This value is set automatically by the bootstrap process.
    |
    */

    'base_path' => defined('APP_BASE_PATH') ? APP_BASE_PATH : dirname(__DIR__),
];
