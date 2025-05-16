<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\WhatsappController;
use App\Http\Controllers\MidtransWebhookController;

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
Route::post('/midtrans/webhook', [MidtransWebhookController::class, 'handle'])->withoutMiddleware(['throttle:api']);
Route::get('/invoice/success', [BillingController::class, 'success'])->withoutMiddleware(['throttle:api'])->name('invoice.success');

Route::get('/invoice/{invoice_id}', [BillingController::class, 'cariInvoice'])->withoutMiddleware(['throttle:api'])->name('invoice.cari');



Route::post('/saveAdminNumber', [WhatsappController::class, 'saveAdminNumber']);



Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/undianapi', [AdminController::class, 'getUndianApi']);
