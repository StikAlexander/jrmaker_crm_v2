<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;
use setasign\Fpdi\Fpdi;

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

        // Filtramos facturas que no tienen un PDF asignado
        $invoicesWithoutPdf = $invoices->filter(fn ($invoice) => is_null($invoice->invoice_pdf));

        if ($invoicesWithoutPdf->count() > 0) {
            Notification::make()
                ->title('Advertencia')
                ->body('Algunas facturas no tienen un PDF disponible.')
                ->warning()
                ->send();
            return back();
        }

        // Creamos un nuevo archivo PDF combinando las facturas
        $pdf = new Fpdi();

        foreach ($invoices as $invoice) {
            $filePath = Storage::path($invoice->invoice_pdf);  // Obtener la ruta del archivo
            if (!file_exists($filePath)) {
                Notification::make()
                    ->title('Error')
                    ->body('Uno de los archivos PDF no existe en el servidor.')
                    ->danger()
                    ->send();
                return back();
            }

            // Agregamos las páginas del PDF a FPDI
            $pageCount = $pdf->setSourceFile($filePath);
            for ($i = 1; $i <= $pageCount; $i++) {
                $templateId = $pdf->importPage($i);
                $pdf->addPage();
                $pdf->useTemplate($templateId);
            }
        }

        // Salida del archivo combinado para su descarga
        $output = $pdf->Output('S');  // Output como string

        return response($output, 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="facturas-combinadas.pdf"');
    }
}
