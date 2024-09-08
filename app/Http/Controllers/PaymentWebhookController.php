<?php

namespace App\Http\Controllers;

use App\Models\VoucherPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentWebhookController extends Controller
{
    public function handleCallback(Request $request)
    {
        //Log::info('Webhook recibido:', $request->all());
        
        // Obtener el recurso o el id dependiendo del tipo de webhook
        $resourceUrl = $request->input('resource');
        $paymentId = $request->input('data.id');
        
        if ($resourceUrl) {
            // Procesar merchant_order
            $this->handleMerchantOrder($resourceUrl);
        } elseif ($paymentId) {
            // Procesar payment.created
            $this->handlePaymentCreated($paymentId);
        } else {
            Log::error('No se proporcionó la URL del recurso ni el ID de pago en el webhook.');
            return response()->json(['message' => 'Datos insuficientes en el webhook.'], 400);
        }
        
        return response()->json(['message' => 'Webhook procesado correctamente.'], 200);
    }
    
    private function handlePaymentCreated($paymentId)
    {
        // Obtener detalles del pago desde la API de MercadoPago usando el id del pago
        $client = new \GuzzleHttp\Client();
        try {
            $response = $client->request('GET', "https://api.mercadopago.com/v1/payments/{$paymentId}", [
                'headers' => [
                    'Authorization' => 'Bearer ' . config('services.mercadopago.access_token'),
                ],
            ]);
            
            $paymentDetails = json_decode($response->getBody()->getContents(), true);
            //Log::info('Detalles del pago obtenidos:', $paymentDetails);
            
            // Obtener el external_reference desde los detalles del pago
            $externalReference = $paymentDetails['external_reference'] ?? null;
            
            if (!$externalReference) {
                //Log::error('External reference no encontrado en los detalles del pago.');
                return response()->json(['message' => 'External reference no encontrado.'], 404);
            }
            
            // Buscar el pago en la base de datos usando el external_reference
            $voucherPayment = VoucherPayment::where('external_reference', (string) $externalReference)->first();
            
            if (!$voucherPayment) {
                //Log::error('Pago no encontrado: ' . $externalReference);
                return response()->json(['message' => 'Pago no encontrado.'], 404);
            }
            
            // Actualizar el estado del pago basado en el estado del pago en MercadoPago
            $paymentStatus = $paymentDetails['status'] ?? 'unknown';
            $this->updatePaymentStatus($voucherPayment, $paymentStatus, $paymentDetails);
            
            //Log::info('Estado del pago actualizado para VoucherPayment ID: ' . $voucherPayment->id);
        } catch (\Exception $e) {
            Log::error('Error al obtener los detalles del pago: ' . $e->getMessage());
            return response()->json(['message' => 'Error al obtener detalles del pago.'], 500);
        }
    }
    
    private function handleMerchantOrder($resourceUrl)
    {
        //Log::info('Procesando merchant_order desde el recurso: ' . $resourceUrl);

        $client = new \GuzzleHttp\Client();
        try {
            // Obtener los detalles de la merchant_order
            $response = $client->request('GET', $resourceUrl, [
                'headers' => [
                    'Authorization' => 'Bearer ' . config('services.mercadopago.access_token'),
                ],
            ]);
            
            $orderDetails = json_decode($response->getBody()->getContents(), true);
            //Log::info('Detalles de la merchant_order obtenidos:', $orderDetails);
            
            // Aquí puedes manejar la lógica que desees con los detalles de la merchant_order
            // Por ejemplo, actualizar información en la base de datos relacionada con la orden
            
        } catch (\Exception $e) {
            Log::error('Error al obtener los detalles de la merchant_order: ' . $e->getMessage());
            return response()->json(['message' => 'Error al obtener detalles de la merchant_order.'], 500);
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

        //Log::info('Estado del pago actualizado para VoucherPayment ID: ' . $voucherPayment->id);
    }
}
