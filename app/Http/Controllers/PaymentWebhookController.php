<?php

namespace App\Http\Controllers;

use App\Models\VoucherPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentWebhookController extends Controller
{
    public function handleCallback(Request $request)
    {
        // Registrar el payload completo del webhook para análisis
        Log::info('Webhook recibido:', $request->all());
    
        // Verificar el tipo de webhook (merchant_order, payment, etc.)
        $topic = $request->input('topic');
        Log::info('Tipo de webhook recibido: ' . $topic);
        
        // Verificar si el payload tiene una URL de recurso
        $resourceUrl = $request->input('resource');
        
        if (!$resourceUrl) {
            Log::error('No se proporcionó la URL del recurso en el webhook.');
            return response()->json(['message' => 'URL del recurso no proporcionada.'], 400);
        }
    
        // Realizar una solicitud HTTP a la URL del recurso para obtener detalles completos
        $client = new \GuzzleHttp\Client();
        try {
            $response = $client->request('GET', $resourceUrl, [
                'headers' => [
                    'Authorization' => 'Bearer ' . config('services.mercadopago.access_token'),
                ],
            ]);
            $paymentDetails = json_decode($response->getBody()->getContents(), true);
            Log::info('Detalles del pago obtenidos:', $paymentDetails);
        } catch (\Exception $e) {
            Log::error('Error al obtener los detalles del pago desde MercadoPago: ' . $e->getMessage());
            return response()->json(['message' => 'Error al obtener detalles del pago.'], 500);
        }
    
        // Extraer el external_reference basado en el tipo de webhook
        $externalReference = null;
        if ($topic === 'merchant_order') {
            $externalReference = $paymentDetails['external_reference'] ?? null;
        } elseif ($topic === 'payment') {
            $externalReference = $paymentDetails['collection']['external_reference'] ?? null;
        }
    
        if (!$externalReference) {
            Log::error('External reference no encontrado en los detalles del pago.');
            return response()->json(['message' => 'External reference no encontrado.'], 404);
        }
    
        // Añadir más detalles sobre la búsqueda en la base de datos
        Log::info('Buscando en la base de datos con external_reference: ' . $externalReference);
        
        // Verificar qué sucede en la base de datos al realizar la búsqueda
        $voucherPayment = VoucherPayment::where('external_reference', (string) $externalReference)->first();
    
        if (!$voucherPayment) {
            Log::error('No se encontró el pago con la referencia externa en la base de datos: ' . $externalReference);
            return response()->json(['message' => 'Pago no encontrado.'], 404);
        } else {
            Log::info('Pago encontrado: ID = ' . $voucherPayment->id . ', External Reference = ' . $voucherPayment->external_reference);
        }
    
        // Procesar el estado del pago...
        $paymentStatus = $request->input('status');
        $this->updatePaymentStatus($voucherPayment, $paymentStatus, $request->all());
    
        return response()->json(['message' => 'Estado de pago actualizado correctamente'], 200);
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
