<?php

namespace App\Filament\Client\Resources\ClientPaymentResource\Pages;

use App\Filament\Client\Resources\ClientPaymentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListClientPayments extends ListRecords
{
    protected static string $resource = ClientPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
