<?php

namespace App\Filament\Client\Resources;

use App\Jobs\CheckWompiPaymentStatus;
use App\Models\Invoice;
use App\Models\Payment;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\BulkAction;
use Illuminate\Support\Collection;
use App\Services\PaymentService;
use Filament\Resources\Resource;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

class ClientInvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;
    protected static ?string $recordTitleAttribute = 'invoice_number';
    protected static ?string $pluralLabel = 'Facturas Pendientes';
    protected static ?string $singularLabel = 'Factura Pendiente';
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'Mi Cuenta';
    protected static ?int $navigationSort = 1;

    // Filtrar facturas del cliente autenticado
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('client_id', auth()->id()) 
            ->where('status', 'Pending'); 
    }
    
    public static function form(Form $form): Form
    {
        return $form->schema([
            // Campos del formulario si es necesario
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->description('Selecciona una o más facturas pendientes para proceder con el pago.')
            ->emptyStateIcon('heroicon-o-document')
            ->emptyStateHeading('Sin facturas pendientes de pago')
            ->emptyStateDescription('No hay facturas pendientes en este momento.')
            ->columns([
                // Aquí están tus columnas
            ])
            ->actions([
                Action::make('viewPdf')
                    ->label('Ver PDF')
                    ->icon('heroicon-o-document-text')
                    ->url(fn ($record) => Storage::url($record->invoice_pdf))
                    ->openUrlInNewTab()
                    ->tooltip('Haz clic para ver el PDF de la factura')
                    ->button()
                    ->color('danger')
                    ->extraAttributes(['class' => 'text-white']),
            ])
            ->bulkActions([
                BulkAction::make('paySelected')
                    ->label('Pagar seleccionadas')
                    ->tooltip('Selecciona las facturas pendientes para proceder con el pago.')
                    ->action(function (Collection $records) {
                        $totalAmount = $records->sum('pending_amount');
    
                        $payment = Payment::create([
                            'client_id' => auth()->id(),
                            'amount' => $totalAmount,
                            'payment_status' => 'Pending',
                            'reference' => 'PAYMENT_' . uniqid(),
                            'external_reference' => 'ref_' . uniqid(),
                        ]);
    
                        foreach ($records as $invoice) {
                            $payment->invoices()->attach($invoice->id, ['amount' => $invoice->pending_amount]);
                        }
    
                        $paymentService = app(PaymentService::class);
                        $paymentLink = $paymentService->generatePaymentLink($payment, route('payment.callback'));
    
                        if ($paymentLink) {
                            $payment->update(['payment_link' => $paymentLink]);
                            CheckWompiPaymentStatus::dispatch($payment)->delay(now()->addMinutes(2)->addSeconds(15));
    
                            return redirect()->away($paymentLink);
                        } else {
                            Notification::make()
                                ->title('Error en el pago')
                                ->body('No se pudo generar el enlace de pago.')
                                ->danger()
                                ->send();
                        }
                    })
                    ->color('success')
                    ->icon('heroicon-o-credit-card'),
            ]);
    }
    public static function getHeaderWidgets(): array
    {
        return [];
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ClientInvoiceResource\Pages\ListClientInvoice::route('/'),
            'create' => ClientInvoiceResource\Pages\CreateClientInvoice::route('/create'),
            'edit' => ClientInvoiceResource\Pages\EditClientInvoice::route('/{record}/edit'),
        ];
    }
}
