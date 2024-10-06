<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payment;
use Illuminate\Support\Facades\Log;

class WompiWebhookController extends Controller
{
    public function handle(Request $request)
    {
        try {
            // Registrar la respuesta completa del webhook para fines de depuración
            Log::info('Webhook recibido:', [
                'response' => $request->all()
            ]);

            // Obtener los datos de la transacción desde el webhook
            $paymentLinkId = $request->input('data.transaction.payment_link_id');
            $status = $request->input('data.transaction.status');
            $transactionId = $request->input('data.transaction.id');
            $reference = $request->input('data.transaction.reference');
            $amount = $request->input('data.transaction.amount_in_cents') / 100; // Convertir de centavos a la moneda real
            $paymentMethodType = $request->input('data.transaction.payment_method_type');
            $apiResponse = $request->all(); // Guardar la respuesta completa de la API

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
