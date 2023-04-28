<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomersController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CompanyProfileController as CP;
use App\Http\Controllers\AnonymousController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::controller(AuthController::class)->group(function () {
    Route::post('login', 'login');
    Route::post('register', 'register');
    Route::post('logout', 'logout');
    Route::post('refresh', 'refresh');
    Route::post('forgot', 'forgot');

});

Route::controller(CustomersController::class)->group(function () {
    Route::get('customers', 'index');
    Route::get('filter/customers', 'filter');
    Route::get('deleted/customers', 'deleted');
    Route::post('customer', 'store');
    Route::get('customer/{id}', 'show');
    Route::get('revoke/customer/{id}', 'revoke'); // revoke customer
    Route::put('customer/{id}', 'update');
    Route::delete('customer/{id}', 'destroy');
    Route::post('search/customer', 'search');
    Route::get('customers/frequent-customers', 'frequentCustomers');
});

Route::controller(InvoiceController::class)->middleware(['checkApiToken','localization'])->group(function () {
    Route::post('invoice', 'store');
    Route::post('invoice/search', 'search');
    Route::post('invoice/field/delete', 'deleteField');
    Route::post('invoice/edit', 'editInvoice');
    Route::post('invoice/clone','cloneInvoice');
    Route::post('invoice/print','invoicePrint');
    Route::post('invoice/print/send','invoicePdfSend');
    Route::post('invoices/sent','invoiceSentList');
    Route::post('invoices/payment','changeInvoiceStatus');
    Route::post('invoices/bydate','invoiceListByDate');
    Route::post('invoice/byinvoiceID','getInvoice');
    Route::post('invoices/search/bydate','invoiceListByDate');
    Route::post('invoice/byinvoiceID','getInvoice');
});

Route::controller(DashboardController::class)->middleware(['checkApiToken','localization'])->group(function () {

    Route::post('dashboard/invoices','dashboardStats');
    Route::post('dashboard/notify','notifications');
});

Route::controller(CP::class)->middleware('auth:api')->prefix('company')->group(function () {
    Route::get('profile', 'index');
    Route::post('profile', 'saveProfile');

});

Route::controller(AnonymousController::class)->group(function () {
    Route::post('forgot', 'forgot');
});
