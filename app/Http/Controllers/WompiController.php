<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payment;
use Illuminate\Support\Facades\Log;

class WompiController extends Controller
{
    public function handleRedirect(Request $request)
    {
        // Aquí obtienes la información de la transacción desde el request
        $transactionId = $request->input('transaction_id');
        $status = $request->input('status');
        
        // Buscar el pago relacionado en tu base de datos
        $payment = Payment::where('transaction_id', $transactionId)->first();

        if ($payment) {
            // Verifica que el estado actual no sea 'Completed' antes de actualizar
            if ($payment->payment_status !== 'Completed') {
                // Manejar el estado de la transacción
                switch ($status) {
                    case 'APPROVED':
                        $payment->update(['payment_status' => 'Completed']);
                        break;
                    case 'DECLINED':
                        $payment->update(['payment_status' => 'Failed']);
                        break;
                    case 'CANCELLED':
                        $payment->update(['payment_status' => 'Cancelled']);
                        break;
                    case 'ERROR':
                        $payment->update(['payment_status' => 'Failed']);
                        break;
                    default:
                        $payment->update(['payment_status' => 'Pending']);
                        break;
                }

                Log::info('Estado del pago actualizado correctamente', [
                    'transaction_id' => $transactionId,
                    'status' => $status,
                ]);

                return redirect()->route('invoice.index')->with('status', 'Pago actualizado correctamente.');
            } else {
                // Si el pago ya fue completado, no hacer nada
                Log::warning('El pago ya fue completado y no se puede modificar.', [
                    'transaction_id' => $transactionId,
                ]);

                return redirect()->route('invoice.index')->with('warning', 'El pago ya está completado.');
            }
        }

        // Si no se encontró el pago relacionado
        Log::error('Pago no encontrado para el ID de la transacción: ' . $transactionId);
        return redirect()->route('invoice.index')->with('error', 'Error al procesar el pago.');
    }
}
