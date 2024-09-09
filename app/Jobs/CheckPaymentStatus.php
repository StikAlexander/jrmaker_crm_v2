<?php

namespace App\Jobs;

use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class CheckPaymentStatus implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $payment;

    public function __construct(Payment $payment)
    {
        $this->payment = $payment;
    }

    public function handle()
    {
        try {
            // Verificar si el estado es Pending y que expiration_date_to no sea null
            if ($this->payment->payment_status === 'Pending' && $this->payment->expiration_date_to) {
                $client = new \GuzzleHttp\Client();
                $response = $client->request('GET', "https://api.mercadopago.com/checkout/preferences/{$this->payment->preference_id}", [
                    'headers' => [
                        'Authorization' => 'Bearer ' . config('services.mercadopago.access_token'),
                    ],
                ]);
    
                $preferenceData = json_decode($response->getBody()->getContents(), true);
    
                // Solo actualizar si la fecha de expiración ha pasado
                if (now()->greaterThan(Carbon::parse($this->payment->expiration_date_to))) {
                    $this->payment->update(['payment_status' => 'Cancelled']);
                }
            }
        } catch (\Exception $e) {
            Log::error('Error en CheckPaymentStatus Job: ' . $e->getMessage());
        }
    }      
}
