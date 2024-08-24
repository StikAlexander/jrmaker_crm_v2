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
        if ($invoice && isset($data['invoice_pdf'])) {
            $oldPath = $data['invoice_pdf']; // Path original
            $extension = pathinfo($oldPath, PATHINFO_EXTENSION); // Obtener la extensión
            $newFilename = 'FEVD' . $invoice->invoice_number . '.' . $extension; // Crear el nuevo nombre
            $newPath = 'invoices/' . $newFilename;

            Storage::move($oldPath, $newPath); // Mover el archivo a la nueva ubicación

            // Actualizar la ruta del PDF en la base de datos
            $invoice->update(['invoice_pdf' => $newPath]);
        }

        return $invoice;
    }
}
