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
        $signatureHeader = $request->header('x-signature');

        // Extraer el timestamp y la firma de x-signature
        $ts = null;
        $signature = null;
        $parts = explode(',', $signatureHeader);

        foreach ($parts as $part) {
            list($key, $value) = explode('=', $part);
            if ($key === 'ts') {
                $ts = $value; // Timestamp
            } elseif ($key === 'v1') {
                $signature = $value; // Firma recibida
            }
        }

        // Validar si se recibieron ts y v1
        if (!$ts || !$signature) {
            Log::error('Faltan ts o v1 en la firma.');
            return response()->json(['message' => 'Firma incompleta.'], 400);
        }

        // Registra el payload recibido completo
        $rawPayload = json_encode($request->all(), JSON_UNESCAPED_SLASHES);
        Log::info('Contenido del payload (API):', [$rawPayload]);
        Log::info('Firma recibida:', [$signature]);

        // Crear el string de manifest usando el payload y el timestamp
        $manifest = "id:{$request->input('data.id')};request-id:{$request->header('x-request-id')};ts:{$ts};";

        // Generar la firma esperada usando el manifest y la clave secreta
        $expectedSignature = hash_hmac('sha256', $manifest, $secretKey);
        Log::info('Firma esperada:', [$expectedSignature]);

        // Comparar la firma esperada con la firma recibida
        if (!hash_equals($expectedSignature, $signature)) {
            Log::warning('Firma no válida. Posible intento de falsificación.');
            return response()->json(['message' => 'Firma inválida.'], 403);
        }

        // Continua con el procesamiento del webhook
        Log::info('Callback recibido de la API de Mercado Pago:', $request->all());

        try {
            return response()->json(['message' => 'Estado de pago actualizado correctamente'], 200);
        } catch (\Exception $e) {
            Log::error('Error en el callback de pago: ' . $e->getMessage());
            return response()->json(['message' => 'Error al procesar el callback'], 500);
        }
    }

    /**
     * Actualiza el estado del pago en la base de datos según la respuesta del webhook.
     */
    private function updatePaymentStatus(VoucherPayment $voucherPayment, $status, $apiResponse)
    {
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
