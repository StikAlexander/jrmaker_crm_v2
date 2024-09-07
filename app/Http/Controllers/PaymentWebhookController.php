<?php

namespace App\Http\Controllers;

use App\Models\VoucherPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentWebhookController extends Controller
{
    /**
     * Maneja el callback de la API de pago.
     */
    public function handleCallback(Request $request)
    {
        Log::info('Recibiendo callback de la API de pago: ', $request->all());
    
        try {
            // Validar la estructura de la respuesta de la API de pago
            $paymentId = $request->input('payment_id');
            $paymentStatus = $request->input('status');
    
            if (!$paymentId || !$paymentStatus) {
                throw new \Exception('Callback de pago inválido, faltan campos.');
            }
    
            // Buscar el pago por su enlace
            $voucherPayment = VoucherPayment::where('payment_link', $request->input('payment_link'))->first();
            if (!$voucherPayment) {
                throw new \Exception('Pago no encontrado para el enlace proporcionado.');
            }
    
            // Actualizar el estado del pago
            $this->updatePaymentStatus($voucherPayment, $paymentStatus, $request->all());
    
            return response()->json(['message' => 'Estado de pago actualizado correctamente']);
    
        } catch (\Exception $e) {
            // Loguear el error
            Log::error('Error en el callback de pago: ' . $e->getMessage());
    
            // Responder con un error a la API
            return response()->json(['message' => 'Error al procesar el callback'], 500);
        }
    }
    
    private function updatePaymentStatus(VoucherPayment $voucherPayment, $status, $apiResponse)
    {
        switch ($status) {
            case 'Completed':
                $voucherPayment->update([
                    'payment_status' => 'Completed',
                    'api_response' => json_encode($apiResponse),
                ]);
                break;
    
            case 'Failed':
                $voucherPayment->update([
                    'payment_status' => 'Failed',
                    'api_response' => json_encode($apiResponse),
                ]);
                break;
    
            case 'Cancelled':
                $voucherPayment->update([
                    'payment_status' => 'Cancelled',
                    'api_response' => json_encode($apiResponse),
                ]);
                break;
    
            default:
                $voucherPayment->update([
                    'payment_status' => 'Pending',
                    'api_response' => json_encode($apiResponse),
                ]);
                break;
        }
    }    
}
