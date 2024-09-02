<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Resources\InvoiceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use YOS\FilamentExcel\Actions\Import;
use App\Imports\InvoiceImport;
use Maatwebsite\Excel\Facades\Excel; // Importa la fachada de Excel
use App\Exports\InvoicesExport; // Asegúrate de que la clase de exportación esté en el lugar correcto

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
                ->color('success'),

            Actions\Action::make('export')
                ->label('Exportar Facturas')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action(function () {
                    return Excel::download(new InvoicesExport, 'invoices.xlsx');
                }),
                Actions\CreateAction::make(),
        ];
    }
}
