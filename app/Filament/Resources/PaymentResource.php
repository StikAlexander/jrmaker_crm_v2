<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Models\Payment;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Actions\ViewAction;
use Illuminate\Database\Eloquent\Builder;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $pluralLabel = 'Pagos';
    protected static ?string $singularLabel = 'Pago';
    protected static ?string $navigationGroup = 'Contabilidad';

    /**
     * Preload client and invoices for optimized query and filter by authenticated client.
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['client', 'invoices']) // Carga anticipada de relaciones
            ->where('client_id', auth()->id()) // Filtrar por cliente autenticado
            ->orderBy('payment_date', 'desc'); // Ordenar por fecha de pago
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('client.name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('payment_number')
                    ->label('Número de Pago')
                    ->prefix('SP')
                    ->sortable(),
                TextColumn::make('amount')
                    ->label('Monto')
                    ->money('COP')
                    ->sortable(),
                TextColumn::make('payment_date')
                    ->label('Fecha de Pago')
                    ->sortable()
                    ->formatStateUsing(fn (string $state): string => \Carbon\Carbon::parse($state)->format('d/m/Y g:i A')),
                BadgeColumn::make('payment_status')
                    ->label('Estado del Pago')
                    ->colors([
                        'warning' => 'Pending', 
                        'success' => 'Completed',
                        'danger' => 'Failed', 
                        'gray' => 'Cancelled',
                    ])
                    ->sortable(),
                TextColumn::make('payment_method_type')
                    ->label('Método de Pago')
                    ->sortable(),
            ])
            ->filters([ // Añadir filtros para optimizar búsquedas
                Tables\Filters\SelectFilter::make('payment_status')
                    ->options([
                        'Pending' => 'Pendiente',
                        'Completed' => 'Completado',
                        'Failed' => 'Fallido',
                        'Cancelled' => 'Cancelado',
                    ]),
            ])
            ->actions([
                ViewAction::make()
                    ->label('Ver Facturas')
                    ->modalHeading('Facturas Asociadas al Pago')
                    ->modalContent(fn (Payment $record) => view('filament.modals.view-invoices', ['invoices' => $record->invoices]))
            ])
            ->bulkActions([ // Acciones en lote
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('payment_date', 'desc') // Ordenación por defecto
            ->paginated(50); // Paginación para mejorar la velocidad con un límite de 50 elementos por página
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayments::route('/'),
        ];
    }
}
