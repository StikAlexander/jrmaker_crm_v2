<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Models\Payment;
use App\Models\User;
use App\Models\Invoice;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $pluralLabel = 'Pagos';
    protected static ?string $singularLabel = 'Pago';
    protected static ?string $navigationGroup = 'Contabilidad';

    // Eliminar el método form() ya que no necesitamos formularios
    // public static function form(Form $form): Form {
    //     // Formulario eliminado porque no permitimos crear ni editar
    // }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('client.name')
                    ->label('Cliente'),
                TextColumn::make('payment_number')
                    ->label('Número de Pago')
                    ->prefix('FEVD')
                    ->sortable(),
                TextColumn::make('amount')
                    ->label('Monto')
                    ->money('COP')
                    ->sortable(),
                TextColumn::make('payment_status')
                    ->label('Estado del Pago')
                    ->badge()
                    ->sortable()
                    ->color(fn (string $state): string => match ($state) {
                        'Pending' => 'warning', // Amarillo para pendiente
                        'Completed' => 'success', // Verde para completado
                        'Failed' => 'danger', // Rojo para fallido
                        'Cancelled' => 'gray', // Gris para cancelado
                        default => 'secondary',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'Pending' => 'Pendiente',
                        'Completed' => 'Completado',
                        'Failed' => 'Fallido',
                        'Cancelled' => 'Cancelado',
                        default => $state,
                    }),
                TextColumn::make('payment_link')
                    ->label('Link de Pago')
                    ->hidden(),
                TextColumn::make('payment_method_type')
                    ->label('Metodo de Pago')
                    ->sortable(),
                
            ]);


        // No agregar acciones de edición o creación para mantenerlo como solo lectura
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayments::route('/'), // Solo la página de listado
            // Eliminar las rutas de creación y edición:
            // 'create' => Pages\CreatePayment::route('/create'),
            // 'edit' => Pages\EditPayment::route('/{record}/edit'),
        ];
    }
}
