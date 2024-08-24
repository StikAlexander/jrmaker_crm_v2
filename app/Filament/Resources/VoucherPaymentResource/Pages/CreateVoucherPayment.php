<?php

namespace App\Filament\Resources\VoucherPaymentResource\Pages;

use App\Filament\Resources\VoucherPaymentResource;
use App\Models\Invoice;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class CreateVoucherPayment extends CreateRecord
{
    protected static string $resource = VoucherPaymentResource::class;

    // Método mount para inicializar el estado del formulario
    public function mount(): void
    {
        parent::mount();

        // Aquí puedes predefinir los campos si es necesario.
        // Por ejemplo, si deseas que el campo 'client_id' tenga un valor predeterminado,
        // puedes hacerlo así:
        // $this->form->fill([
        //     'client_id' => valor_predeterminado,
        // ]);
    }

    protected function handleRecordCreation(array $data): Model
    {
        // Añade el ID del usuario autenticado
        $data['created_by'] = auth()->id();
    
        // Genera el número de voucher
        $lastVoucherNumber = static::getModel()::max('voucher_number');
        $data['voucher_number'] = $lastVoucherNumber ? $lastVoucherNumber + 1 : 1;
    
        //dd($data);
        // Verificar si 'invoice_id' existe y es un array
        // if (!isset($data['invoice_id']) || !is_array($data['invoice_id'])) {
        //     throw new \Exception('No invoices were selected.');
        // }
        if (!isset($data['invoice_id'])) {
            // Manejar el caso en que 'invoice_id' no esté definido
            throw new \Exception('No invoices were selected.');
        }
        
        // Extraer los IDs de las facturas seleccionadas
        $invoiceIds = $data['invoice_id'];
        
    
        // Extraer los IDs de las facturas seleccionadas
        $invoiceIds = $data['invoice_id'];
        unset($data['invoice_id']); // Elimina 'invoice_id' del array $data
    
        // Crea el Voucher Payment
        $voucherPayment = static::getModel()::create($data);
    
        // Distribuir el monto entre las facturas seleccionadas
        $totalAmount = $data['amount'];
        $remainingAmount = $totalAmount;
    
        foreach ($invoiceIds as $invoiceId) {
            $invoice = Invoice::find($invoiceId);
            $amountForInvoice = min($remainingAmount, $invoice->pending_amount);
    
            // Asociar las facturas seleccionadas al mismo voucher
            $voucherPayment->invoices()->attach($invoiceId, ['amount' => $amountForInvoice]);
    
            // Actualizar el estado de la factura
            $invoice->pending_amount -= $amountForInvoice;
            $invoice->total_paid += $amountForInvoice;
            $invoice->status = $invoice->pending_amount <= 0 ? 'Paid' : 'Pending';
            $invoice->save();
    
            $remainingAmount -= $amountForInvoice;
    
            // Si el monto restante es 0, no es necesario seguir procesando
            if ($remainingAmount <= 0) {
                break;
            }
        }
    
        // Manejar el archivo de soporte de pago
        if (isset($data['payment_support']) && $data['payment_support'] instanceof \Illuminate\Http\UploadedFile) {
            $tempPath = $data['payment_support']->store('public/voucher_payments');
    
            if ($tempPath) {
                $extension = pathinfo($tempPath, PATHINFO_EXTENSION);
                $newFilename = 'SP' . $voucherPayment->voucher_number . '.' . $extension;
                $newPath = 'public/voucher_payments/' . $newFilename;
    
                Storage::move($tempPath, $newPath);
                $voucherPayment->update(['payment_support' => $newPath]);
            }
        }
    
        return $voucherPayment;
    }
    
}
