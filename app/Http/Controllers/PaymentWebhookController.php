<?php

namespace App\Http\Controllers;

use App\Models\VoucherPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentWebhookController extends Controller
{
    public function handleCallback(Request $request)
    {
        $secretKey = config('mercadopago.secret_key');
        $signature = $request->header('x-mp-signature');
    
        // Verifica si la firma está presente
        if (!$signature) {
            Log::error('Firma no proporcionada en el encabezado x-mp-signature.');
            return response()->json(['message' => 'Firma no proporcionada.'], 400);
        }
    
        // Registrar el contenido crudo del payload y la firma
        $rawPayload = json_encode($request->all(), JSON_UNESCAPED_SLASHES);
        Log::info('Contenido del payload (API):', [$rawPayload]);
        //Log::info('Contenido del payload:', [$rawPayload]);
        Log::info('Firma recibida:', [$signature]);
    
        $expectedSignature = trim(hash_hmac('sha256', $rawPayload, $secretKey));
        Log::info('Firma esperada:', [$expectedSignature]);
        
        $signature = trim($signature);
        Log::info('Firma recibida:', [$signature]);
        
        if (!hash_equals($expectedSignature, $signature)) {
            Log::warning('Firma no válida. Posible intento de falsificación.');
            return response()->json(['message' => 'Firma inválida.'], 403);
        }
        
        
        // Continuar con el procesamiento del webhook
        Log::info('Callback recibido de la API de Mercado Pago:', $request->all());
    
        try {
            // Extraer datos del webhook
            $paymentId = $request->input('data.id');
            $paymentStatus = $request->input('data.status');
            $externalReference = $request->input('data.external_reference');
    
            if (!$paymentId || !$paymentStatus) {
                throw new \Exception('Callback de pago inválido, faltan campos.');
            }
    
            // Buscar el pago en la base de datos usando la external_reference
            $voucherPayment = VoucherPayment::where('external_reference', $externalReference)->first();
            if (!$voucherPayment) {
                throw new \Exception('Pago no encontrado para la referencia proporcionada.');
            }
    
            // Actualizar el estado del pago
            $this->updatePaymentStatus($voucherPayment, $paymentStatus, $request->all());
    
            return response()->json(['message' => 'Estado de pago actualizado correctamente'], 200);
        } catch (\Exception $e) {
            Log::error('Error en el callback de pago: ' . $e->getMessage());
            return response()->json(['message' => 'Error al procesar el callback'], 500);
        }
    }

    private function updatePaymentStatus(VoucherPayment $voucherPayment, $status, $apiResponse)
    {
        // Actualizar el estado del pago según el estado recibido en el webhook
        switch ($status) {
            case 'approved':
                $voucherPayment->update([
                    'payment_status' => 'Completed',
                    'api_response' => json_encode($apiResponse),
                ]);
                break;

            case 'rejected':
                $voucherPayment->update([
                    'payment_status' => 'Failed',
                    'api_response' => json_encode($apiResponse),
                ]);
                break;

            case 'cancelled':
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
