<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Constants\InvoiceStatus;
use App\Exports\InvoicesExport;
use App\Filament\Resources\InvoiceResource;
use App\Imports\InvoiceImport;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Maatwebsite\Excel\Facades\Excel;
use YOS\FilamentExcel\Actions\Import;

class ListInvoices extends ListRecords
{
    protected static string $resource = InvoiceResource::class;
    protected ?string $pollingInterval = '30s';

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
                    // Notificación de inicio de exportación
                    Notification::make()
                        ->title('Exportación iniciada')
                        ->body('La exportación de facturas ha comenzado')
                        ->info()
                        ->send();
                        
                    $result = Excel::download(new InvoicesExport, 'facturas_' . now()->format('Y-m-d_His') . '.xlsx');
                    
                    // Notificación de éxito
                    Notification::make()
                        ->title('Exportación completada')
                        ->body('Todas las facturas fueron exportadas correctamente')
                        ->success()
                        ->send();
                        
                    return $result;
                })
                ->rateLimit(5),
            
            Actions\CreateAction::make()
                ->label('Crear Factura')  
                ->icon('heroicon-o-document-plus')
                ->color('primary'),
        ];
    }
}
