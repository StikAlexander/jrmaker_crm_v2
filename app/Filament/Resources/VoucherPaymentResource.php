<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VoucherPaymentResource\Pages;
use App\Models\VoucherPayment;
use App\Models\User;
use App\Models\Invoice;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;
use Filament\Tables\Actions\Action;

class VoucherPaymentResource extends Resource
{
    protected static ?string $model = VoucherPayment::class;
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $recordTitleAttribute = 'voucher_number';
    protected static ?string $navigationLabel = 'Pagos';
    protected static ?string $navigationGroup = 'Contabilidad';
    protected static ?int $navigationSort = 2;
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('client_id')
                    ->label('Cliente')
                    ->options(User::role('client')->pluck('name', 'id'))
                    ->searchable()
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function (callable $set) {
                        // Resetear las facturas seleccionadas al cambiar el cliente
                        $set('invoice_id', []);
                    }),
    
                    Forms\Components\Select::make('invoice_id')
                    ->label('Factura(s)')
                    ->options(function (callable $get) {
                        $clientId = $get('client_id');
                        if (!$clientId) return [];
                        return Invoice::where('client_id', $clientId)
                            ->where('status', 'Pending')
                            ->pluck('invoice_number', 'id');
                    })
                    ->searchable()
                    ->required()
                    ->multiple()
                    ->reactive(),



                Forms\Components\DatePicker::make('payment_date')
                    ->label('Fecha de Pago')
                    ->required()
                    ->maxDate(now())
                    ->default(now()),
    
                    Forms\Components\TextInput::make('amount')
                    ->label('Monto')
                    ->required()
                    ->numeric()
                    ->mask(RawJs::make('$money($input)'))
                    ->stripCharacters(',')
                    ->prefix('$')
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        $invoiceIds = $get('invoice_id');
                        if (!$invoiceIds) return;
                        $totalAmount = Invoice::whereIn('id', $invoiceIds)->sum('total_amount');
                        if ($state > $totalAmount) {
                            $set('amount', $totalAmount);
                        }
                    }),
    
                Forms\Components\FileUpload::make('payment_support')
                    ->label('Soporte de Pago')
                    ->directory('voucher_payments')
                    ->acceptedFileTypes(['application/pdf'])
                    ->maxSize(10240)
                    ->required(),
    
                Forms\Components\TextInput::make('voucher_number')
                    ->default(function () {
                        $lastVoucherNumber = VoucherPayment::max('voucher_number');
                        return $lastVoucherNumber ? $lastVoucherNumber + 1 : 1;
                    })
                    ->disabled(),
    
                Forms\Components\Hidden::make('issue_date')
                    ->default(now()),
            ]);
    }
    

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('voucher_number')
                    ->label('Número de Voucher')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('client.name')  
                    ->label('Cliente')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('invoices')
                    ->label('Número(s) de Factura')
                    ->getStateUsing(function ($record) {
                        return $record->invoices->pluck('invoice_number')->join(', ');
                    }),
                Tables\Columns\TextColumn::make('payment_date')
                    ->label('Fecha de Pago')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Monto')
                    ->money('COP')
                    ->sortable(),
                Tables\Columns\TextColumn::make('createdBy.name')
                    ->label('Creado por')
                    ->sortable()
                    ->searchable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Action::make('viewPdf')
                    ->label('Ver PDF')
                    ->icon('heroicon-o-document-text')
                    ->url(fn ($record) => Storage::url($record->payment_support))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
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
