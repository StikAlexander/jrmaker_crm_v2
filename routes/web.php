<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\PasswordChangeController;
use App\Http\Controllers\PaymentWebhookController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/verify-password-change', [PasswordChangeController::class, 'verify'])->name('password.change.verify');
Route::post('/verify-password-change', [PasswordChangeController::class, 'verifyCode'])->name('password.change.verify_code');

route::post('/payment/callback', [PaymentWebhookController::class, 'handleCallback'])->name('payment.callback');

