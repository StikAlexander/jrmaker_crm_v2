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
            // 1. Verificar si el payment tiene un transaction_id
            if (!empty($this->payment->transaction_id)) {
                // Hacer la solicitud a la API de Wompi para verificar el estado de la transacción
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . env('WOMPI_PRIVATE_KEY'),
                    'Content-Type' => 'application/json',
                ])->get(env('WOMPI_ENV') === 'production' ? 'https://production.wompi.co/v1/transactions/' . $this->payment->transaction_id : 'https://sandbox.wompi.co/v1/transactions/' . $this->payment->transaction_id);

                Log::info('Respuesta completa de Wompi:', $response->json());

                if ($response->successful()) {
                    $transactionData = $response->json()['data'];

                    // Manejar el estado de la transacción
                    $status = $transactionData['status'];
                    switch ($status) {
                        case 'APPROVED':
                            $this->payment->update(['payment_status' => 'Completed']);
                            break;
                        case 'DECLINED':
                        case 'CANCELLED':
                            $this->payment->update(['payment_status' => 'Cancelled']);
                            break;
                        case 'PENDING':
                            // Si sigue en estado PENDING y ha pasado el tiempo de expiración, cancela el pago
                            if (now()->greaterThan($this->payment->created_at->addMinutes(3))) {
                                $this->payment->update(['payment_status' => 'Cancelled']);
                                Log::info('Pago cancelado por expiración del link. Payment ID: ' . $this->payment->id);
                            }
                            break;
                        default:
                            Log::warning('Estado inesperado en Wompi: ' . $status);
                            break;
                    }
                } else {
                    Log::error('Error al consultar el estado de la transacción en Wompi. Respuesta: ' . $response->body());
                }
            } 
            // 2. Si no hay transaction_id, manejar como transacción abandonada
            else {
                Log::warning('El pago no tiene un transaction_id. Verificando el link de pago para Payment ID: ' . $this->payment->id);
                
                // Verificar si el link ha expirado y cancelar el pago
                if (now()->greaterThan($this->payment->created_at->addMinutes(3))) {
                    $this->payment->update(['payment_status' => 'Cancelled']);
                    Log::info('Pago cancelado por expiración del link. Payment ID: ' . $this->payment->id);
                }
            }
        } catch (\Exception $e) {
            Log::error('Excepción en el Job CheckWompiPaymentStatus: ' . $e->getMessage());
        }
    }
}
