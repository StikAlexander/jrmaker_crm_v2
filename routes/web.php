<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\PasswordChangeController;
use App\Http\Controllers\PaymentWebhookController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\WompiController;
use App\Http\Controllers\WompiWebhookController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Aquí es donde puedes registrar las rutas web para tu aplicación. Estas
| rutas están cargadas por el RouteServiceProvider y todas se asignan
| al grupo de middleware "web". 
|
*/

// Ruta para la página de bienvenida
Route::get('/', function () {
    return redirect()->to('https://www.jrmaker.com.co');
});

/*
|--------------------------------------------------------------------------
| Rutas para el cambio y verificación de contraseñas
|--------------------------------------------------------------------------
*/

// Ruta para verificar el cambio de contraseña
Route::get('/verify-password-change', [PasswordChangeController::class, 'verify'])->name('password.change.verify');

// Ruta para verificar el código de cambio de contraseña
Route::post('/verify-password-change', [PasswordChangeController::class, 'verifyCode'])->name('password.change.verify_code');

/*
|--------------------------------------------------------------------------
| Rutas de integración con la pasarela de pago wompi
|--------------------------------------------------------------------------
*/


Route::get('/payment/callback', [WompiController::class, 'handleRedirect'])->name('payment.callback');

Route::post('/wompi/webhook', [WompiWebhookController::class, 'handle']);

/*
|--------------------------------------------------------------------------
| Rutas para la verificación de correos electrónicos
|--------------------------------------------------------------------------
*/

// Página de notificación de verificación
Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

// Verificar el correo electrónico
Route::get('/email/verify/{id}/{hash}', [EmailVerificationRequest::class, '__invoke'])
    ->middleware(['auth', 'signed'])->name('verification.verify');

// Reenviar el correo de verificación
Route::post('/email/resend', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with('message', 'Verification link sent!');
})->middleware(['auth', 'throttle:6,1'])->name('verification.resend');
