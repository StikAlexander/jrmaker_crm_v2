<?php

namespace App\Services;

use Carbon\Carbon;
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

            $externalReference = substr(time(), -5) . rand(10, 99);
            $Payment->update(['external_reference' => $externalReference]);
    
            // Preparar los items basados en las facturas
            $preferenceItems = $Payment->invoices->map(function ($invoice) {
                return [
                    "title" => "Factura " . $invoice->invoice_number,
                    "quantity" => 1,
                    "unit_price" => (float) $invoice->pending_amount,
                ];
            })->toArray();

            // Datos del pagador
            $payer = [
                "name" => "Test",  
                "surname" => "User",
                "email" => "test_user@example.com",  
            ];

            // Crear la preferencia de pago
            $preferenceRequest = [
                "items" => $preferenceItems,
                "payer" => $payer,
                "back_urls" => [
                    'success' => 'https://c24a-200-118-80-78.ngrok-free.app/payment/success',
                    'failure' => 'https://c24a-200-118-80-78.ngrok-free.app/payment/failure',
                    'pending' => 'https://c24a-200-118-80-78.ngrok-free.app/payment/pending',
                ],
                "auto_return" => 'approved',
                "notification_url" => 'https://c24a-200-118-80-78.ngrok-free.app/payment/callback',
                "external_reference" => $Payment->id,
                "expires" => true,
                "expiration_date_from" => now()->format("Y-m-d\TH:i:s.000P"),
                "expiration_date_to" => now()->addMinutes(2)->format("Y-m-d\TH:i:s.000P"),
            ];

            Log::info('Datos enviados a MercadoPago:', $preferenceRequest);

            // Enviar la solicitud a MercadoPago
            $preference = $client->create($preferenceRequest);

            // Verificar si se generaron correctamente los datos antes de guardarlos
            Log::info('Fechas para guardar:', [
                'expiration_date_from' => Carbon::parse($preferenceRequest['expiration_date_from'])->toDateTimeString(),
                'expiration_date_to' => Carbon::parse($preferenceRequest['expiration_date_to'])->toDateTimeString(),
            ]);

            // Guardar el preference_id, expiration_date_from y expiration_date_to en la tabla de pagos
            $Payment->update([
                'preference_id' => $preference->id,
                'expiration_date_from' => Carbon::parse($preferenceRequest['expiration_date_from'])->toDateTimeString(),
                'expiration_date_to' => Carbon::parse($preferenceRequest['expiration_date_to'])->toDateTimeString(),
            ]);

            Log::info('Datos actualizados en la base de datos:', $Payment->toArray());
            
            // Retornar el init_point para redirigir al cliente
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
