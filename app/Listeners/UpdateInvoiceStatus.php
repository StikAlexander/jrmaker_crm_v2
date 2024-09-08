<?php

namespace App\Listeners;

use App\Events\PaymentCompleted;

class UpdateInvoiceStatus
{
    public function handle(PaymentCompleted $event)
    {
        $payment = $event->payment;

        foreach ($payment->invoices as $invoice) {
            // Ajustar el monto pagado en la factura
            $invoice->total_paid += $payment->amount;

            // Calcular el monto pendiente de la factura
            $invoice->pending_amount = $invoice->total_amount - $invoice->total_paid;

            // Cambiar el estado de la factura a 'Paid' si el monto pendiente es 0
            if ($invoice->pending_amount <= 0) {
                $invoice->status = 'Paid';
            }

            // Guardar la factura actualizada
            $invoice->save();
        }
    }
}
