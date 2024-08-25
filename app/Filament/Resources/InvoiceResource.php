<?php


namespace App\Filament\Resources;

use App\Filament\Resources\InvoiceResource\Pages;
use App\Models\Invoice;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Support\RawJs; 
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Support\Htmlable;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;
    protected static ?string $recordTitleAttribute = 'invoice_number';
    protected static ?string $pluralLabel = 'Facturas';
    protected static ?string $singularLabel = 'Factura';
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Contabilidad';
    protected static ?int $navigationSort = 1;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['client']);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['invoice_number', 'description', 'total_amount'];
    }

    public static function getGlobalSearchResultTitle(Model $record): string | Htmlable
    {
        return $record->invoice_number;
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Total Amount' => $record->total_amount,
            'Description' => $record->description,
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
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\Section::make('Datos de la Factura')
                        ->description('Incluye los detalles principales de la factura')
                        ->schema([
                            Forms\Components\TextInput::make('invoice_number')
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
                                ->hintColor('danger')
                                ->columnSpan(2),
                    
                            Forms\Components\Select::make('client_id')
                                ->label('Cliente')
                                ->required()
                                ->relationship('client', 'name')
                                ->options(fn () => \App\Models\User::whereHas('roles', fn ($query) => $query->where('name', 'client'))->pluck('name', 'id'))
                                ->searchable()
                                ->preload()
                                ->columnSpan(2),
                        ])
                        ->columns(4),
                    
                    Forms\Components\Section::make('Fechas')
                        ->description('Selecciona las fechas relevantes para la factura')
                        ->schema([
                            Forms\Components\DatePicker::make('issue_date')
                                ->label('Fecha de Emisión')
                                ->required()
                                ->reactive()
                                ->default(now())
                                ->maxDate(now())  // Asegura que la fecha de emisión no sea mayor a la fecha actual
                                ->afterStateUpdated(function ($state, callable $set) {
                                    $dueDate = \Carbon\Carbon::parse($state)->addDays(30)->format('Y-m-d');
                                    $set('due_date', $dueDate);
                                })
                                ->columnSpan(2),
                    
                            Forms\Components\DatePicker::make('due_date')
                                ->label('Fecha de Vencimiento')
                                ->required()
                                ->disabled()
                                ->columnSpan(2),
                        ])
                        ->columns(4),
                    
                        Forms\Components\Section::make('Montos')
                        ->description('Especifica los montos de la factura')
                        ->schema([
                            Forms\Components\TextInput::make('total_amount')
                            ->label('Monto Total')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->extraAttributes(['onkeydown' => 'if(event.key === "e" || event.key === "E") event.preventDefault();'])
                            ->mask(RawJs::make('{
                                mask: Number,
                                thousandsSeparator: ".",
                                radix: ",",
                                scale: 0, // Set scale to 0 to remove decimals
                                mapToRadix: []
                            }'))
                            ->reactive()  
                            ->debounce(750)
                            ->afterStateUpdated(function ($state, callable $set, Get $get) {
                                $totalPaid = (float)($get('total_paid') ?? 0);
                                $pendingAmount = (float)$state - $totalPaid;
                                $set('pending_amount', $pendingAmount);
                                // Verificar si el monto pendiente es cero
                                if ($pendingAmount <= 0) {
                                    $set('status', 'Paid');
                                }
                            })
                            ->columnSpan(2),
                        
                    
                            Forms\Components\TextInput::make('total_paid')
                                ->label('Monto Pagado')
                                ->required()
                                ->numeric()
                                ->prefix('$')
                                ->extraAttributes(['onkeydown' => 'if(event.key === "e" || event.key === "E") event.preventDefault();'])
                                ->reactive()  // Sigue siendo reactivo
                                ->debounce(750)  // Aumenta el tiempo de espera para reducir los problemas de borrado
                                ->disabled(fn (callable $get) => empty($get('total_amount')))
                                ->afterStateUpdated(function ($state, callable $set, Get $get) {
                                    $totalAmount = (float)($get('total_amount') ?? 0);
                                    if ((float)$state > $totalAmount) {
                                        $state = $totalAmount;
                                        $set('total_paid', $state);
                                    }
                                    $pendingAmount = $totalAmount - (float)$state;
                                    $set('pending_amount', $pendingAmount);
                                    // Verificar si el monto pendiente es cero
                                    if ($pendingAmount <= 0) {
                                        $set('status', 'Paid');
                                    }
                                })
                                ->columnSpan(2),
                    
                            Forms\Components\TextInput::make('pending_amount')
                                ->label('Monto Pendiente')
                                ->prefix('$')
                                ->required()
                                ->disabled()
                                ->columnSpan(2),
                        ])
                        ->columns(4),
                    
                    
                        Forms\Components\Section::make('Información Adicional')
                            ->description('Agrega el pdf de la factura y una descripcion de la misma')
                            ->schema([
                                Forms\Components\Select::make('status')
                                    ->label('Estado')
                                    ->options([
                                        'Pending' => 'Pendiente',
                                        'Paid' => 'Pagada',
                                        'Cancelled' => 'Cancelada',
                                        'Partially Paid' => 'Parcialmente Pagada',
                                    ])
                                    ->default('Pending')
                                    ->disabled()
                                    ->columnSpan(2),
    
                                Forms\Components\FileUpload::make('invoice_pdf')
                                    ->label('Factura PDF')
                                    ->required()
                                    ->directory('invoices')
                                    ->acceptedFileTypes(['application/pdf'])
                                    ->maxSize(10240)
                                    ->columnSpanFull(),
    
                                Forms\Components\Textarea::make('description')
                                    ->label('Descripción')
                                    ->columnSpanFull(),
    
                                Forms\Components\Placeholder::make('created_by')
                                    ->label('Creado por')
                                    ->content(fn (Invoice $record): string => $record->createdBy?->name ?? '-')
                                    ->visible(fn (?Invoice $record): bool => $record !== null)
                                    ->columnSpan(2),
                            ])
                            ->columns(4),
                    ])
                    ->columnSpanFull(),
            ])
            ->columns(4);
    }
    
    
    
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('invoice_number')
                    ->label('Numero de Factura')
                    ->sortable()
                    ->searchable()
                    ->limit(50)
                    ->formatStateUsing(fn (string $state): string => 'FEVD' . $state),
                TextColumn::make('client.name')
                    ->label('Cliente')
                    ->sortable()
                    ->searchable(),
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
                        'Pending' => 'warning',
                        'Paid' => 'success',
                        'Cancelled' => 'danger',
                        'Partially Paid' => 'info',
                        default => 'secondary',
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Action::make('viewPdf')
                    ->label('Ver PDF')
                    ->icon('heroicon-o-document-text')
                    ->url(fn ($record) => Storage::url($record->invoice_pdf))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInvoices::route('/'),
            'create' => Pages\CreateInvoice::route('/create'),
            'edit' => Pages\EditInvoice::route('/{record}/edit'),
        ];
    }
}
