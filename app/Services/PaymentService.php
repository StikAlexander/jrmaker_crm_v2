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

    public function generatePaymentLink($amount, $description, $items, $callbackUrl)
    {
        try {
            $client = new PreferenceClient();
            
            // Preparar el array de items
            $preferenceItems = [];
            foreach ($items as $invoice) {
                $preferenceItems[] = [
                    "title" => "Factura " . $invoice->invoice_number,
                    "quantity" => 1,
                    "unit_price" => $invoice->pending_amount,
                ];
            }
            
            // Crear la preferencia de pago incluyendo external_reference
            $preferenceRequest = [
                "items" => $preferenceItems,
                "back_urls" => [
                    'success' => route('payment.success'),
                    'failure' => route('payment.failure'),
                    'pending' => route('payment.pending')
                ],
                "auto_return" => 'approved',
                "notification_url" => $callbackUrl, // URL del webhook
                "external_reference" => $items->first()->voucher_payment_id, // Pasar el ID de VoucherPayment o cualquier otra referencia
            ];
    
            // Crear la preferencia de pago en MercadoPago
            $preference = $client->create($preferenceRequest);
    
            return $preference->init_point;
        } catch (MPApiException $e) {
            Log::error('Error al generar el enlace de pago: ', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'response' => $e->getResponseBody(),
            ]);
            return null;
        }
    }  
}
