<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Resources\InvoiceResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;

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

        // Añadir el usuario que creó la factura
        $data['created_by'] = auth()->id();

        // Crear la factura
        $invoice = static::getModel()::create($data);

        return $invoice;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        // Actualizar el registro de la factura
        $record->update($data);

        return $record;
    }
}
