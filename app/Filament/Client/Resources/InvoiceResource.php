<?php

namespace App\Filament\Client\Resources;


use App\Models\Invoice;
use App\Models\VoucherPayment;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Actions\BulkAction;
use Illuminate\Support\Collection;
use Filament\Notifications\Notification;
use App\Services\PaymentService;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    // Filtrar solo las facturas del cliente autenticado y con estado Pendiente
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('client_id', auth()->id())
            ->where('status', 'Pending'); // Mostrar solo facturas pendientes
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            // Esquema del formulario si lo necesitas
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->description('Selecciona una o más facturas pendientes para proceder con el pago.') 
            ->columns([
                TextColumn::make('invoice_number')
                    ->label('Número de Factura')
                    ->sortable()
                    ->searchable()
                    ->limit(50),
                
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
                ViewAction::make()
                    ->label('Ver')
                    ->modalHeading('Detalles de la Factura')
                    ->modalWidth('4xl')
                    ->tooltip('Ver detalles de la factura'),
            ])
            ->bulkActions([
                BulkAction::make('paySelected')
                    ->label('Pagar seleccionadas')
                    ->tooltip('Selecciona las facturas pendientes para proceder con el pago.') // Mensaje claro sobre qué hacer
                    ->action(function (Collection $records) {
                        $invoiceIds = $records->pluck('id')->toArray();
                
                        // Crear el VoucherPayment con las facturas seleccionadas
                        $voucherPayment = VoucherPayment::create([
                            'client_id' => auth()->id(),
                            'amount' => $records->sum('pending_amount'),
                            'payment_status' => 'Pending',
                        ]);
                
                        // Asociar las facturas seleccionadas con el VoucherPayment
                        foreach ($records as $invoice) {
                            $voucherPayment->invoices()->attach($invoice->id, ['amount' => $invoice->pending_amount]);
                        }
                
                        // Llamada al servicio de pago para generar el enlace
                        $paymentService = app(PaymentService::class);
                        $paymentLink = $paymentService->generatePaymentLink(
                            $voucherPayment->amount,
                            'Pago de varias facturas',
                            $voucherPayment->invoices,
                            route('payment.callback')
                        );
                
                        if ($paymentLink) {
                            $voucherPayment->update(['payment_link' => $paymentLink]);
                
                            Notification::make()
                                ->title('Enlace de pago generado')
                                ->body('Serás redirigido automáticamente en unos segundos.')
                                ->success()
                                ->send();
                
                            // Redirigir al usuario al enlace de pago
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

        protected static function getHeaderWidgets(): array
    {
        return [
            
        ];
    }


    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => InvoiceResource\Pages\ListInvoices::route('/'),
            'create' => InvoiceResource\Pages\CreateInvoice::route('/create'),
            'edit' => InvoiceResource\Pages\EditInvoice::route('/{record}/edit'),
        ];
    }
}
