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

    public function generatePaymentLink($Payment, $callbackUrl)
    {
        try {
            $client = new PreferenceClient();
    
            // Preparar los items basados en las facturas
            $preferenceItems = $Payment->invoices->map(function ($invoice) {
                return [
                    "title" => "Factura " . $invoice->invoice_number,
                    "quantity" => 1,
                    "unit_price" => (float) $invoice->pending_amount,
                ];
            })->toArray();

            // Aquí defines los datos del pagador
            $payer = [
                "name" => "Test",  // Puedes poner datos reales o dinámicos aquí
                "surname" => "User",
                "email" => "test_user@example.com",  // Asegúrate de obtener un email válido
            ];

            // Crear la preferencia de pago
            $preferenceRequest = [
                "items" => $preferenceItems,
                "payer" => $payer,
                "back_urls" => [
                    'success' => 'https://a031-200-118-80-78.ngrok-free.app/payment/success',
                    'failure' => 'https://a031-200-118-80-78.ngrok-free.app/payment/failure',
                    'pending' => 'https://a031-200-118-80-78.ngrok-free.app/payment/pending',
                ],
                "auto_return" => 'approved',
                "notification_url" => 'https://a031-200-118-80-78.ngrok-free.app/payment/callback',
                "external_reference" => $Payment->id,
                "expiration_date_from" => now()->format("Y-m-d\TH:i:s.000P"),
                "expiration_date_to" => now()->addMinutes(2)->format("Y-m-d\TH:i:s.000P"),
            ];

            Log::info('Datos enviados a MercadoPago:', $preferenceRequest);
    
            // Enviar la solicitud a MercadoPago
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
