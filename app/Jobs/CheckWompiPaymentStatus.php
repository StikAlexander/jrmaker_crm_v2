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

class CheckWompiPaymentStatus implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $payment;

    /**
     * Create a new job instance.
     *
     * @param Payment $payment
     */
    public function __construct(Payment $payment)
    {
        $this->payment = $payment;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            // Verifica que el estado actual del pago sea "Pending"
            if ($this->payment->payment_status === 'Pending') {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . env('WOMPI_PRIVATE_KEY'),
                    'Content-Type' => 'application/json',
                ])->get(env('WOMPI_ENV') === 'production' ? 'https://production.wompi.co/v1/transactions/' : 'https://sandbox.wompi.co/v1/transactions/', [
                    'reference' => $this->payment->reference
                ]);

                if ($response->successful()) {
                    $transactionData = $response->json();

                    // Evaluar el estado de la transacción
                    $status = $transactionData['data']['status'];
                    if ($status === 'APPROVED') {
                        $this->payment->update(['payment_status' => 'Completed']);
                    } elseif ($status === 'DECLINED' || $status === 'CANCELLED') {
                        $this->payment->update(['payment_status' => 'Cancelled']);
                    } elseif ($status === 'PENDING' && now()->greaterThan($this->payment->created_at->addMinutes(2))) {
                        // Si el link ha expirado, cancela el pago
                        $this->payment->update(['payment_status' => 'Cancelled']);
                        Log::info('Pago cancelado debido a que el link ha expirado. Payment ID: ' . $this->payment->id);
                    }
                } else {
                    Log::error('Error al consultar el estado de la transacción en Wompi. Respuesta: ' . $response->body());
                }
            }
        } catch (\Exception $e) {
            Log::error('Excepción en el Job CheckWompiPaymentStatus: ' . $e->getMessage());
        }
    }
}
