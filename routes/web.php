<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'app' => 'FreePOS',
        'version' => '2.0',
        'framework' => 'Laravel',
        'status' => 'running'
    ]);
});
