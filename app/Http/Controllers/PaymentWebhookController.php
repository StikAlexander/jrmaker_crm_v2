<?php

namespace App\Http\Controllers;

use App\Models\VoucherPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentWebhookController extends Controller
{
    public function handleCallback(Request $request)
    {
        Log::info('Webhook recibido:', $request->all());
        
        // Obtener el external_reference desde la notificación de pago
        $resourceUrl = $request->input('resource');

        if (!$resourceUrl) {
            Log::error('No se proporcionó la URL del recurso en el webhook.');
            return response()->json(['message' => 'URL del recurso no proporcionada.'], 400);
        }

        $client = new \GuzzleHttp\Client();
        try {
            $response = $client->request('GET', $resourceUrl, [
                'headers' => [
                    'Authorization' => 'Bearer ' . config('services.mercadopago.access_token'),
                ],
            ]);

            $paymentDetails = json_decode($response->getBody()->getContents(), true);
            Log::info('Detalles del pago obtenidos:', $paymentDetails);

            // Obtener el external_reference desde los detalles del pago
            $externalReference = $paymentDetails['external_reference'] ?? null;

            if (!$externalReference) {
                Log::error('External reference no encontrado en los detalles del pago.');
                return response()->json(['message' => 'External reference no encontrado.'], 404);
            }

            // Buscar el pago en la base de datos usando el external_reference
            $voucherPayment = VoucherPayment::where('external_reference', (string) $externalReference)->first();

            if (!$voucherPayment) {
                Log::error('Pago no encontrado: ' . $externalReference);
                return response()->json(['message' => 'Pago no encontrado.'], 404);
            }

            // Procesar el estado del pago
            $paymentStatus = $paymentDetails['status'] ?? 'unknown';
            $this->updatePaymentStatus($voucherPayment, $paymentStatus, $paymentDetails);

            return response()->json(['message' => 'Estado de pago actualizado correctamente'], 200);

        } catch (\Exception $e) {
            Log::error('Error al obtener los detalles del pago: ' . $e->getMessage());
            return response()->json(['message' => 'Error al obtener detalles del pago.'], 500);
        }
    }

    private function updatePaymentStatus(VoucherPayment $voucherPayment, $status, $apiResponse)
    {
        $voucherPayment->update([
            'payment_status' => match ($status) {
                'approved' => 'Completed',
                'rejected' => 'Failed',
                'cancelled' => 'Cancelled',
                default => 'Pending',
            },
            'api_response' => json_encode($apiResponse),
        ]);
    }
}
