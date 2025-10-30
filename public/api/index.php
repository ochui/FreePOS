<?php

/**
 * Laravel API Entry Point
 * 
 * This file bootstraps the Laravel application and handles API requests
 */

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Check if composer dependencies are installed
$vendorAutoload = __DIR__ . '/../../vendor/autoload.php';
if (!file_exists($vendorAutoload)) {
    header('Content-Type: application/json');
    echo json_encode([
        'errorCode' => 'dependency',
        'error' => 'Composer dependencies not installed. Please run "composer install" first.',
        'data' => null
    ]);
    exit;
}

// Register the Composer autoloader
require $vendorAutoload;

// Bootstrap Laravel application
$app = require_once __DIR__ . '/../../bootstrap/app.php';

// Handle the request through Laravel's kernel
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Request::capture()
)->send();

$kernel->terminate($request, $response);
