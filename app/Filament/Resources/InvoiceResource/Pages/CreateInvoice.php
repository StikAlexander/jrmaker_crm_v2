<?php


namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Resources\InvoiceResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;

class CreateInvoice extends CreateRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        // Validar la unicidad del número de factura
        $exists = DB::table('invoices')
            ->where('invoice_number', $data['invoice_number'])
            ->exists();

        if ($exists) {
            Notification::make()
                ->title('Error')
                ->body('Este número de factura ya está siendo usado. Por favor, elija otro.')
                ->danger()
                ->send();

            $this->halt();
            return null;
        }

        $data['created_by'] = auth()->id();

        // Crear la factura
        $invoice = static::getModel()::create($data);

        // Renombrar el archivo PDF después de que la factura ha sido creada
        $this->renameInvoicePdf($invoice, $data['invoice_pdf'] ?? null);

        return $invoice;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        if (isset($data['invoice_pdf'])) {
            $this->renameInvoicePdf($record, $data['invoice_pdf']);
        }

        $record->update($data);

        return $record;
    }

    protected function renameInvoicePdf(Model $invoice, $pdfPath): void
    {
        if ($pdfPath) {
            $extension = pathinfo($pdfPath, PATHINFO_EXTENSION);
            $newFilename = 'FEVD' . $invoice->invoice_number . '.' . $extension;
            $newPath = 'invoices/' . $newFilename;

            Storage::move($pdfPath, $newPath); // Mover el archivo a la nueva ubicación
            $invoice->update(['invoice_pdf' => $newPath]);
        }
    }
}
