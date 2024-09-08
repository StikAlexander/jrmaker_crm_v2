<?php

namespace App\Services;

use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\Exceptions\MPApiException;

class PaymentService
{
    public function __construct()
    {
        // Inicializar el SDK de Mercado Pago con tu token de acceso
        $mpAccessToken = config('services.mercadopago.access_token');
        //dd($mpAccessToken);

        if (empty($mpAccessToken)) {
            throw new \Exception("El token de acceso de Mercado Pago no está configurado.");
        }
        
        MercadoPagoConfig::setAccessToken($mpAccessToken);
    }

    public function generatePaymentLink($amount, $description, $items, $callbackUrl)
    {
        try {
            // Crear un cliente de preferencias
            $client = new PreferenceClient();

            // Preparar el array de items
            $preferenceItems = [];
            foreach ($items as $invoice) {
                $preferenceItems[] = [
                    "title" => "Factura " . $invoice->invoice_number,
                    "quantity" => 1,
                    "unit_price" => $invoice->pending_amount
                ];
            }

            // Crear la preferencia de pago
            $preferenceRequest = [
                "items" => $preferenceItems,
                "back_urls" => [
                    'success' => route('payment.success'),
                    'failure' => route('payment.failure'),
                    'pending' => route('payment.pending')
                ],
                "auto_return" => 'approved',
                "notification_url" => "https://9893-200-118-80-78.ngrok-free.app/payment/callback", // Aquí la URL del webhook
            ];

            \Log::info("Datos enviados a Mercado Pago:", $preferenceRequest);

            // Generar la preferencia de pago
            $preference = $client->create($preferenceRequest);

            // Retornar el enlace de pago
            return $preference->init_point;

        } catch (MPApiException $e) {
            // Manejar el error y registrar en logs
            \Log::error('Error al generar el enlace de pago: ', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'response' => $e->getResponseBody(),
            ]);
            return null;
        }
    }
}
