<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvoiceResource\Pages;
use App\Models\Invoice;
use Carbon\Carbon;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Support\Enums\MaxWidth;
use Filament\Forms\Components\Card;
use Filament\Support\Enums\ActionSize;
use App\Tables\Columns\ModelLinkColumn;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Filament\GlobalSearch\GlobalSearchResult;



class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;
    protected static ?string $recordTitleAttribute = 'invoice_number';
    protected static ?string $pluralLabel = 'Facturas';
    protected static ?string $singularLabel = 'Factura';
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Contabilidad';
    protected static ?int $navigationSort = 1;
    public static function getGlobalSearchResults(string $search): Collection
    {
        
        if (str_starts_with(strtoupper($search), 'FEVD')) {
            
            $search = substr($search, 4);
    
            return static::getModel()::query()
                ->where('invoice_number', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")
                ->orWhere('total_amount', 'like', "%{$search}%")
                ->get()
                ->map(function (Model $record) {
                    return new GlobalSearchResult(
                        title: 'FEVD' . $record->invoice_number,
                        url: static::getUrl('edit', ['record' => $record]),
                        details: [
                            'Monto Total' => '$' . number_format($record->total_amount, 0, ',', '.'),
                            'Descripción' => $record->description,
                        ],
                    );
                });
        }
    
        
        return collect([]);
    }
    

    public static function getGloballySearchableAttributes(): array
    {
        return ['invoice_number', 'description', 'total_amount'];
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return 'FEVD' . $record->invoice_number;
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Monto Total' => '$' . number_format($record->total_amount, 0, ',', '.'),
            'Descripción' => $record->description,
        ];
    }

    public static function getGlobalSearchResultUrl(Model $record): string
    {
        return static::getUrl('edit', ['record' => $record]);
    }

    public static function getGlobalSearchResultActions(Model $record): array
    {
        return [
            Action::make('edit')
                ->url(static::getUrl('edit', ['record' => $record])),
        ];
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()
                    ->schema([
                        Section::make('Datos de la Factura')
                            ->schema([
                                Grid::make()
                                    ->columns(2)
                                    ->schema([
                                        TextInput::make('invoice_number')
                                            ->label('Número de Factura')
                                            ->prefix('FEVD')
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
                                                $currentId = $get('id');
                                                $exists = \App\Models\Invoice::where('invoice_number', $state)
                                                    ->when($currentId, fn ($query) => $query->where('id', '!=', $currentId))
                                                    ->exists();
    
                                                if ($exists) {
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
                                            ->options(fn () => \App\Models\User::whereHas('roles', function ($query) {
                                                $query->where('name', 'client');
                                            })->pluck('name', 'id')),
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
                                            ->placeholder('Se calculará automáticamente si aplica'),
                                    ]),
                            ]),
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
                                                    $set('status', 'Pending');
                                                } else {
                                                    $set('status', 'Paid');
                                                }
                                            }),
                                        TextInput::make('pending_amount')
                                            ->label('Monto Pendiente')
                                            ->prefix('$')
                                            ->disabled(),
                                    ]),
                            ]),
                        Section::make('Información Adicional')
                            ->schema([
                                Grid::make()
                                    ->columns(2)
                                    ->schema([
                                        Select::make('status')
                                            ->label('Estado')
                                            ->options([
                                                'Pending' => 'Pendiente',
                                                'Paid' => 'Pagada',
                                            ])
                                            ->disabled(),
                                        FileUpload::make('invoice_pdf')
                                            ->label('Factura PDF')
                                            ->required()
                                            ->directory('invoices')
                                            ->acceptedFileTypes(['application/pdf'])
                                            ->maxSize(10240),
                                    ]),
                                Textarea::make('description')
                                    ->label('Descripción')
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->maxWidth(MaxWidth::FiveExtraLarge)
                    ->extraAttributes([
                        'class' => 'mx-auto mt-10',
                    ]),
            ]);
    }
    
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ModelLinkColumn::make('invoice_number')
                    ->label('Número de Factura')
                    ->setViewType('view')
                    ->sortable()
                    ->searchable()
                    ->limit(50)
                    ->url(fn ($record) => InvoiceResource::getUrl('view', ['record' => $record->getKey()]))
                    ->formatStateUsing(fn (string $state): string => 'FEVD' . $state),
                ModelLinkColumn::make('client.name')
                    ->label('Cliente')
                    ->setViewType('view'),
                TextColumn::make('createdBy.name')
                    ->label('Creado por')
                    ->sortable()
                    ->searchable()
                    ->placeholder('-')
                    ->hidden(true),
                TextColumn::make('issue_date')
                    ->label('Fecha de Emisión')
                    ->date()
                    ->sortable(),
                TextColumn::make('due_date')
                    ->label('Fecha de Vencimiento')
                    ->date()
                    ->sortable(),
                TextColumn::make('total_amount')
                    ->label('Monto Total')
                    ->sortable()
                    ->formatStateUsing(fn (string $state): string => '$' . number_format($state, 0, ',', '.')),
                TextColumn::make('total_paid')
                    ->label('Monto Pagado')
                    ->sortable()
                    ->formatStateUsing(fn (string $state): string => '$' . number_format($state, 0, ',', '.')),
                TextColumn::make('pending_amount')
                    ->label('Monto Pendiente')
                    ->sortable()
                    ->formatStateUsing(fn (string $state): string => '$' . number_format($state, 0, ',', '.')),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Pending' => 'warning',    // Amarillo para pendiente
                        'Paid' => 'success',       // Verde para pagada
                        'Cancelled' => 'danger',   // Rojo para cancelada
                        default => 'secondary',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'Pending' => 'Pendiente',
                        'Paid' => 'Pagada',
                        'Cancelled' => 'Cancelada',
                        default => $state,
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('')
                    ->size(ActionSize::Large)
                    ->tooltip('Ver Detalles')
                    ->iconButton(),
            
                Tables\Actions\EditAction::make()
                    ->modalHeading('Editar Factura')
                    ->modalWidth('4xl')
                    ->label('')
                    ->size(ActionSize::Large)
                    ->tooltip('Editar Factura')
                    ->iconButton(),
            
                Action::make('viewPdf')
                    ->label('')
                    ->icon('heroicon-o-document-text')
                    ->size(ActionSize::Large)
                    ->url(fn ($record) => Storage::url($record->invoice_pdf))
                    ->openUrlInNewTab()
                    ->tooltip('Ver PDF')
                    ->iconButton(),
            
                Action::make('cancelInvoice')
                    ->label('')
                    ->icon('heroicon-o-x-circle')
                    ->size(ActionSize::Large)
                    ->color(fn (Invoice $record) => $record->status === 'Cancelled' ? 'secondary' : 'danger')
                    ->disabled(fn (Invoice $record) => $record->status === 'Cancelled' || $record->status === 'Paid') // Deshabilita si está cancelada o pagada
                    ->tooltip(fn (Invoice $record) => match ($record->status) {
                        'Paid' => 'No se puede anular una factura pagada',  // Tooltip si está pagada
                        'Cancelled' => 'Factura Anulada',  // Tooltip si está cancelada
                        default => 'Anular Factura'  // Tooltip estándar
                    })
                    ->iconButton()
                    ->action(function (Invoice $record) {
                        $record->status = 'Cancelled';
                        $record->save();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('cancelInvoices')
                    ->label('Anular Seleccionadas')
                    ->action(function (Collection $records) {
                        foreach ($records as $invoice) {
                            if ($invoice->status !== 'Cancelled' && $invoice->status !== 'Paid') { // No anula si está pagada o cancelada
                                $invoice->update(['status' => 'Cancelled']);
                            }
                        }
                    })
                    ->requiresConfirmation()
                    ->color('danger')
                    ->icon('heroicon-o-x-circle'),
            ]);
            
    }
    

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInvoices::route('/'),
            'edit' => Pages\EditInvoice::route('/{record}/edit'),
            'create' => Pages\CreateInvoice::route('/create'),
            'view' => Pages\ViewInvoice::route('/{record}'),
        ];
    }
}
