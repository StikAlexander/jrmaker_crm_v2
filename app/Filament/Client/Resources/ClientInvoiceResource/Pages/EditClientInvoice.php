<?php

namespace App\Filament\Client\Resources\ClientInvoiceResource\Pages;

use App\Filament\Client\Resources\ClientInvoiceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditClientInvoice extends EditRecord
{
    protected static string $resource = ClientInvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
