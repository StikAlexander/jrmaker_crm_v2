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
        $invoices = $payment->invoices;
    
        // Si no hay facturas asociadas, mostramos un mensaje de error
        if ($invoices->isEmpty()) {
            Notification::make()
                ->title('Error')
                ->body('No hay facturas asociadas a este pago.')
                ->danger()
                ->send();
            return back();
        }
    
        // Filtramos facturas que no tienen PDF
        $invoicesWithoutPdf = $invoices->filter(fn ($invoice) => is_null($invoice->invoice_pdf));
    
        if ($invoicesWithoutPdf->count() > 0) {
            Notification::make()
                ->title('Advertencia')
                ->body('Algunas facturas no tienen un PDF disponible.')
                ->warning()
                ->send();
            return back();
        }
    
        // Generamos un PDF combinando las facturas con PDF
        $pdf = Pdf::loadView('pdf.multiple-invoices', compact('invoices'));
        return $pdf->download('facturas-' . $payment->payment_number . '.pdf');
    }    
}
