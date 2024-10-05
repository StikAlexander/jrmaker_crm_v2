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
            // Registrar la respuesta del webhook para revisión
            Log::info('Webhook recibido:', [
                'response' => $request->all()
            ]);

            // Obtener el ID del link de pago, el estado de la transacción y otros detalles
            $paymentLinkId = $request->input('data.transaction.payment_link_id');
            $status = $request->input('data.transaction.status');
            $statusMessage = $request->input('data.transaction.status_message'); // Obtener el mensaje de estado
            $returnCode = $request->input('data.transaction.payment_method.extra.return_code'); // Obtener el código de retorno
            $reference = $request->input('data.transaction.reference'); // Obtener la referencia de la transacción
            $transactionId = $request->input('data.transaction.id'); // Obtener la ID de la transacción

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

                    // Actualizar el estado del pago según el estado de la transacción
                    switch ($status) {
                        case 'APPROVED':
                            $payment->update([
                                'payment_status' => 'Completed',
                                'status_message' => $statusMessage,
                                'return_code' => $returnCode,
                                'reference' => $reference,
                                'transaction_id' => $transactionId, // Guardar la transacción procesada
                            ]);
                            break;
                        case 'DECLINED':
                            $payment->update([
                                'payment_status' => 'Declined',
                                'status_message' => $statusMessage,
                                'return_code' => $returnCode,
                                'reference' => $reference,
                                'transaction_id' => $transactionId, // Guardar la transacción procesada
                            ]);
                            break;
                        case 'CANCELLED':
                            $payment->update([
                                'payment_status' => 'Cancelled',
                                'status_message' => $statusMessage,
                                'return_code' => $returnCode,
                                'reference' => $reference,
                                'transaction_id' => $transactionId, // Guardar la transacción procesada
                            ]);
                            break;
                        case 'ERROR':
                            $payment->update([
                                'payment_status' => 'Error',
                                'status_message' => $statusMessage,
                                'return_code' => $returnCode,
                                'reference' => $reference,
                                'transaction_id' => $transactionId, // Guardar la transacción procesada
                            ]);
                            break;
                        default:
                            $payment->update([
                                'payment_status' => 'Pending',
                                'status_message' => $statusMessage,
                                'return_code' => $returnCode,
                                'reference' => $reference,
                                'transaction_id' => $transactionId, // Guardar la transacción procesada
                            ]);
                            break;
                    }

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
