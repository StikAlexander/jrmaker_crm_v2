<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\PasswordChangeController;
use App\Http\Controllers\PaymentWebhookController;
use App\Http\Controllers\PaymentController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Aquí es donde puedes registrar las rutas web para tu aplicación. Estas
| rutas están cargadas por el RouteServiceProvider y todas se asignan
| al grupo de middleware "web". ¡Crea algo grandioso!
|
*/

// Ruta para la página de bienvenida
Route::get('/', function () {
    return view('welcome');
});

// Rutas para cambiar y verificar contraseñas
Route::get('/verify-password-change', [PasswordChangeController::class, 'verify'])->name('password.change.verify');
Route::post('/verify-password-change', [PasswordChangeController::class, 'verifyCode'])->name('password.change.verify_code');

/*
|--------------------------------------------------------------------------
| Rutas de integración con la pasarela de pago
|--------------------------------------------------------------------------
*/

// Ruta para manejar el webhook de MercadoPago (notificaciones automáticas)
Route::post('/mercadopago/webhook', [PaymentWebhookController::class, 'handleCallback'])->name('mercadopago.webhook');

// Ruta para manejar el callback de la pasarela de pagos (redirección del usuario)
Route::post('/payment/callback', [PaymentWebhookController::class, 'handleCallback'])->name('payment.callback');

// Ruta de éxito: cuando el pago se completa con éxito
Route::get('/payment/success', [PaymentController::class, 'handleSuccess'])->name('payment.success');

// Ruta de fallo: si el pago ha fallado
Route::get('/payment/failure', [PaymentController::class, 'handleFailure'])->name('payment.failure');

// Ruta de estado pendiente: cuando el pago está pendiente de confirmación
Route::get('/payment/pending', [PaymentController::class, 'handlePending'])->name('payment.pending');


