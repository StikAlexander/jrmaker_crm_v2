<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Resources\InvoiceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;

class EditInvoice extends EditRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    // Verificar si la factura está cancelada antes de permitir la edición
    public function mount($record): void
    {
        parent::mount($record);

        if ($this->record->status === 'Cancelled') {
            Notification::make()
                ->title('Factura anulada')
                ->body('No se puede editar una factura que ha sido anulada.')
                ->danger()
                ->send();

            // No redirigimos, pero podemos bloquear la edición aquí si prefieres
            $this->halt(); // Detenemos el proceso si está anulada
        }
    }

    // Al actualizar la factura, renombra el archivo PDF si es necesario
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $record = parent::handleRecordUpdate($record, $data);

        // Renombrar el archivo PDF si se subió uno nuevo
        if (isset($data['invoice_pdf'])) {
            $this->renameInvoicePdf($record, $data['invoice_pdf']);
        }

        return $record;  // No redirigimos, solo dejamos que actualice.
    }

    // Reutilizar el método de renombrado que ya tienes
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
