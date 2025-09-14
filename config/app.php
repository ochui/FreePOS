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

    'timezone' => env('TIMEZONE') ?: 'UTC',
    
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
        'host' => env('DATABASE_HOST') ?: 'localhost',
        'port' => env('DATABASE_PORT') ?: '3306',
        'name' => env('DATABASE_NAME') ?: '',
        'user' => env('DATABASE_USER') ?: '',
        'password' => env('DATABASE_PASSWORD') ?: '',
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
