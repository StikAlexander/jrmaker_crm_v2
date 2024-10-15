<?php

namespace App\Filament\Resources\ClientUserResource\Pages;

use App\Filament\Resources\ClientUserResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use YOS\FilamentExcel\Actions\Import;
use App\Imports\ClientImport;
use App\Exports\ClientExport;
use Maatwebsite\Excel\Facades\Excel;

class ListClientUsers extends ListRecords
{
    protected static string $resource = ClientUserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Crear Cliente')  
                ->icon('heroicon-o-user-plus')
                ->color('primary'),

            
            Import::make()
                ->import(ClientImport::class)  
                ->type(\Maatwebsite\Excel\Excel::XLSX)
                ->label('Importar Clientes')
                ->hint('Sube un archivo XLSX para importar clientes')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('primary'),

            Actions\Action::make('export')
                ->label('Exportar Clientes')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('primary')
                ->action(function () {
                    return Excel::download(new ClientExport, 'clients.xlsx');  
                }),
        ];
    }
}
