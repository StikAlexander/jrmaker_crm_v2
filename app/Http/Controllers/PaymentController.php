<?php

namespace App\Http\Controllers;

use MercadoPago\SDK;
use MercadoPago\Preference;
use App\Models\VoucherPayment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function createPaymentLink(VoucherPayment $voucherPayment)
    {
        // Inicializar el SDK con las credenciales
        SDK::setAccessToken(config('services.mercadopago.access_token'));

        // Crear una preferencia de pago
        $preference = new Preference();
        $preference->items = [
            [
                'title' => 'Pago de facturas',
                'quantity' => 1,
                'unit_price' => $voucherPayment->amount,
                'currency_id' => 'COP',
            ]
        ];
        
        // URLs de redirección
        $preference->back_urls = [
            'success' => route('payment.success'),
            'failure' => route('payment.failure'),
            'pending' => route('payment.pending'),
        ];

        $preference->auto_return = 'approved';

        // Guardar la preferencia y obtener el link
        $preference->save();

        // Guardar el link de pago en el modelo VoucherPayment
        $voucherPayment->update([
            'payment_link' => $preference->init_point,
        ]);

        return redirect($preference->init_point); // Redirigir al cliente a MercadoPago
    }

    // Métodos para manejar las redirecciones de éxito, fallo y pendiente
    public function handleSuccess(Request $request)
    {
        // Lógica para actualizar el estado del pago y mostrar un mensaje de éxito
        return view('payment.success');
    }

    public function handleFailure(Request $request)
    {
        // Lógica para manejar el fallo del pago
        return view('payment.failure');
    }

    public function handlePending(Request $request)
    {
        // Lógica para manejar un pago pendiente
        return view('payment.pending');
    }
}
