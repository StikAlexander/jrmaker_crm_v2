<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Barryvdh\DomPDF\Facade\Pdf; 
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class ClientPaymentController extends Controller
{
    public function downloadInvoices(Payment $payment)
    {
        // Obtener las facturas asociadas al pago
        $invoices = $payment->invoices;  // Asegúrate de que Payment tiene una relación con las facturas

        // Si hay más de una factura, generamos un solo PDF combinando las facturas
        if ($invoices->count() > 1) {
            $pdf = Pdf::loadView('pdf.multiple-invoices', compact('invoices'));
            return $pdf->download('facturas-' . $payment->payment_number . '.pdf');
        }

        // Si solo es una factura, descargamos directamente el PDF almacenado
        return Storage::download($invoices->first()->invoice_pdf);
    }
}
