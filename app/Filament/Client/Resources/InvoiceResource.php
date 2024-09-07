<?php

namespace App\Filament\Client\Resources;

use App\Models\Invoice;
use App\Models\VoucherPayment;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Actions\BulkAction;
use Illuminate\Support\Collection;
use Filament\Notifications\Notification;
use App\Services\PaymentService; // Asegúrate de tener este servicio configurado correctamente

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    // Filtrar solo las facturas del cliente autenticado
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('client_id', auth()->id()); 
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

                Action::make('pay')
                    ->label('Pagar')
                    ->icon('heroicon-o-credit-card')
                    ->visible(fn ($record) => $record->status === 'Pending')
                    ->tooltip('Realizar el pago de la factura')
                    ->requiresConfirmation()
                    ->color('success')
                    ->action(function (Invoice $record) {
                        Notification::make()
                            ->title('Pago procesado')
                            ->body("Factura {$record->invoice_number} procesada correctamente.")
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                BulkAction::make('paySelected')
                    ->label('Pagar seleccionadas')
                    ->action(function (Collection $records) {
                        $invoiceIds = $records->pluck('id')->toArray();

                        $voucherPayment = VoucherPayment::create([
                            'client_id' => auth()->id(),
                            'amount' => $records->sum('pending_amount'),
                            'payment_status' => 'Pending',
                        ]);

                        foreach ($records as $invoice) {
                            $voucherPayment->invoices()->attach($invoice->id, ['amount' => $invoice->pending_amount]);
                        }

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
                                ->body('El enlace de pago para las facturas seleccionadas ha sido generado.')
                                ->success()
                                ->send();
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
