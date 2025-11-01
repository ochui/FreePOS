<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PosController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\InstallController;

// Authentication routes
Route::match(['get', 'post'], '/auth', [AuthController::class, 'authenticate']);
Route::match(['get', 'post'], '/authrenew', [AuthController::class, 'renewToken']);
Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout']);
Route::match(['get', 'post'], '/hello', [AuthController::class, 'hello']);

// Installation routes
Route::prefix('install')->group(function () {
    Route::match(['get', 'post'], '/status', [InstallController::class, 'status']);
    Route::match(['get', 'post'], '/requirements', [InstallController::class, 'requirements']);
    Route::match(['get', 'post'], '/test-database', [InstallController::class, 'testDatabase']);
    Route::match(['get', 'post'], '/save-database', [InstallController::class, 'saveDatabaseConfig']);
    Route::match(['get', 'post'], '/configure-admin', [InstallController::class, 'configureAdmin']);
    Route::match(['get', 'post'], '/install-with-config', [InstallController::class, 'installWithConfig']);
    Route::match(['get', 'post'], '/', [InstallController::class, 'install']);
    Route::match(['get', 'post'], '/upgrade', [InstallController::class, 'upgrade']);
});

// POS routes
Route::prefix('config')->group(function () {
    Route::match(['get', 'post'], '/get', [PosController::class, 'getConfig']);
});

Route::prefix('items')->group(function () {
    Route::match(['get', 'post'], '/get', [PosController::class, 'getItems']);
    Route::match(['get', 'post'], '/add', [AdminController::class, 'addItem']);
    Route::match(['get', 'post'], '/edit', [AdminController::class, 'editItem']);
    Route::match(['get', 'post'], '/delete', [AdminController::class, 'deleteItem']);
});

Route::prefix('sales')->group(function () {
    Route::match(['get', 'post'], '/get', [PosController::class, 'getSales']);
    Route::match(['get', 'post'], '/add', [PosController::class, 'addSale']);
    Route::match(['get', 'post'], '/void', [PosController::class, 'voidSale']);
    Route::match(['get', 'post'], '/search', [PosController::class, 'searchSales']);
    Route::match(['get', 'post'], '/delete', [AdminController::class, 'deleteSale']);
});

Route::prefix('tax')->group(function () {
    Route::match(['get', 'post'], '/get', [PosController::class, 'getTaxes']);
});

Route::prefix('customers')->group(function () {
    Route::match(['get', 'post'], '/get', [PosController::class, 'getCustomers']);
    Route::match(['get', 'post'], '/add', [AdminController::class, 'addCustomer']);
    Route::match(['get', 'post'], '/edit', [AdminController::class, 'editCustomer']);
    Route::match(['get', 'post'], '/delete', [AdminController::class, 'deleteCustomer']);
});

Route::prefix('devices')->group(function () {
    Route::match(['get', 'post'], '/get', [PosController::class, 'getDevices']);
    Route::match(['get', 'post'], '/add', [AdminController::class, 'addDevice']);
    Route::match(['get', 'post'], '/edit', [AdminController::class, 'editDevice']);
    Route::match(['get', 'post'], '/delete', [AdminController::class, 'deleteDevice']);
    Route::match(['get', 'post'], '/setup', [AdminController::class, 'setupDevice']);
});

Route::prefix('locations')->group(function () {
    Route::match(['get', 'post'], '/get', [PosController::class, 'getLocations']);
    Route::match(['get', 'post'], '/add', [AdminController::class, 'addLocation']);
    Route::match(['get', 'post'], '/edit', [AdminController::class, 'editLocation']);
    Route::match(['get', 'post'], '/delete', [AdminController::class, 'deleteLocation']);
});

Route::prefix('stock')->group(function () {
    Route::match(['get', 'post'], '/get', [AdminController::class, 'getStock']);
    Route::match(['get', 'post'], '/add', [AdminController::class, 'addStock']);
    Route::match(['get', 'post'], '/set', [AdminController::class, 'setStock']);
    Route::match(['get', 'post'], '/history', [AdminController::class, 'getStockHistory']);
});

Route::prefix('settings')->group(function () {
    Route::match(['get', 'post'], '/get', [AdminController::class, 'getSettings']);
    Route::match(['get', 'post'], '/set', [AdminController::class, 'saveSettings']);
});

// Admin routes
Route::prefix('adminconfig')->group(function () {
    Route::match(['get', 'post'], '/get', [AdminController::class, 'getAdminConfig']);
});

Route::prefix('stats')->group(function () {
    Route::match(['get', 'post'], '/general', [AdminController::class, 'getOverviewStats']);
});

// Customer portal routes
Route::prefix('customer')->group(function () {
    Route::match(['get', 'post'], '/auth', [AuthController::class, 'customerAuth']);
    Route::match(['get', 'post'], '/config', [CustomerController::class, 'getConfig']);
    Route::match(['get', 'post'], '/register', [CustomerController::class, 'register']);
});
