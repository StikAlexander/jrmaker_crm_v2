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
        return $form->schema([ /* Esquema del formulario si lo necesitas */ ]);
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
                // Acción para ver detalles de la factura
                ViewAction::make()
                    ->label('Ver')
                    ->modalHeading('Detalles de la Factura')
                    ->modalWidth('4xl')
                    ->tooltip('Ver detalles de la factura'),

                // Botón de pago para una sola factura
                Action::make('pay')
                    ->label('Pagar')
                    ->icon('heroicon-o-credit-card')
                    ->visible(fn ($record) => $record->status === 'Pending') // Visible solo si está pendiente
                    ->tooltip('Realizar el pago de la factura')
                    ->requiresConfirmation()
                    ->color('success')
                    ->action(function (Invoice $record) {
                        // Lógica para pago único de la factura
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

                        // Llamada a la API externa para generar un enlace de pago
                        $paymentLink = $this->generatePaymentLink($voucherPayment);
                        $voucherPayment->update(['payment_link' => $paymentLink]);

                        // Notificación de éxito
                        Notification::make()
                            ->title('Enlace de pago generado')
                            ->body('El enlace de pago para las facturas seleccionadas ha sido generado.')
                            ->success()
                            ->send();
                    })
                    ->color('success')
                    ->icon('heroicon-o-credit-card'),
            ]);
    }

    // Método para generar el enlace de pago llamando a la API externa
    private function generatePaymentLink(VoucherPayment $voucherPayment)
    {
        try {
            // Intento de hacer la solicitud a la API de pago
            $client = new \GuzzleHttp\Client();
            $response = $client->post('https://api.paymentprovider.com/create-link', [
                'json' => [
                    'amount' => $voucherPayment->amount,
                    'description' => 'Pago de varias facturas',
                    'client_id' => $voucherPayment->client_id,
                    'callback_url' => route('payment.callback'),
                ]
            ]);
    
            $data = json_decode($response->getBody()->getContents(), true);
            
            // Verificar si la API devolvió un error
            if (!isset($data['payment_link'])) {
                throw new \Exception('Error al generar el enlace de pago.');
            }
    
            return $data['payment_link'];
    
        } catch (\Exception $e) {
            // Loguear el error
            \Log::error('Error al generar el enlace de pago: ' . $e->getMessage());
    
            // Notificar al usuario sobre el error
            Notification::make()
                ->title('Error en el pago')
                ->body('No se pudo generar el enlace de pago. Inténtalo más tarde.')
                ->danger()
                ->send();
    
            return null;
        }
    }    
}
