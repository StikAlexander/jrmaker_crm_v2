<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Payment;
use Illuminate\Support\Facades\Log;

class WompiController extends Controller
{
    /**
     * Maneja la redirección después del proceso de pago en Wompi.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handleRedirect(Request $request)
    {
        // Recuperar el transaction_id desde la redirección
        $transactionId = $request->input('transaction_id');

        if (!$transactionId) {
            Log::error('No se recibió transaction_id en la redirección.');
            return redirect('https://jrmaker.com.co/Error-Payment')
                ->with('message', 'No se pudo identificar la transacción.');
        }

        // Consultar el estado de la transacción usando la API de Wompi
        $transaction = $this->fetchTransactionFromApi($transactionId);

        if (!$transaction) {
            Log::error('La transacción no fue encontrada en la API de Wompi.', [
                'transaction_id' => $transactionId,
            ]);
            return redirect('https://jrmaker.com.co/Error-Payment')
                ->with('message', 'Error al verificar el estado del pago.');
        }

        $status = $transaction['status'];

        Log::info('Estado consultado desde la API de Wompi.', [
            'transaction_id' => $transactionId,
            'status' => $status,
        ]);

        // Redirigir basado en el estado de la transacción
        return $this->redirectBasedOnStatus($status);
    }

    /**
     * Consulta el estado de una transacción en la API de Wompi.
     *
     * @param string $transactionId
     * @return array|null
     */
    private function fetchTransactionFromApi(string $transactionId)
    {
        try {
            $response = Http::withToken('TU_CLAVE_API_WOMPI')
                ->get("https://sandbox.wompi.co/v1/transactions/{$transactionId}");

            if ($response->failed()) {
                Log::error('Error al consultar la API de Wompi.', [
                    'transaction_id' => $transactionId,
                    'response' => $response->body(),
                ]);
                return null;
            }

            return $response->json('data');
        } catch (\Exception $e) {
            Log::error('Excepción al consultar la API de Wompi.', [
                'transaction_id' => $transactionId,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Redirige al usuario basado en el estado de la transacción.
     *
     * @param string $status
     * @return \Illuminate\Http\RedirectResponse
     */
    private function redirectBasedOnStatus(string $status)
    {
        switch ($status) {
            case 'APPROVED':
                return redirect('https://jrmaker.com.co/Successful-Payment');
            case 'DECLINED':
            case 'ERROR':
                return redirect('https://jrmaker.com.co/Error-Payment');
            case 'CANCELLED':
                return redirect('https://jrmaker.com.co/Error-Payment');
            case 'VOIDED':
                return redirect('https://jrmaker.com.co/Error-Payment')
                    ->with('message', 'La transacción fue anulada.');
            default:
                return redirect('https://jrmaker.com.co/Error-Payment')
                    ->with('message', 'Estado desconocido o pendiente.');
        }
    }
}