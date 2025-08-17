<?php

namespace App\Filament\Resources\InvoiceResource\Tables;

use App\Constants\InvoiceStatus;
use App\Filament\Resources\ClientUserResource;
use App\Filament\Resources\InvoiceResource;
use App\Models\Invoice;
use App\Tables\Columns\ModelLinkColumn;
use Filament\Support\Enums\ActionSize;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;
use Filament\Notifications\Notification;

class InvoiceTableSchema
{
    public static function getColumns(): array
    {
        return [
            ModelLinkColumn::make('invoice_number')
                ->label('Número de Factura')
                ->setViewType('view')
                ->sortable()
                ->searchable()
                ->limit(50)
                ->url(fn ($record) => InvoiceResource::getUrl('view', ['record' => $record->getKey()]))
                ->formatStateUsing(fn (string $state): string => InvoiceStatus::INVOICE_PREFIX . $state),
            
            ModelLinkColumn::make('client.name')
                ->label('Cliente')
                ->setViewType('view')
                ->url(fn ($record) => ClientUserResource::getUrl('view', ['record' => $record->client->getKey()])),
                
            TextColumn::make('createdBy.name')
                ->label('Creado por')
                ->sortable()
                ->searchable()
                ->placeholder('-')
                ->hidden(true),
                
            TextColumn::make('issue_date')
                ->label('Fecha de Emisión')
                ->date()
                ->sortable()
                ->since(),
                
            TextColumn::make('due_date')
                ->label('Fecha de Vencimiento')
                ->date()
                ->sortable(),
                
            TextColumn::make('total_amount')
                ->label('Monto Total')
                ->sortable()
                ->formatStateUsing(fn (string $state): string => InvoiceStatus::formatCurrency($state)),
                
            TextColumn::make('total_paid')
                ->label('Monto Pagado')
                ->sortable()
                ->formatStateUsing(fn (string $state): string => InvoiceStatus::formatCurrency($state)),
                
            TextColumn::make('pending_amount')
                ->label('Monto Pendiente')
                ->sortable()
                ->formatStateUsing(fn (string $state): string => InvoiceStatus::formatCurrency($state)),
                
            TextColumn::make('status')
                ->label('Estado')
                ->badge()
                ->color(fn (string $state): string => InvoiceStatus::getStatusColor($state))
                ->sortable()
                ->formatStateUsing(fn (string $state): string => InvoiceStatus::getStatusLabel($state)),
        ];
    }

    public static function getActions(): array
    {
        return [
            ViewAction::make()
                ->label('')
                ->size(ActionSize::Large)
                ->tooltip('Ver Detalles')
                ->iconButton(),

            EditAction::make()
                ->modalHeading('Editar Factura')
                ->modalWidth('4xl')
                ->label('')
                ->size(ActionSize::Large)
                ->tooltip('Editar Factura')
                ->iconButton()
                ->disabled(fn (Invoice $record) => $record->isCancelled()),

            Action::make('viewPdf')
                ->label('')
                ->icon('heroicon-o-document-text')
                ->size(ActionSize::Large)
                ->url(fn ($record) => Storage::url($record->invoice_pdf))
                ->openUrlInNewTab()
                ->tooltip('Ver PDF')
                ->iconButton(),

            // Temporarily commented out to resolve rateLimit error
        /*Action::make('cancelInvoice')
                ->label('')
                ->icon('heroicon-o-x-circle')
                ->size(ActionSize::Large)
                ->color(fn (Invoice $record) => $record->isCancelled() ? 'secondary' : 'danger')
                ->disabled(fn (Invoice $record) => !$record->canBeCancelled())
                ->tooltip(fn (Invoice $record) => match ($record->status) {
                    InvoiceStatus::PAID => 'No se puede anular una factura pagada',
                    InvoiceStatus::CANCELLED => 'Factura Anulada',
                    default => 'Anular Factura'
                })
                ->requiresConfirmation()
                ->modalHeading('¿Estás seguro de que deseas anular esta factura?')
                ->modalSubheading('Esta acción no se puede deshacer.')
                ->iconButton()
                ->action(function (Invoice $record) {
                    $record->status = InvoiceStatus::CANCELLED;
                    $record->save();
                    
                    Notification::make()
                        ->title('Factura anulada')
                        ->success()
                        ->send();
                }),*/
        ];
    }

    public static function getBulkActions(): array
    {
        return [
            // Temporarily commented out to resolve rateLimit error
            /*BulkAction::make('cancelInvoices')
                ->label('Anular Seleccionadas')
                ->action(function (Collection $records) {
                    $cancelCount = 0;
                    $failCount = 0;
                    
                    foreach ($records as $invoice) {
                        if ($invoice->canBeCancelled()) {
                            $invoice->update(['status' => InvoiceStatus::CANCELLED]);
                            $cancelCount++;
                        } else {
                            $failCount++;
                        }
                    }
                    
                    Notification::make()
                        ->title("Facturas anuladas: {$cancelCount}")
                        ->body($failCount > 0 ? "{$failCount} facturas no pudieron ser anuladas" : null)
                        ->success()
                        ->send();
                })
                ->requiresConfirmation()
                ->color('danger')
                ->icon('heroicon-o-x-circle')
                ->chunkSelectedRecords(250),*/
        ];
    }
}
