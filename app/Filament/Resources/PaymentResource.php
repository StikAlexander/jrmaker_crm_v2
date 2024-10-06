<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Models\Payment;
use App\Models\User;
use App\Models\Invoice;
use Filament\Forms;
use Filament\Forms\Form;
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

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('payment_number')
                ->prefix('SP')
                ->disabled(),

            Forms\Components\Select::make('client_id')
                ->label('Cliente')
                ->options(User::role('client')->pluck('name', 'id'))
                ->searchable()
                ->required(),

            Forms\Components\Select::make('invoice_id')
                ->label('Factura(s)')
                ->options(function ($get) {
                    return Invoice::where('client_id', $get('client_id'))->pluck('invoice_number', 'id');
                })
                ->multiple()
                ->required(),

            Forms\Components\TextInput::make('amount')
                ->label('Monto')
                ->required()
                ->numeric()
                ->prefix('$'),

            Forms\Components\TextInput::make('payment_link')
                ->label('Link de Pago')
                ->nullable()
                ->disabled(),  // Link generado automáticamente

            Forms\Components\Select::make('payment_status')
                ->label('Estado del Pago')
                ->options([
                    'Pending' => 'Pendiente',
                    'Completed' => 'Completado',
                    'Failed' => 'Fallido',
                    'Cancelled' => 'Cancelado',
                ])
                ->disabled(),  // lo maneja la pasarela de pago
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('client.name')
                ->label('Cliente')  
                ->sortable(),  
            Tables\Columns\TextColumn::make('payment_number')
                ->label('Número de Pago')
                ->sortable(),
            
            // Eliminada la columna 'issue_date' ya que no existe en la tabla 'payments'

            Tables\Columns\TextColumn::make('amount')
                ->label('Monto')
                ->money('COP')  
                ->sortable(),  
            TextColumn::make('payment_status')
                ->label('Estado del Pago')
                ->badge()  
                ->color(fn (string $state): string => match ($state) {
                    'Pending' => 'warning',  // Color amarillo para pendiente
                    'Completed' => 'success', // Verde para completado
                    'Failed' => 'danger',    // Rojo para fallido
                    'Cancelled' => 'gray',   // Gris para cancelado
                    default => 'secondary',
                })
                ->formatStateUsing(fn (string $state): string => match ($state) {
                    'Pending' => 'Pendiente',
                    'Completed' => 'Completado',
                    'Failed' => 'Fallido',
                    'Cancelled' => 'Cancelado',
                    default => $state,
                }),
            Tables\Columns\TextColumn::make('payment_link')
                ->label('Link de Pago')
                ->hidden(),  // Oculto si no es necesario mostrar
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayments::route('/'),
            'create' => Pages\CreatePayment::route('/create'),
            'edit' => Pages\EditPayment::route('/{record}/edit'),
        ];
    }
}
