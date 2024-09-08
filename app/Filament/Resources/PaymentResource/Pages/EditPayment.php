<?php

namespace App\Filament\Resources\PaymentResource\Pages;

use App\Filament\Resources\PaymentResource;
use App\Models\Invoice;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Support\RawJs;

class EditPayment extends EditRecord
{
    protected static string $resource = PaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    // Sobrescribimos el método mount para cargar las facturas y otros datos
    public function mount(int | string $record): void
    {
        parent::mount($record);
    
        // Cargar todos los datos relevantes en el formulario
        $this->form->fill([
            'client_id' => $this->record->client_id,
            'payment_date' => $this->record->payment_date,
            'amount' => $this->record->amount,
            'payment_support' => $this->record->payment_support,
            'invoice_id' => $this->record->invoices->pluck('id')->toArray(),
            'Payment_number' => $this->record->Payment_number,  // Agregar el número de Payment aquí
        ]);
    }

    // Esquema del formulario para asegurar que los campos se carguen correctamente
    protected function getFormSchema(): array
    {
        return [
            Forms\Components\Select::make('client_id')
                ->label('Cliente')
                ->relationship('client', 'name')
                ->required()
                ->reactive()
                ->afterStateUpdated(function (callable $set) {
                    // Resetear las facturas seleccionadas al cambiar el cliente
                    $set('invoice_id', []);
                }),

            Select::make('invoice_id')
                ->label('Factura(s)')
                ->options(function (callable $get) {
                    $clientId = $get('client_id');
                    if (!$clientId) return [];
                    return Invoice::where('client_id', $clientId)
                        ->where('status', 'Pending')
                        ->pluck('invoice_number', 'id');
                })
                ->searchable()
                ->required()
                ->multiple()
                ->reactive(),

            Forms\Components\DatePicker::make('payment_date')
                ->label('Fecha de Pago')
                ->required(),

            Forms\Components\TextInput::make('amount')
                ->label('Monto')
                ->required()
                ->numeric()
                ->mask(RawJs::make('$money($input)'))
                ->stripCharacters(',')
                ->prefix('$')
                ->reactive()
                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                    $invoiceIds = $get('invoice_id');
                    if (!$invoiceIds) return;
                    $totalAmount = Invoice::whereIn('id', $invoiceIds)->sum('total_amount');
                    if ($state > $totalAmount) {
                        $set('amount', $totalAmount);
                    }
                }),

            Forms\Components\FileUpload::make('payment_support')
                ->label('Soporte de Pago')
                ->directory('Payment_payments')
                ->acceptedFileTypes(['application/pdf'])
                ->maxSize(10240)
                ->required(),

            Forms\Components\TextInput::make('Payment_number')
                ->label('Número de Payment')
                ->disabled()
                ->required(),
        ];
    }

    // Actualización del record con firma correcta
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        // Actualizar los datos del registro
        $record->update($data);

        // Logica adicional para la distribucion del monto entre las facturas seleccionadas
        $invoiceIds = $data['invoice_id'];
        $totalAmount = $data['amount'];
        $remainingAmount = $totalAmount;

        foreach ($invoiceIds as $invoiceId) {
            $invoice = Invoice::find($invoiceId);
            $amountForInvoice = min($remainingAmount, $invoice->pending_amount);
            
            // Actualizar la relación de las facturas con el Payment
            $record->invoices()->syncWithoutDetaching([$invoiceId => ['amount' => $amountForInvoice]]);

            // Actualizar el estado de la factura
            $invoice->pending_amount -= $amountForInvoice;
            $invoice->total_paid += $amountForInvoice;
            $invoice->status = $invoice->pending_amount <= 0 ? 'Paid' : 'Pending';
            $invoice->save();

            $remainingAmount -= $amountForInvoice;

            if ($remainingAmount <= 0) {
                break;
            }
        }

        return $record;
    }
}
