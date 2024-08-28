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
use Illuminate\Contracts\Support\Htmlable;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Support\Enums\MaxWidth;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Tabs;


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
                    ->maxWidth(MaxWidth::FiveExtraLarge)  // Limitar el ancho de la tarjeta
                    ->extraAttributes([
                        'class' => 'mx-auto mt-10',  // Centrar la tarjeta en la pantalla
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


