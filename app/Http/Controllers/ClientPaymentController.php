<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Notifications\Notification; // Asegúrate de importar las notificaciones
use Illuminate\Support\Facades\Storage;

class ClientPaymentController extends Controller
{
    public function downloadInvoices(Payment $payment)
    {
        // Obtener las facturas asociadas al pago
        $invoices = $payment->invoices;

        // Si no hay facturas asociadas, mostrar notificación de error
        if ($invoices->isEmpty()) {
            Notification::make()
                ->title('Error')
                ->body('No hay facturas asociadas a este pago.')
                ->danger()
                ->send();
            return back();
        }

        // Verificar si alguna factura no tiene PDF subido
        $missingPdf = $invoices->filter(fn($invoice) => is_null($invoice->invoice_pdf));

        if ($missingPdf->count() > 0) {
            Notification::make()
                ->title('Error')
                ->body('Algunas facturas no tienen un PDF disponible.')
                ->danger()
                ->send();
            return back();
        }

        // Generar un solo PDF combinando las facturas que tienen PDF
        $pdf = Pdf::loadView('pdf.multiple-invoices', compact('invoices'));
        return $pdf->download('facturas-' . $payment->payment_number . '.pdf');
    }
}
