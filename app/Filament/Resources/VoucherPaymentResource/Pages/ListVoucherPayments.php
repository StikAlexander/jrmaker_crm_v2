<?php

namespace App\Filament\Resources\VoucherPaymentResource\Pages;

use App\Filament\Resources\VoucherPaymentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListVoucherPayments extends ListRecords
{
    protected static string $resource = VoucherPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
