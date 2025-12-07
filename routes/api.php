<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Client\SePayController;

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

// SePay Webhook (no CSRF protection needed)
// Accept both GET (for testing) and POST (for actual webhook)
Route::match(['get', 'post'], '/sepay/webhook', [SePayController::class, 'webhook'])->name('api.sepay.webhook');
