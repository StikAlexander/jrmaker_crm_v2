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

    /**
     * Número máximo de intentos
     */
    public $tries = 3;
    
    /**
     * Tiempo de espera antes de marcar un trabajo como fallido
     */
    public $timeout = 60;
    
    public function handle()
    {
        try {
            // Verificar si el pago aún existe en la base de datos
            $payment = Payment::find($this->payment->id);
            if (!$payment) {
                Log::warning('Pago no encontrado en la base de datos. ID: ' . $this->payment->id);
                return;
            }
            
            // Recargar el modelo para asegurar datos actualizados
            $this->payment->refresh();
            
            // Verificamos si el pago sigue en estado "Pending"
            if ($this->payment->payment_status === 'Pending') {
                // Verificar si tenemos un payment_link_id
                if (empty($this->payment->payment_link_id)) {
                    Log::warning('Pago sin payment_link_id. Payment ID: ' . $this->payment->id);
                    return;
                }
                
                $apiUrl = env('WOMPI_ENV') === 'production' 
                    ? 'https://production.wompi.co/v1/payment_links/' . $this->payment->payment_link_id 
                    : 'https://sandbox.wompi.co/v1/payment_links/' . $this->payment->payment_link_id;
                    
                // Verificar si el link de pago ha expirado
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . env('WOMPI_PRIVATE_KEY'),
                ])->get($apiUrl);

                Log::info('Verificando estado de pago en Wompi. Payment ID: ' . $this->payment->id);
                
                if ($response->successful()) {
                    $paymentLinkData = $response->json()['data'];
                    Log::info('Respuesta de Wompi:', ['payment_id' => $this->payment->id, 'data' => $paymentLinkData]);

                    // Si el link ha expirado
                    if (Carbon::parse($paymentLinkData['expires_at'])->isPast()) {
                        $this->payment->update(['payment_status' => 'Cancelled']);
                        Log::info('El link ha expirado. Pago cancelado. Payment ID: ' . $this->payment->id);
                    }
                    
                    // Verificar si hay transacciones asociadas
                    if (isset($paymentLinkData['transactions']) && !empty($paymentLinkData['transactions'])) {
                        $transaction = $paymentLinkData['transactions'][0];
                        
                        // Actualizar el estado del pago según la transacción
                        $paymentStatus = match ($transaction['status']) {
                            'APPROVED' => 'Completed',
                            'DECLINED' => 'Declined',
                            'VOIDED', 'CANCELLED' => 'Cancelled',
                            'ERROR' => 'Error',
                            default => $this->payment->payment_status,
                        };
                        
                        if ($paymentStatus !== $this->payment->payment_status) {
                            $this->payment->update([
                                'payment_status' => $paymentStatus,
                                'transaction_id' => $transaction['id'],
                                'api_response' => json_encode($response->json()),
                            ]);
                            
                            Log::info('Estado del pago actualizado. Payment ID: ' . $this->payment->id . ', Nuevo estado: ' . $paymentStatus);
                        }
                    }
                } else {
                    Log::error('Error al consultar el link de pago en Wompi. Payment ID: ' . $this->payment->id . ', Respuesta: ' . $response->body());
                }
            }
        } catch (\Exception $e) {
            Log::error('Excepción en el Job CheckWompiPaymentStatus: ' . $e->getMessage(), [
                'payment_id' => $this->payment->id,
                'trace' => $e->getTraceAsString()
            ]);
            
            // Si hay error, reintentamos el job
            $this->release(60); // Reintentar después de 1 minuto
        }
    }
}
