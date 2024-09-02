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
use Illuminate\Database\Eloquent\Builder;
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
                                            ->rules(['regex:/^\d+$/']),
                                        Select::make('client_id')
                                            ->label('Cliente')
                                            ->required()
                                            ->relationship('client', 'name')
                                            ->searchable()
                                            ->preload(),
                                    ]),
                                Grid::make()
                                    ->columns(2)
                                    ->schema([
                                        DatePicker::make('issue_date')
                                            ->label('Fecha de Emisión')
                                            ->required()
                                            ->reactive()
                                            ->maxDate(now())
                                            ->afterStateUpdated(function ($state, callable $set) {
                                                $dueDate = Carbon::parse($state)->addDays(30)->format('Y-m-d');
                                                $set('due_date', $dueDate);
                                            }),
                                        DatePicker::make('due_date')
                                            ->label('Fecha de Vencimiento')
                                            ->required()
                                            ->disabled(),
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
                                            ->debounce(500),
                                        TextInput::make('total_paid')
                                            ->label('Monto Pagado')
                                            ->required()
                                            ->numeric()
                                            ->prefix('$')
                                            ->reactive()
                                            ->debounce(500),
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
                                                'Cancelled' => 'Cancelada',
                                                'Partially Paid' => 'Parcialmente Pagada',
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
                TextColumn::make('invoice_number')
                    ->label('Numero de Factura')
                    ->sortable()
                    ->searchable()
                    ->limit(50)
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
                        'Pending' => 'warning',
                        'Paid' => 'success',
                        'Cancelled' => 'danger',
                        'Partially Paid' => 'info',
                        default => 'secondary',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'Pending' => 'Pendiente',
                        'Paid' => 'Pagada',
                        'Cancelled' => 'Cancelada',
                        'Partially Paid' => 'Parcialmente Pagada',
                        default => $state, 
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Ver')
                    ->modalHeading('Detalles de la Factura')
                    ->modalWidth('4xl')
                    ->tooltip('Ver detalles de la factura')
                    ->iconButton(),
                Tables\Actions\EditAction::make()
                    ->label('Editar')
                    ->tooltip('Editar Factura')
                    ->url(fn ($record) => static::getUrl('edit', ['record' => $record]))
                    ->iconButton(),
                Tables\Actions\DeleteAction::make()
                    ->label('')
                    ->icon('heroicon-o-trash')
                    ->size(ActionSize::Large)
                    ->tooltip('Eliminar Factura')
                    ->iconButton(),
                Action::make('viewPdf')
                    ->label('')
                    ->icon('heroicon-o-document-text')
                    ->size(ActionSize::Large)
                    ->url(fn ($record) => Storage::url($record->invoice_pdf))
                    ->openUrlInNewTab()
                    ->tooltip('Ver PDF')
                    ->iconButton(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInvoices::route('/'),
            'edit' => Pages\EditInvoice::route('/{record}/edit'),
            'create' => Pages\CreateInvoice::route('/create'),
        ];
    }
}
