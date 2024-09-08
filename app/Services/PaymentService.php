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
        // Inicializar el SDK de Mercado Pago con tu token de acceso
        $mpAccessToken = config('services.mercadopago.access_token');
        //dd($mpAccessToken);

        if (empty($mpAccessToken)) {
            throw new \Exception("El token de acceso de Mercado Pago no está configurado.");
        }
        
        MercadoPagoConfig::setAccessToken($mpAccessToken);
    }

    public function generatePaymentLink($amount, $description, $items, $callbackUrl, $externalReference)
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
                    "unit_price" => (float)$invoice->pending_amount,
                ];
            }
    
            // Crear la preferencia de pago, incluyendo external_reference
            $preferenceRequest = [
                "items" => $preferenceItems,
                "back_urls" => [
                    'success' => $callbackUrl . "/success",
                    'failure' => $callbackUrl . "/failure",
                    'pending' => $callbackUrl . "/pending",
                ],
                "auto_return" => 'approved',
                "notification_url" => "https://978d-200-118-80-78.ngrok-free.app/payment/callback",
                "external_reference" => $externalReference,
            ];
    
            // Registrar los datos enviados
            Log::info('Datos enviados a MercadoPago:', $preferenceRequest);
    
            // Crear la preferencia de pago en MercadoPago
            $preference = $client->create($preferenceRequest);
    
            // Retornar el enlace de pago
            return $preference->init_point;
    
        } catch (MPApiException $e) {
            // Registrar detalles del error de la API
            if ($e->getApiResponse()) {
                Log::error('Error al generar el enlace de pago: ', [
                    'status_code' => $e->getApiResponse()->getStatusCode(),
                    'error_content' => $e->getApiResponse()->getContent(),
                ]);
            } else {
                Log::error('Error al generar el enlace de pago: ', [
                    'message' => $e->getMessage(),
                    'code' => $e->getCode(),
                ]);
            }
            return null;
        }
    }         
}
