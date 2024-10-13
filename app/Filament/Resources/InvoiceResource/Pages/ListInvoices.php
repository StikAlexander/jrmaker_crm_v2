<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Resources\InvoiceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use YOS\FilamentExcel\Actions\Import;
use App\Imports\InvoiceImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\InvoicesExport;

class ListInvoices extends ListRecords
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Import::make()
                ->import(InvoiceImport::class)
                ->type(\Maatwebsite\Excel\Excel::XLSX)
                ->label('Importar Facturas')
                ->hint('Sube un archivo XLSX')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('primary'),

            Actions\Action::make('export')
                ->label('Exportar Facturas')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('primary')
                ->action(function () {
                    return Excel::download(new InvoicesExport, 'invoices.xlsx');
                }),

            
            Actions\CreateAction::make()
                ->label('Crear Factura')  
                ->icon('heroicon-o-document-plus')
                ->color('primary'),
        ];
    }
}
