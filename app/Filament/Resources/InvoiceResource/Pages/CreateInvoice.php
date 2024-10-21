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

    public function getTitle(): string
    {
        return 'Crear Factura';
    }

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

        // Renombrar el archivo PDF con el número de factura
        if (isset($data['invoice_pdf'])) {
            $this->renameInvoicePdf($invoice, $data['invoice_pdf']);
        }

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

            // Verificar si el archivo existe y renombrarlo
            if (Storage::disk('public')->exists($pdfPath)) {
                Storage::disk('public')->move($pdfPath, $newPath); // Mover el archivo
                $invoice->update(['invoice_pdf' => $newPath]); // Actualizar el registro en la BD
            }
        }
    }
}
