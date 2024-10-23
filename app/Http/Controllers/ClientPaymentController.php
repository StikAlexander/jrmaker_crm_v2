<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class ClientPaymentController extends Controller
{
    public function downloadInvoices(Payment $payment)
    {
        // Obtener las facturas asociadas al pago
        $invoices = $payment->invoices;

        // Si no hay facturas asociadas, redirigir con un mensaje
        if ($invoices->isEmpty()) {
            return back()->with('error', 'No hay facturas asociadas a este pago.');
        }

        // Si hay más de una factura, generamos un solo PDF combinando las facturas
        $pdf = Pdf::loadView('pdf.multiple-invoices', compact('invoices'));
        return $pdf->download('facturas-' . $payment->payment_number . '.pdf');
    }
}
