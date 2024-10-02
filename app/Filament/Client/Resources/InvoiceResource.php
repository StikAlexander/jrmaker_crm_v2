<?php

namespace App\Filament\Client\Resources;

use App\Jobs\CheckPaymentStatus;
use App\Models\Invoice;
use App\Models\Payment;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\BulkAction;
use Illuminate\Support\Collection;
use App\Services\PaymentService;
use Filament\Resources\Resource;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;
    protected static ?string $recordTitleAttribute = 'invoice_number';
    protected static ?string $pluralLabel = 'Facturas';
    protected static ?string $singularLabel = 'Factura';
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'Contabilidad';
    protected static ?int $navigationSort = 1;

    // Filtrar facturas del cliente autenticado y pendientes
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('client_id', auth()->id())
            ->where('status', 'Pending');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            // Esquema del formulario
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
                    ->limit(50)
                    ->alignCenter(), 
                    

                TextColumn::make('issue_date')
                    ->label('Fecha de Emisión')
                    ->date()
                    ->sortable()
                    ->alignCenter(), 

                TextColumn::make('due_date')
                    ->label('Fecha de Vencimiento')
                    ->date()
                    ->sortable()
                    ->alignCenter(), 

                TextColumn::make('total_amount')
                    ->label('Monto Total')
                    ->sortable()
                    ->formatStateUsing(fn (string $state): string => '$' . number_format($state, 0, ',', '.'))
                    ->alignCenter(), 

                TextColumn::make('pending_amount')
                    ->label('Monto Pendiente')
                    ->sortable()
                    ->formatStateUsing(fn (string $state): string => '$' . number_format($state, 0, ',', '.'))
                    ->alignCenter(), 

                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Pending' => 'warning',
                        'Paid' => 'success',
                        'Cancelled' => 'danger',
                        default => 'secondary',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'Pending' => 'heroicon-o-clock', // Ícono de reloj para estado 'Pendiente'
                        'Paid' => 'heroicon-o-check-circle',
                        'Cancelled' => 'heroicon-o-x-circle',
                        default => 'heroicon-o-question-mark-circle',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'Pending' => 'Pendiente',
                        'Paid' => 'Pagada',
                        'Cancelled' => 'Cancelada',
                        default => $state,
                    })
                    ->alignCenter(), 
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
                    ->tooltip('Selecciona las facturas pendientes para proceder con el pago.')
                    ->action(function (Collection $records) {
                        $totalAmount = $records->sum('pending_amount');

                        // Crear el Payment
                        $Payment = Payment::create([
                            'client_id' => auth()->id(),
                            'amount' => $totalAmount,
                            'payment_status' => 'Pending',
                        ]);

                        // Asignar external_reference al ID del Payment
                        $Payment->external_reference = $Payment->id;
                        $Payment->save(); // Guardar el external_reference en la base de datos   

                        // Asociar las facturas al Payment con el campo 'amount' en la tabla pivot
                        foreach ($records as $invoice) {
                            $Payment->invoices()->attach($invoice->id, ['amount' => $invoice->pending_amount]);
                        }

                        // Llamada al servicio de pago
                        $paymentService = app(PaymentService::class);
                        $paymentLink = $paymentService->generatePaymentLink($Payment, route('payment.callback'));

                        if ($paymentLink) {
                            // Actualizar el enlace de pago
                            $Payment->update(['payment_link' => $paymentLink]);

                            CheckPaymentStatus::dispatch($Payment)->delay(now()->addMinutes(2)->addSeconds(30));

                            return redirect()->away($paymentLink);
                        } else {
                            Notification::make()
                                ->title('Error en el pago')
                                ->body('No se pudo generar el enlace de pago.')
                                ->danger()
                                ->send();
                        }
                    })
                    ->requiresConfirmation('¿Estás seguro de que deseas pagar las facturas seleccionadas?') // Confirmación adicional
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
            'index' => InvoiceResource\Pages\ListInvoices::route('/'),
            'create' => InvoiceResource\Pages\CreateInvoice::route('/create'),
            'edit' => InvoiceResource\Pages\EditInvoice::route('/{record}/edit'),
        ];
    }
}
