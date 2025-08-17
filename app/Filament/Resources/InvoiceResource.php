<?php

namespace App\Filament\Resources;

use App\Constants\InvoiceStatus;
use App\Filament\Resources\InvoiceResource\Forms\InvoiceForm;
use App\Filament\Resources\InvoiceResource\Pages;
use App\Filament\Resources\InvoiceResource\Tables\InvoiceTableSchema;
use App\Models\Invoice;
use App\Services\InvoiceService;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Filament\GlobalSearch\GlobalSearchResult;
use Illuminate\Support\Collection;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;
    protected static ?string $recordTitleAttribute = 'invoice_number';
    protected static ?string $pluralLabel = 'Facturas';
    protected static ?string $singularLabel = 'Factura';
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Contabilidad';
    protected static ?int $navigationSort = 1;
    protected static ?string $pollingInterval = '30s';

    public static function getGlobalSearchResults(string $search): Collection
    {
        $searchPrefix = InvoiceStatus::INVOICE_PREFIX;
        
        if (str_starts_with(strtoupper($search), $searchPrefix)) {
            $search = substr($search, strlen($searchPrefix));
            
            return static::getModel()::query()
                ->where(function ($query) use ($search) {
                    $query->where('invoice_number', 'like', "%{$search}%")
                          ->orWhere('description', 'like', "%{$search}%")
                          ->orWhere('total_amount', 'like', "%{$search}%");
                })
                ->get()
                ->map(function (Model $record) {
                    return new GlobalSearchResult(
                        title: $record->display_number,
                        url: static::getUrl('edit', ['record' => $record]),
                        details: [
                            'Monto Total' => $record->formatted_total_amount,
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
        return $record->display_number;
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Monto Total' => $record->formatted_total_amount,
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
            // Temporarily commented out to resolve rateLimit error
            /*Action::make('edit')
                ->url(static::getUrl('edit', ['record' => $record])),*/
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return static::getModel()::query()
            ->with(['client', 'createdBy', 'payments']) // Carga anticipada de relaciones
            ->orderBy('created_at', 'desc'); // Ordenar por fecha de creación
    }
    
    
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        ...InvoiceForm::getBasicInfoSchema(),
                        ...InvoiceForm::getAmountsSchema(),
                        ...InvoiceForm::getAdditionalInfoSchema(),
                    ])
                    ->columns(1)
                    ->maxWidth(MaxWidth::FiveExtraLarge)
                    ->extraAttributes([
                        'class' => 'mx-auto mt-10',
                    ]),
            ])
            ->uniqueValidationIgnoresRecordByDefault();
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(InvoiceTableSchema::getColumns())
            ->actions(InvoiceTableSchema::getActions())
            ->bulkActions(InvoiceTableSchema::getBulkActions())
            ->deferFilters(false);
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
