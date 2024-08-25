<?php

namespace App\Filament\Resources\VoucherPaymentResource\Pages;

use App\Filament\Resources\VoucherPaymentResource;
use App\Models\Invoice;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

namespace App\Filament\Resources\VoucherPaymentResource\Pages;

use App\Filament\Resources\VoucherPaymentResource;
use App\Models\Invoice;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class CreateVoucherPayment extends CreateRecord
{
    protected static string $resource = VoucherPaymentResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        // Añade el ID del usuario autenticado
        $data['created_by'] = auth()->id();

        // Genera el número de voucher
        $lastVoucherNumber = static::getModel()::max('voucher_number');
        $data['voucher_number'] = $lastVoucherNumber ? $lastVoucherNumber + 1 : 1;

        // Verifica si 'invoice_id' existe
        if (!isset($data['invoice_id'])) {
            throw new \Exception('No invoices were selected.');
        }

        // Extrae los IDs de las facturas seleccionadas
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

            if ($remainingAmount <= 0) {
                break;
            }
        }

        // Renombrar el archivo de soporte de pago (PDF) después de que el voucher ha sido creado
        if ($voucherPayment && isset($data['payment_support'])) {
            $oldPath = $data['payment_support']; // Path original
            $extension = pathinfo($oldPath, PATHINFO_EXTENSION); // Obtener la extensión
            $newFilename = 'SP' . $voucherPayment->voucher_number . '.' . $extension; // Crear el nuevo nombre
            $newPath = 'voucher_payments/' . $newFilename;

            Storage::move($oldPath, $newPath); // Mover el archivo a la nueva ubicación

            // Actualizar la ruta del archivo en la base de datos
            $voucherPayment->update(['payment_support' => $newPath]);
        }

        return $voucherPayment;
    }
}

