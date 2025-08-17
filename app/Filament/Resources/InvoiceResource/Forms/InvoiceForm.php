<?php

namespace App\Filament\Resources\InvoiceResource\Forms;

use App\Constants\InvoiceStatus;
use App\Services\InvoiceService;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Get;
use Filament\Forms\Set;

class InvoiceForm
{
    public static function getBasicInfoSchema(): array
    {
        return [
            Section::make('Datos de la Factura')
                ->schema([
                    Grid::make()
                        ->columns(2)
                        ->schema([
                            TextInput::make('invoice_number')
                                ->label('Número de Factura')
                                ->prefix(InvoiceStatus::INVOICE_PREFIX)
                                ->required()
                                ->numeric()
                                ->rules([
                                    'regex:/^\d+$/',
                                    'not_in:e,E',
                                ])
                                ->extraAttributes(['onkeydown' => 'if(event.key === "e" || event.key === "E") event.preventDefault();'])
                                ->live()
                                ->debounce(500)
                                ->afterStateUpdated(function (Get $get, $state, Set $set) {
                                    $invoiceService = app(InvoiceService::class);
                                    $currentId = $get('id');
                                    
                                    if (!$invoiceService->isInvoiceNumberUnique($state, $currentId)) {
                                        $set('invoice_number_error', 'Este número de factura ya está siendo usado.');
                                    } else {
                                        $set('invoice_number_error', null);
                                    }
                                })
                                ->hint(fn (Get $get) => $get('invoice_number_error'))
                                ->hintColor('danger'),
                                
                            Select::make('client_id')
                                ->label('Cliente')
                                ->required()
                                ->relationship('client', 'name')
                                ->searchable()
                                ->preload()
                                ->options(function () {
                                    return app(InvoiceService::class)->getCachedClientsList();
                                }),
                        ]),
                    Grid::make()
                        ->columns(2)
                        ->schema([
                            DatePicker::make('issue_date')
                                ->label('Fecha de Emisión')
                                ->required()
                                ->reactive()
                                ->maxDate(Carbon::today())
                                ->afterStateUpdated(function ($state, callable $set) {
                                    if ($state) {
                                        $dueDate = Carbon::parse($state)->addDays(30)->format('Y-m-d');
                                        $set('due_date', $dueDate);
                                    } else {
                                        $set('due_date', null);
                                    }
                                }),
                            DatePicker::make('due_date')
                                ->label('Fecha de Vencimiento')
                                ->required()
                                ->disabled()
                                ->placeholder('Se calculará automáticamente'),
                        ]),
                ]),
        ];
    }

    public static function getAmountsSchema(): array
    {
        return [
            Section::make('Montos')
                ->schema([
                    Grid::make()
                        ->columns(3)
                        ->schema([
                            TextInput::make('total_amount')
                                ->label('Monto Total')
                                ->required()
                                ->numeric()
                                ->prefix('$')
                                ->reactive()
                                ->debounce(500)
                                ->afterStateUpdated(function ($state, callable $set) {
                                    $set('total_paid', null);
                                    $set('pending_amount', $state);
                                }),
                            TextInput::make('total_paid')
                                ->label('Monto Pagado')
                                ->required()
                                ->numeric()
                                ->prefix('$')
                                ->reactive()
                                ->debounce(500)
                                ->disabled(fn (callable $get) => empty($get('total_amount')))
                                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                    $totalAmount = (float)($get('total_amount') ?? 0);
                                    $paidAmount = (float)($state ?? 0);

                                    if ($paidAmount > $totalAmount) {
                                        $paidAmount = $totalAmount;
                                        $set('total_paid', $paidAmount);
                                    }

                                    $pendingAmount = $totalAmount - $paidAmount;
                                    $set('pending_amount', $pendingAmount);

                                    if ($paidAmount < $totalAmount) {
                                        $set('status', InvoiceStatus::PENDING);
                                    } else {
                                        $set('status', InvoiceStatus::PAID);
                                    }
                                }),
                            TextInput::make('pending_amount')
                                ->label('Monto Pendiente')
                                ->prefix('$')
                                ->disabled(),
                        ]),
                ]),
        ];
    }

    public static function getAdditionalInfoSchema(): array
    {
        return [
            Section::make('Información Adicional')
                ->schema([
                    Grid::make()
                        ->columns(2)
                        ->schema([
                            Select::make('status')
                                ->label('Estado')
                                ->options(InvoiceStatus::getStatusOptions())
                                ->disabled(),
                            FileUpload::make('invoice_pdf')
                                ->directory('invoices')
                                ->required()
                                ->acceptedFileTypes(['application/pdf'])
                                ->maxSize(10240)
                                ->visibility('public'),
                        ]),
                    Textarea::make('description')
                        ->label('Descripción')
                        ->columnSpanFull(),
                ]),
        ];
    }
}
