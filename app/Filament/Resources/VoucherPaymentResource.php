<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VoucherPaymentResource\Pages;
use App\Models\VoucherPayment;
use App\Models\User;
use App\Models\Invoice;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class VoucherPaymentResource extends Resource
{
    protected static ?string $model = VoucherPayment::class;
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'Contabilidad';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('voucher_number')
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
                ->disabled(),

            Forms\Components\Select::make('payment_status')
                ->label('Estado del Pago')
                ->options([
                    'Pending' => 'Pendiente',
                    'Completed' => 'Completado',
                    'Failed' => 'Fallido',
                    'Cancelled' => 'Cancelado',
                ])
                ->disabled(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('voucher_number')->label('Número de Voucher'),
            Tables\Columns\TextColumn::make('client.name')->label('Cliente'),
            Tables\Columns\TextColumn::make('payment_link')->label('Link de Pago'),
            Tables\Columns\TextColumn::make('payment_status')->label('Estado del Pago'),
            Tables\Columns\TextColumn::make('amount')->label('Monto')->money('COP'),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVoucherPayments::route('/'),
            'create' => Pages\CreateVoucherPayment::route('/create'),
            'edit' => Pages\EditVoucherPayment::route('/{record}/edit'),
        ];
    }
}
