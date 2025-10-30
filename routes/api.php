<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Controllers\ViewController;
use App\Controllers\Api\AuthController;
use App\Controllers\Api\PosController;
use App\Controllers\Api\AdminController;
use App\Controllers\Api\CustomerController;
use App\Controllers\Api\InstallController;
use App\Controllers\Api\VariantsController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Template content routes
Route::get('/admin/content/{template}', [ViewController::class, 'adminContent']);
Route::get('/customer/content/{template}', [ViewController::class, 'customerContent']);
Route::get('/installer/content/{template}', [ViewController::class, 'installerView']);

// Authentication routes
Route::match(['GET', 'POST'], '/auth', [AuthController::class, 'authenticate']);
Route::match(['GET', 'POST'], '/authrenew', [AuthController::class, 'renewToken']);
Route::match(['GET', 'POST'], '/logout', [AuthController::class, 'logout']);
Route::match(['GET', 'POST'], '/hello', [AuthController::class, 'hello']);
Route::match(['GET', 'POST'], '/auth/websocket', [AuthController::class, 'authorizeWebsocket']);

// Installation routes
Route::match(['GET', 'POST'], '/install/status', [InstallController::class, 'status']);
Route::match(['GET', 'POST'], '/install/requirements', [InstallController::class, 'requirements']);
Route::match(['GET', 'POST'], '/install/test-database', [InstallController::class, 'testDatabase']);
Route::match(['GET', 'POST'], '/install/save-database', [InstallController::class, 'saveDatabaseConfig']);
Route::match(['GET', 'POST'], '/install/configure-admin', [InstallController::class, 'configureAdmin']);
Route::match(['GET', 'POST'], '/install/install-with-config', [InstallController::class, 'installWithConfig']);
Route::match(['GET', 'POST'], '/install', [InstallController::class, 'install']);
Route::match(['GET', 'POST'], '/install/upgrade', [InstallController::class, 'upgrade']);

// POS routes
Route::match(['GET', 'POST'], '/config/get', [PosController::class, 'getConfig']);
Route::match(['GET', 'POST'], '/items/get', [PosController::class, 'getItems']);
Route::match(['GET', 'POST'], '/sales/get', [PosController::class, 'getSales']);
Route::match(['GET', 'POST'], '/tax/get', [PosController::class, 'getTaxes']);
Route::match(['GET', 'POST'], '/customers/get', [PosController::class, 'getCustomers']);
Route::match(['GET', 'POST'], '/devices/get', [PosController::class, 'getDevices']);
Route::match(['GET', 'POST'], '/locations/get', [PosController::class, 'getLocations']);
Route::match(['GET', 'POST'], '/orders/set', [PosController::class, 'setOrder']);
Route::match(['GET', 'POST'], '/orders/remove', [PosController::class, 'removeOrder']);
Route::match(['GET', 'POST'], '/sales/add', [PosController::class, 'addSale']);
Route::match(['GET', 'POST'], '/sales/void', [PosController::class, 'voidSale']);
Route::match(['GET', 'POST'], '/sales/search', [PosController::class, 'searchSales']);
Route::match(['GET', 'POST'], '/sales/updatenotes', [PosController::class, 'updateSaleNotes']);
Route::match(['GET', 'POST'], '/sales/delete', [AdminController::class, 'deleteSale']);
Route::match(['GET', 'POST'], '/sales/deletevoid', [AdminController::class, 'deleteSaleVoid']);
Route::match(['GET', 'POST'], '/sales/adminvoid', [AdminController::class, 'adminVoidSale']);
Route::match(['GET', 'POST'], '/transactions/get', [PosController::class, 'getTransaction']);

// Admin routes
Route::match(['GET', 'POST'], '/devices/setup', [AdminController::class, 'setupDevice']);
Route::match(['GET', 'POST'], '/adminconfig/get', [AdminController::class, 'getAdminConfig']);

// Items management
Route::match(['GET', 'POST'], '/items/add', [AdminController::class, 'addItem']);
Route::match(['GET', 'POST'], '/items/edit', [AdminController::class, 'editItem']);
Route::match(['GET', 'POST'], '/items/delete', [AdminController::class, 'deleteItem']);
Route::match(['GET', 'POST'], '/items/import/set', [AdminController::class, 'setItemImport']);
Route::match(['GET', 'POST'], '/items/import/start', [AdminController::class, 'startItemImport']);

// Product Attributes management
Route::match(['GET', 'POST'], '/attributes/get', [AdminController::class, 'getProductAttributes']);
Route::match(['GET', 'POST'], '/attributes/add', [AdminController::class, 'addProductAttribute']);
Route::match(['GET', 'POST'], '/attributes/edit', [AdminController::class, 'updateProductAttribute']);
Route::match(['GET', 'POST'], '/attributes/delete', [AdminController::class, 'deleteProductAttribute']);

// Product Attribute Values management
Route::match(['GET', 'POST'], '/attribute-values/get', [AdminController::class, 'getProductAttributeValues']);
Route::match(['GET', 'POST'], '/attribute-values/add', [AdminController::class, 'addProductAttributeValue']);
Route::match(['GET', 'POST'], '/attribute-values/edit', [AdminController::class, 'updateProductAttributeValue']);
Route::match(['GET', 'POST'], '/attribute-values/delete', [AdminController::class, 'deleteProductAttributeValue']);

// Product Variants management
Route::match(['GET', 'POST'], '/variants/add', [AdminController::class, 'addProductVariant']);
Route::match(['GET', 'POST'], '/variants/edit', [AdminController::class, 'updateProductVariant']);
Route::match(['GET', 'POST'], '/variants/delete', [AdminController::class, 'deleteProductVariant']);
Route::match(['GET', 'POST'], '/variants/get', [AdminController::class, 'getProductVariants']);

// Variant Stock management
Route::match(['GET', 'POST'], '/variants/stock/get', [AdminController::class, 'getVariantStock']);
Route::match(['GET', 'POST'], '/variants/stock/set', [AdminController::class, 'updateVariantStock']);
Route::match(['GET', 'POST'], '/variants/stock/transfer', [AdminController::class, 'transferVariantStock']);

Route::match(['GET', 'POST'], '/variants/attributes/get', [VariantsController::class, 'getAttributes']);
Route::match(['GET', 'POST'], '/variants/attributes/add', [VariantsController::class, 'createAttribute']);
Route::match(['GET', 'POST'], '/variants/attributes/edit/{id}', [VariantsController::class, 'updateAttribute']);
Route::match(['GET', 'POST'], '/variants/attributes/delete/{id}', [VariantsController::class, 'deleteAttribute']);
Route::match(['GET', 'POST'], '/variants/attributes/{id}/values', [VariantsController::class, 'getAttributeValues']);
Route::match(['GET', 'POST'], '/variants/attribute-values/add', [VariantsController::class, 'createAttributeValue']);
Route::match(['GET', 'POST'], '/variants/attribute-values/edit/{id}', [VariantsController::class, 'updateAttributeValue']);
Route::match(['GET', 'POST'], '/variants/attribute-values/delete/{id}', [VariantsController::class, 'deleteAttributeValue']);
Route::match(['GET', 'POST'], '/variants/product/{id}/get', [VariantsController::class, 'getProductVariants']);
Route::match(['GET', 'POST'], '/variants/edit/{id}', [VariantsController::class, 'updateVariant']);
Route::match(['GET', 'POST'], '/variants/delete/{id}', [VariantsController::class, 'deleteVariant']);
Route::match(['GET', 'POST'], '/variants/find', [VariantsController::class, 'findVariantByAttributes']);
Route::match(['GET', 'POST'], '/items/{id}/make-variant-parent', [VariantsController::class, 'makeVariantParent']);

// Suppliers management
Route::match(['GET', 'POST'], '/suppliers/get', [AdminController::class, 'getSuppliers']);
Route::match(['GET', 'POST'], '/suppliers/add', [AdminController::class, 'addSupplier']);
Route::match(['GET', 'POST'], '/suppliers/edit', [AdminController::class, 'editSupplier']);
Route::match(['GET', 'POST'], '/suppliers/delete', [AdminController::class, 'deleteSupplier']);

// Categories management
Route::match(['GET', 'POST'], '/categories/get', [AdminController::class, 'getCategories']);
Route::match(['GET', 'POST'], '/categories/add', [AdminController::class, 'addCategory']);
Route::match(['GET', 'POST'], '/categories/edit', [AdminController::class, 'editCategory']);
Route::match(['GET', 'POST'], '/categories/delete', [AdminController::class, 'deleteCategory']);

// Stock management
Route::match(['GET', 'POST'], '/stock/get', [AdminController::class, 'getStock']);
Route::match(['GET', 'POST'], '/stock/add', [AdminController::class, 'addStock']);
Route::match(['GET', 'POST'], '/stock/set', [AdminController::class, 'setStock']);
Route::match(['GET', 'POST'], '/stock/transfer', [AdminController::class, 'transferStock']);
Route::match(['GET', 'POST'], '/stock/history', [AdminController::class, 'getStockHistory']);

// Customer management
Route::match(['GET', 'POST'], '/customers/add', [AdminController::class, 'addCustomer']);
Route::match(['GET', 'POST'], '/customers/edit', [AdminController::class, 'editCustomer']);
Route::match(['GET', 'POST'], '/customers/delete', [AdminController::class, 'deleteCustomer']);
Route::match(['GET', 'POST'], '/customers/contacts/add', [AdminController::class, 'addCustomerContact']);
Route::match(['GET', 'POST'], '/customers/contacts/edit', [AdminController::class, 'editCustomerContact']);
Route::match(['GET', 'POST'], '/customers/contacts/delete', [AdminController::class, 'deleteCustomerContact']);
Route::match(['GET', 'POST'], '/customers/setaccess', [AdminController::class, 'setCustomerAccess']);
Route::match(['GET', 'POST'], '/customers/setpassword', [AdminController::class, 'setCustomerPassword']);
Route::match(['GET', 'POST'], '/customers/sendreset', [AdminController::class, 'sendCustomerReset']);

// User management
Route::match(['GET', 'POST'], '/users/get', [AdminController::class, 'getUsers']);
Route::match(['GET', 'POST'], '/users/add', [AdminController::class, 'addUser']);
Route::match(['GET', 'POST'], '/users/edit', [AdminController::class, 'editUser']);
Route::match(['GET', 'POST'], '/users/delete', [AdminController::class, 'deleteUser']);
Route::match(['GET', 'POST'], '/users/disable', [AdminController::class, 'disableUser']);
Route::match(['GET', 'POST'], '/user/disable', [AdminController::class, 'disableUser']);

// Device management
Route::match(['GET', 'POST'], '/devices/add', [AdminController::class, 'addDevice']);
Route::match(['GET', 'POST'], '/devices/edit', [AdminController::class, 'editDevice']);
Route::match(['GET', 'POST'], '/devices/delete', [AdminController::class, 'deleteDevice']);
Route::match(['GET', 'POST'], '/devices/disable', [AdminController::class, 'disableDevice']);
Route::match(['GET', 'POST'], '/devices/registrations', [AdminController::class, 'getDeviceRegistrations']);
Route::match(['GET', 'POST'], '/devices/registrations/delete', [AdminController::class, 'deleteDeviceRegistration']);
Route::match(['GET', 'POST'], '/device/disable', [AdminController::class, 'disableDevice']);

// Location management
Route::match(['GET', 'POST'], '/locations/add', [AdminController::class, 'addLocation']);
Route::match(['GET', 'POST'], '/locations/edit', [AdminController::class, 'editLocation']);
Route::match(['GET', 'POST'], '/locations/delete', [AdminController::class, 'deleteLocation']);
Route::match(['GET', 'POST'], '/locations/disable', [AdminController::class, 'disableLocation']);
Route::match(['GET', 'POST'], '/location/add', [AdminController::class, 'addLocation']);
Route::match(['GET', 'POST'], '/location/edit', [AdminController::class, 'editLocation']);
Route::match(['GET', 'POST'], '/location/delete', [AdminController::class, 'deleteLocation']);
Route::match(['GET', 'POST'], '/location/disable', [AdminController::class, 'disableLocation']);

// Invoice management
Route::match(['GET', 'POST'], '/invoices/get', [AdminController::class, 'getInvoices']);
Route::match(['GET', 'POST'], '/invoices/search', [AdminController::class, 'searchInvoices']);
Route::match(['GET', 'POST'], '/invoices/add', [AdminController::class, 'addInvoice']);
Route::match(['GET', 'POST'], '/invoices/edit', [AdminController::class, 'editInvoice']);
Route::match(['GET', 'POST'], '/invoices/delete', [AdminController::class, 'deleteInvoice']);
Route::match(['GET', 'POST'], '/invoices/history/get', [AdminController::class, 'getInvoiceHistory']);
Route::match(['GET', 'POST'], '/invoices/generate', [AdminController::class, 'generateInvoice']);
Route::match(['GET', 'POST'], '/invoices/email', [AdminController::class, 'emailInvoice']);
Route::match(['GET', 'POST'], '/invoices/items/add', [AdminController::class, 'addInvoiceItem']);
Route::match(['GET', 'POST'], '/invoices/items/edit', [AdminController::class, 'editInvoiceItem']);
Route::match(['GET', 'POST'], '/invoices/items/delete', [AdminController::class, 'deleteInvoiceItem']);
Route::match(['GET', 'POST'], '/invoices/payments/add', [AdminController::class, 'addInvoicePayment']);
Route::match(['GET', 'POST'], '/invoices/payments/edit', [AdminController::class, 'editInvoicePayment']);
Route::match(['GET', 'POST'], '/invoices/payments/delete', [AdminController::class, 'deleteInvoicePayment']);

// Tax management
Route::match(['GET', 'POST'], '/tax/rules/add', [AdminController::class, 'addTaxRule']);
Route::match(['GET', 'POST'], '/tax/rules/edit', [AdminController::class, 'editTaxRule']);
Route::match(['GET', 'POST'], '/tax/rules/delete', [AdminController::class, 'deleteTaxRule']);
Route::match(['GET', 'POST'], '/tax/items/add', [AdminController::class, 'addTaxItem']);
Route::match(['GET', 'POST'], '/tax/items/edit', [AdminController::class, 'editTaxItem']);
Route::match(['GET', 'POST'], '/tax/items/delete', [AdminController::class, 'deleteTaxItem']);

// Logging
Route::match(['GET', 'POST'], '/logs/list', [AdminController::class, 'listLogs']);
Route::match(['GET', 'POST'], '/logs/read', [AdminController::class, 'readLog']);

// Utilities
Route::match(['GET', 'POST'], '/message/send', [AdminController::class, 'sendMessage']);
Route::match(['GET', 'POST'], '/device/reset', [AdminController::class, 'resetDevice']);
Route::match(['GET', 'POST'], '/devices/online', [AdminController::class, 'getOnlineDevices']);
Route::match(['GET', 'POST'], '/devices/register', [AdminController::class, 'registerDevice']);
Route::match(['GET', 'POST'], '/communication/trigger-updates', [AdminController::class, 'triggerCommunicationUpdates']);

// Settings management
Route::match(['GET', 'POST'], '/settings/get', [AdminController::class, 'getSettings']);
Route::match(['GET', 'POST'], '/settings/set', [AdminController::class, 'saveSettings']);
Route::match(['GET', 'POST'], '/settings/pos/get', [AdminController::class, 'getPosSettings']);
Route::match(['GET', 'POST'], '/settings/pos/set', [AdminController::class, 'savePosSettings']);
Route::match(['GET', 'POST'], '/settings/general/get', [AdminController::class, 'getGeneralSettings']);
Route::match(['GET', 'POST'], '/settings/general/set', [AdminController::class, 'saveGeneralSettings']);
Route::match(['GET', 'POST'], '/settings/invoice/get', [AdminController::class, 'getInvoiceSettings']);
Route::match(['GET', 'POST'], '/settings/invoice/set', [AdminController::class, 'saveInvoiceSettings']);
Route::match(['GET', 'POST'], '/stats/general', [AdminController::class, 'getOverviewStats']);
Route::match(['GET', 'POST'], '/stats/itemselling', [AdminController::class, 'getItemSellingStats']);
Route::match(['GET', 'POST'], '/stats/takings', [AdminController::class, 'getTakingsStats']);
Route::match(['GET', 'POST'], '/stats/locations', [AdminController::class, 'getLocationStats']);
Route::match(['GET', 'POST'], '/stats/devices', [AdminController::class, 'getDeviceStats']);
Route::match(['GET', 'POST'], '/stats/categoryselling', [AdminController::class, 'getCategorySellingStats']);
Route::match(['GET', 'POST'], '/stats/supplyselling', [AdminController::class, 'getSupplySellingStats']);
Route::match(['GET', 'POST'], '/stats/stock', [AdminController::class, 'getStockStats']);
Route::match(['GET', 'POST'], '/stats/users', [AdminController::class, 'getUserStats']);
Route::match(['GET', 'POST'], '/stats/tax', [AdminController::class, 'getTaxStats']);
Route::match(['GET', 'POST'], '/graph/general', [AdminController::class, 'getGeneralGraph']);
Route::match(['GET', 'POST'], '/graph/takings', [AdminController::class, 'getTakingsGraph']);
Route::match(['GET', 'POST'], '/graph/devices', [AdminController::class, 'getDevicesGraph']);
Route::match(['GET', 'POST'], '/graph/locations', [AdminController::class, 'getLocationsGraph']);
Route::match(['GET', 'POST'], '/file/upload', [AdminController::class, 'uploadFile']);

// Template management
Route::match(['GET', 'POST'], '/templates/get', [AdminController::class, 'getTemplates']);
Route::match(['GET', 'POST'], '/templates/edit', [AdminController::class, 'editTemplate']);
Route::match(['GET', 'POST'], '/templates/restore', [AdminController::class, 'restoreTemplate']);

// Customer API routes
Route::match(['GET', 'POST'], '/customer/auth', [AuthController::class, 'customerAuth']);
Route::match(['GET', 'POST'], '/customer/logout', [AuthController::class, 'logout']);
Route::match(['GET', 'POST'], '/customer/hello', [AuthController::class, 'customerHello']);
Route::match(['GET', 'POST'], '/customer/register', [CustomerController::class, 'register']);
Route::match(['GET', 'POST'], '/customer/resetpasswordemail', [CustomerController::class, 'sendPasswordResetEmail']);
Route::match(['GET', 'POST'], '/customer/resetpassword', [CustomerController::class, 'resetPassword']);
Route::match(['GET', 'POST'], '/customer/config', [CustomerController::class, 'getConfig']);
Route::match(['GET', 'POST'], '/customer/mydetails/get', [CustomerController::class, 'getMyDetails']);
Route::match(['GET', 'POST'], '/customer/mydetails/save', [CustomerController::class, 'saveMyDetails']);
Route::match(['GET', 'POST'], '/customer/transactions/get', [CustomerController::class, 'getTransactions']);
Route::match(['GET', 'POST'], '/customer/invoice/generate', [CustomerController::class, 'generateInvoice']);
