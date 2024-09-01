<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Resources\InvoiceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use YOS\FilamentExcel\Actions\Import;
use App\Imports\InvoiceImport;

class ListInvoices extends ListRecords
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Import::make()
                ->import(InvoiceImport::class)
                ->type(\Maatwebsite\Excel\Excel::XLSX)
                ->label('Importar desde Excel')
                ->hint('Sube un archivo XLSX')
                ->icon('heroicon-o-arrow-up-on-square-stack')
                ->color('success'),
            Actions\CreateAction::make(),
        ];
    }
}
