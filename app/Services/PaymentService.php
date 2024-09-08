<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\Exceptions\MPApiException;

class PaymentService
{
    public function __construct()
    {
        $mpAccessToken = config('services.mercadopago.access_token');

        if (empty($mpAccessToken)) {
            throw new \Exception("El token de acceso de Mercado Pago no está configurado.");
        }
        
        MercadoPagoConfig::setAccessToken($mpAccessToken);
    }

    public function generatePaymentLink($voucherPayment, $callbackUrl)
    {
        try {
            $client = new PreferenceClient();
    
            // Preparar el array de items basados en el voucher payment
            $preferenceItems = $voucherPayment->invoices->map(function ($invoice) {
                return [
                    "title" => "Factura " . $invoice->invoice_number,
                    "quantity" => 1,
                    "unit_price" => (float) $invoice->pending_amount,
                ];
            })->toArray();
    
            // Crear la preferencia de pago
            $preferenceRequest = [
                "items" => $preferenceItems,
                "back_urls" => [
                    'success' => "{$callbackUrl}/success",
                    'failure' => "{$callbackUrl}/failure",
                    'pending' => "{$callbackUrl}/pending",
                ],
                "auto_return" => 'approved',
                // Obtener la URL del webhook desde el archivo .env o utilizar una URL por defecto
                "notification_url" => env('MERCADOPAGO_NOTIFICATION_URL', 'https://978d-200-118-80-78.ngrok-free.app/payment/callback'),
                "external_reference" => $voucherPayment->id,
            ];
    
            Log::info('Datos enviados a MercadoPago:', $preferenceRequest);
    
            $preference = $client->create($preferenceRequest);
    
            return $preference->init_point ?? null;
        } catch (MPApiException $e) {
            Log::error('Error al generar el enlace de pago: ', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);
            return null;
        }
    }
}
