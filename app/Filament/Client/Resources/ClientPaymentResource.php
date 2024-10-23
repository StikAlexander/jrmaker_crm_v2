<?php

namespace App\Filament\Client\Resources;

use App\Filament\Client\Resources\ClientPaymentResource\Pages;
use App\Models\Payment;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\ViewAction;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Actions\Action;

class ClientPaymentResource extends Resource
{
    protected static ?string $model = Payment::class;
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $pluralLabel = 'Pagos Realizados';
    protected static ?string $singularLabel = 'Pago Realizado';
    protected static ?string $navigationGroup = 'Mi Cuenta';
    protected static ?int $navigationSort = 2;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('client_id', auth()->id())
            ->where('payment_status', 'Completed'); // Solo pagos completados
    }

    public static function table(Table $table): Table
    {
        return $table
            ->emptyStateIcon('heroicon-o-credit-card')
            ->emptyStateHeading('Sin pagos realizados')
            ->emptyStateDescription('No hay pagos completados en este momento.')
            ->columns([
                TextColumn::make('payment_number')
                    ->label('Número de Pago')
                    ->prefix('SP')
                    ->sortable(),

                TextColumn::make('amount')
                    ->label('Monto')
                    ->money('COP')
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->label('Fecha de Pago')
                    ->sortable()
                    ->formatStateUsing(fn (string $state): string => \Carbon\Carbon::parse($state)->format('d/m/Y g:i A')),
            ])
            ->actions([
                ViewAction::make()
                    ->label('Ver Facturas Asociadas')
                    ->modalHeading('Facturas Asociadas al Pago')
                    ->modalContent(fn (Payment $record) => view('filament.modals.view-invoices', ['invoices' => $record->invoices])),

                Action::make('downloadInvoicePdf')
                    ->label('Descargar Facturas Relacionadas')  // Cambié el nombre para algo más claro
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn (Payment $record) => route('client.download-invoices', ['payment' => $record->id]))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClientPayments::route('/'),
        ];
    }
}
