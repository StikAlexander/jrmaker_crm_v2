<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payment;
use Illuminate\Support\Facades\Log;

class WompiWebhookController extends Controller
{
    /**
     * Valida la firma del webhook de Wompi.
     * Nota: Esta es una implementación básica, actualízala según las especificaciones de Wompi.
     *
     * @param Request $request
     * @return bool
     */
    protected function validateWebhookSignature(Request $request)
    {
        // Implementación específica para validar firmas según la documentación de Wompi
        // Por ahora, permitiremos todos los webhooks (en producción, implementa la validación real)
        return true;
    }

    /**
     * Sanitiza los datos de la solicitud para registro seguro.
     *
     * @param array $data
     * @return array
     */
    protected function sanitizeRequestData(array $data)
    {
        // Eliminar información sensible si existe
        if (isset($data['data']['transaction']['payment_method'])) {
            if (isset($data['data']['transaction']['payment_method']['card'])) {
                $data['data']['transaction']['payment_method']['card'] = '[REDACTED]';
            }
        }
        
        return $data;
    }
    
    /**
     * Extrae y valida los datos de la transacción del webhook.
     *
     * @param Request $request
     * @return array|null
     */
    protected function extractTransactionData(Request $request)
    {
        // Extraer datos de la solicitud
        $paymentLinkId = $request->input('data.transaction.payment_link_id');
        $status = $request->input('data.transaction.status');
        $transactionId = $request->input('data.transaction.id');
        
        // Validación básica de datos requeridos
        if (!$paymentLinkId || !$status || !$transactionId) {
            Log::warning('Datos incompletos en el webhook', [
                'paymentLinkId' => $paymentLinkId ? 'present' : 'missing',
                'status' => $status ? 'present' : 'missing',
                'transactionId' => $transactionId ? 'present' : 'missing',
            ]);
            return null;
        }
        
        // Extraer el resto de los datos
        $reference = $request->input('data.transaction.reference');
        $amountInCents = $request->input('data.transaction.amount_in_cents');
        $amount = $amountInCents ? $amountInCents / 100 : 0;
        $paymentMethodType = $request->input('data.transaction.payment_method_type');
        $apiResponse = $this->sanitizeRequestData($request->all());
        
        return [
            'paymentLinkId' => $paymentLinkId,
            'status' => $status,
            'transactionId' => $transactionId,
            'reference' => $reference,
            'amount' => $amount,
            'paymentMethodType' => $paymentMethodType,
            'apiResponse' => $apiResponse,
        ];
    }
    /**
     * Procesa los webhooks de Wompi.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handle(Request $request)
    {
        try {
            // Validar la firma del webhook si está disponible
            if (!$this->validateWebhookSignature($request)) {
                Log::warning('Firma de webhook inválida', ['ip' => $request->ip()]);
                return response()->json(['status' => 'invalid_signature'], 403);
            }
            
            // Registrar la respuesta completa del webhook para fines de depuración
            Log::info('Webhook recibido:', [
                'ip' => $request->ip(),
                'data' => $this->sanitizeRequestData($request->all())
            ]);

            // Extraer y validar los datos de la transacción
            $data = $this->extractTransactionData($request);
            
            if (!$data) {
                return response()->json(['status' => 'invalid_data'], 400);
            }
            
            // Extraer los datos validados
            ['paymentLinkId' => $paymentLinkId, 
             'status' => $status, 
             'transactionId' => $transactionId, 
             'reference' => $reference, 
             'amount' => $amount, 
             'paymentMethodType' => $paymentMethodType,
             'apiResponse' => $apiResponse] = $data;

            // Verificar si se recibió el ID del link de pago
            if ($paymentLinkId) {
                // Buscar el pago relacionado usando el ID del link de pago
                $payment = Payment::where('payment_link_id', $paymentLinkId)->first();

                if ($payment) {
                    // Evitar reprocesar la misma transacción más de una vez
                    if ($payment->transaction_id === $transactionId) {
                        Log::info('Transacción ya procesada: ' . $transactionId);
                        return response()->json(['status' => 'success'], 200);
                    }

                    // Actualizar el estado del pago y otros detalles relevantes
                    $payment->update([
                        'payment_status' => match ($status) {
                            'APPROVED' => 'Completed',
                            'DECLINED' => 'Declined',
                            'CANCELLED' => 'Cancelled',
                            'ERROR' => 'Error',
                            'VOIDED' => 'Voided',
                            default => 'Pending',
                        },
                        'transaction_id' => $transactionId,
                        'amount' => $amount,
                        'reference' => $reference,
                        'payment_method_type' => $paymentMethodType,
                        'api_response' => json_encode($apiResponse),
                    ]);

                    Log::info('Webhook procesado correctamente', [
                        'payment_link_id' => $paymentLinkId,
                        'status' => $status,
                    ]);
                    return response()->json(['status' => 'success'], 200);
                } else {
                    Log::warning('Pago no encontrado para el ID del link de pago: ' . $paymentLinkId);
                    return response()->json(['status' => 'not_found'], 404);
                }
            } else {
                Log::warning('No se encontró el ID del link de pago en la respuesta del webhook.');
                return response()->json(['status' => 'invalid_data'], 400);
            }

        } catch (\Exception $e) {
            Log::error('Error al procesar el webhook de Wompi: ' . $e->getMessage());
            return response()->json(['status' => 'error'], 500);
        }
    }
}
