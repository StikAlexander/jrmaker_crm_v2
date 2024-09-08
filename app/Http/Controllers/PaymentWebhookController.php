<?php

namespace App\Http\Controllers;

use App\Models\VoucherPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentWebhookController extends Controller
{
    public function handleCallback(Request $request)
    {
        $externalReference = $request->input('external_reference');

        // Buscar el VoucherPayment por external_reference
        $voucherPayment = VoucherPayment::where('external_reference', $externalReference)->first();

        if (!$voucherPayment) {
            Log::error('Pago no encontrado con la referencia externa: ' . $externalReference);
            return response()->json(['message' => 'Pago no encontrado.'], 404);
        }

        // Procesar el estado del pago
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
