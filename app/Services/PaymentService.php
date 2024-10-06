<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    protected $privateKey;
    protected $url;

    public function __construct()
    {
        // Carga las llaves de configuración desde el archivo .env
        $this->privateKey = env('WOMPI_PRIVATE_KEY');
        $this->url = env('WOMPI_ENV') === 'production' ? 'https://production.wompi.co/v1' : 'https://sandbox.wompi.co/v1';
    }

    public function generatePaymentLink($payment, $callbackUrl)
    {
        try {
            $transactionData = [
                'name' => 'Pago de Facturas - Cliente ' . $payment->client->name,
                'description' => 'Pago de facturas seleccionadas',
                'amount_in_cents' => $payment->amount * 100,
                'currency' => 'COP',
                'single_use' => true,
                'expires_at' => now()->addMinutes(2)->toISOString(),
                'redirect_url' => $callbackUrl,
                'collect_shipping' => false,
            ];
    
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->privateKey,
                'Content-Type' => 'application/json',
            ])->post($this->url . '/payment_links', $transactionData);
    
            if ($response->successful()) {
                $paymentLinkData = $response->json()['data'];
    
                $payment->update([
                    'payment_link_id' => $paymentLinkData['id'],
                    'reference' => $paymentLinkData['id'] . '_custom_reference_suffix', // Ajustar esta lógica según la necesidad
                    'payment_link' => 'https://checkout.wompi.co/l/' . $paymentLinkData['id'],
                ]);
                
    
                return $payment->payment_link;
            } else {
                Log::error('Error al crear el link de pago en Wompi:', [
                    'response_status' => $response->status(),
                    'response_body' => $response->body(),
                ]);
                return null;
            }
        } catch (\Exception $e) {
            Log::error('Excepción al crear el link de pago en Wompi:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }
    
}
