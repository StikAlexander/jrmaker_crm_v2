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
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Support\Enums\MaxWidth;

class VoucherPaymentResource extends Resource
{
    protected static ?string $model = VoucherPayment::class;
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $recordTitleAttribute = 'voucher_number';
    protected static ?string $pluralLabel = 'Pagos';
    protected static ?string $singularLabel = 'pago';
    protected static ?string $navigationGroup = 'Contabilidad';
    protected static ?int $navigationSort = 2;
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()
                    ->schema([
                        Section::make('Información General')
                            ->description('Detalles generales del pago')
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        Forms\Components\TextInput::make('voucher_number')
                                            ->default(function () {
                                                $lastVoucherNumber = VoucherPayment::max('voucher_number');
                                                return $lastVoucherNumber ? $lastVoucherNumber + 1 : 1;
                                            })
                                            ->prefix('SP')
                                            ->disabled()
                                            ->columnSpan(1),
    
                                        Select::make('client_id')
                                            ->label('Cliente')
                                            ->options(User::role('client')->pluck('name', 'id'))
                                            ->searchable()
                                            ->required()
                                            ->reactive()
                                            ->afterStateUpdated(function (callable $set) {
                                                $set('invoice_id', []);
                                            })
                                            ->columnSpan(1),
    
                                        Select::make('invoice_id')
                                            ->label('Factura(s)')
                                            ->options(function (callable $get) {
                                                $clientId = $get('client_id');
                                                return $clientId ? Invoice::where('client_id', $clientId)
                                                    ->where('status', 'Pending')
                                                    ->pluck('invoice_number', 'id') : [];
                                            })
                                            ->searchable()
                                            ->required()
                                            ->multiple()
                                            ->reactive()
                                            ->columnSpan(2),
                                    ]),
                            ])
                            ->columns(1),
    
                        Section::make('Detalles de Pago')
                            ->description('Ingrese la información del pago')
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        Forms\Components\TextInput::make('amount')
                                            ->label('Monto')
                                            ->required()
                                            ->numeric()
                                            ->mask(RawJs::make('$money($input)'))
                                            ->stripCharacters([',', '.']) // Eliminar puntos y comas
                                            ->prefix('$')
                                            ->reactive()
                                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                                $invoiceIds = $get('invoice_id');
                                                if (!$invoiceIds) return;
                                                $totalAmount = (int)Invoice::whereIn('id', $invoiceIds)->sum('total_amount');
                                                if ((int)$state > $totalAmount) {
                                                    $set('amount', $totalAmount);
                                                }
                                            })
                                            ->columnSpan(1),
    
                                        Forms\Components\DatePicker::make('payment_date')
                                            ->label('Fecha de Pago')
                                            ->required()
                                            ->maxDate(now())
                                            ->default(now())
                                            ->columnSpan(1),
    
                                        Forms\Components\TextInput::make('issue_date')
                                            ->label('Fecha de Emisión')
                                            ->default(now()->format('Y-m-d'))
                                            ->disabled()
                                            ->columnSpan(1),
    
                                        Forms\Components\TextInput::make('due_date')
                                            ->label('Fecha de Vencimiento')
                                            ->default(fn (callable $get) => \Carbon\Carbon::parse($get('issue_date'))->addDays(5)->format('Y-m-d'))
                                            ->disabled()
                                            ->columnSpan(1),
                                    ]),
                            ])
                            ->columns(1),
    
                        Section::make('Soporte de Pago')
                            ->description('Adjunte el soporte del pago')
                            ->schema([
                                Forms\Components\FileUpload::make('payment_support')
                                    ->label('Soporte de Pago')
                                    ->directory('voucher_payments')
                                    ->acceptedFileTypes(['application/pdf'])
                                    ->maxSize(10240)
                                    ->required()
                                    ->preserveFilenames()
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->maxWidth(MaxWidth::FiveExtraLarge)  // Limita el ancho de la tarjeta
                    ->extraAttributes([
                        'class' => 'mx-auto mt-10',  // Centra la tarjeta en la pantalla
                    ]),
            ]);
    }
    
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('voucher_number')
                    ->label('Número de Voucher')
                    ->sortable()
                    ->searchable()
                    ->formatStateUsing(fn (string $state): string => 'SP' . $state),
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
                    ->sortable()
                    ->formatStateUsing(fn($state) => '$' . number_format($state, 0)),                
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
