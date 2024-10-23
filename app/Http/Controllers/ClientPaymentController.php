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

        if ($invoices->isEmpty()) {
            Notification::make()
                ->title('Error')
                ->body('No hay facturas asociadas a este pago.')
                ->danger()
                ->send();
            return back();
        }

        $invoicesWithoutPdf = $invoices->filter(fn ($invoice) => is_null($invoice->invoice_pdf));

        if ($invoicesWithoutPdf->count() > 0) {
            Notification::make()
                ->title('Advertencia')
                ->body('Algunas facturas no tienen un PDF disponible.')
                ->warning()
                ->send();
            return back();
        }

        $pdf = new Fpdi();

        foreach ($invoices as $invoice) {
            $filePath = "public/invoices/{$invoice->invoice_pdf}";

            if (strpos($invoice->invoice_pdf, 'invoices/') === 0) {
                $filePath = "public/{$invoice->invoice_pdf}";
            }

            if (!Storage::exists($filePath)) {
                Notification::make()
                    ->title('Error')
                    ->body("El archivo PDF {$filePath} no existe en el servidor.")
                    ->danger()
                    ->send();
                return back();
            }

            $fullFilePath = Storage::path($filePath);
            $pageCount = $pdf->setSourceFile($fullFilePath);

            for ($i = 1; $i <= $pageCount; $i++) {
                $templateId = $pdf->importPage($i);
                $pdf->addPage();
                $pdf->useTemplate($templateId);
            }
        }

        $output = $pdf->Output('S');

        return response($output, 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="facturas-combinadas.pdf"');
    }
}
