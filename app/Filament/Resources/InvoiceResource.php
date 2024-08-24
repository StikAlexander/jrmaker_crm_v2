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
    protected static ?string $navigationLabel = 'Facturas';
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
        return $form->schema([
            Forms\Components\Grid::make(2)->schema([
                Forms\Components\TextInput::make('invoice_number')
                    ->label('Numero de Factura')
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

                Forms\Components\DatePicker::make('issue_date')
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        $dueDate = Carbon::parse($state)->addDays(30)->format('Y-m-d');
                        $set('due_date', $dueDate);
                    }),

                Forms\Components\DatePicker::make('due_date')
                    ->required()
                    ->disabled(),

                Forms\Components\TextInput::make('total_amount')
                    ->required()
                    ->numeric()
                    ->extraAttributes(['onkeydown' => 'if(event.key === "e" || event.key === "E") event.preventDefault();'])
                    ->reactive()
                    ->debounce('500ms')
                    ->afterStateUpdated(function ($state, callable $set) {
                        $set('total_paid', null);
                        $set('pending_amount', $state);
                    }),

                Forms\Components\TextInput::make('total_paid')
                    ->required()
                    ->numeric()
                    ->extraAttributes(['onkeydown' => 'if(event.key === "e" || event.key === "E") event.preventDefault();'])
                    ->reactive()
                    ->debounce('500ms')
                    ->disabled(fn (callable $get) => empty($get('total_amount')))
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        $totalAmount = (float)($get('total_amount') ?? 0);
                        if ((float)$state > $totalAmount) {
                            $state = $totalAmount;
                            $set('total_paid', $state);
                            $pendingAmount = $totalAmount - (float)$state;
                            $set('pending_amount', $pendingAmount);
                        }
                    }),

                Forms\Components\TextInput::make('pending_amount')
                    ->required()
                    ->disabled(),

                Forms\Components\Select::make('client_id')
                    ->label('Cliente')
                    ->required()
                    ->relationship('client', 'name')
                    ->options(fn () => \App\Models\User::whereHas('roles', fn ($query) => $query->where('name', 'client'))->pluck('name', 'id'))
                    ->searchable()
                    ->preload(),
            ]),

            Forms\Components\Section::make('Información adicional')
                ->schema([
                    Forms\Components\Select::make('status')
                        ->options([
                            'Pending' => 'Pending',
                            'Paid' => 'Paid',
                            'Cancelled' => 'Cancelled',
                            'Partially Paid' => 'Partially Paid',
                        ])
                        ->default('Pending')
                        ->disabled(),

                    Forms\Components\FileUpload::make('invoice_pdf')
                        ->label('Factura PDF')
                        ->required()
                        ->directory('invoices')
                        ->acceptedFileTypes(['application/pdf'])
                        ->maxSize(10240),

                    Forms\Components\Textarea::make('description'),

                    Forms\Components\Placeholder::make('created_by')
                        ->label('Creado por')
                        ->content(fn (Invoice $record): string => $record->createdBy?->name ?? '-')
                        ->visible(fn (?Invoice $record): bool => $record !== null),
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
                    ->money('COP')
                    ->sortable(),
                TextColumn::make('total_paid')
                    ->label('Monto Pagado')
                    ->money('COP')
                    ->sortable(),
                TextColumn::make('pending_amount')
                    ->label('Monto Pendiente')
                    ->money('COP')
                    ->sortable(),
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
