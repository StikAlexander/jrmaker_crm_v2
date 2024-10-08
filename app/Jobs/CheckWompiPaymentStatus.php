<?php

namespace App\Jobs;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CheckWompiPaymentStatus implements ShouldQueue
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
            // Verificamos si el pago sigue en estado "Pending"
            if ($this->payment->payment_status === 'Pending') {
                // Verificar si el link de pago ha expirado
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . env('WOMPI_PRIVATE_KEY'),
                ])->get(env('WOMPI_ENV') === 'production' ? 'https://production.wompi.co/v1/payment_links/' . $this->payment->payment_link_id : 'https://sandbox.wompi.co/v1/payment_links/' . $this->payment->payment_link_id);

                Log::info('Respuesta completa de Wompi:', $response->json());

                if ($response->successful()) {
                    $paymentLinkData = $response->json()['data'];

                    // Si el link ha expirado
                    if (Carbon::parse($paymentLinkData['expires_at'])->isPast()) {
                        $this->payment->update(['payment_status' => 'Cancelled']);
                        Log::info('El link ha expirado. Pago cancelado. Payment ID: ' . $this->payment->id);
                    }
                } else {
                    Log::error('Error al consultar el link de pago en Wompi. Respuesta: ' . $response->body());
                }
            }
        } catch (\Exception $e) {
            Log::error('Excepción en el Job CheckWompiPaymentStatus: ' . $e->getMessage());
        }
    }
}
