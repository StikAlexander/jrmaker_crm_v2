<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Invoice;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PaymentProcessor
{
    /**
     * Crear un nuevo pago y asignarlo a facturas
     *
     * @param array $data Los datos del pago
     * @param Collection|array $invoices Las facturas a las que se asignará el pago
     * @return Payment
     */
    public function createPayment(array $data, $invoices = [])
    {
        try {
            DB::beginTransaction();
            
            // Si no se proporciona una fecha, usar la fecha actual
            if (empty($data['payment_date'])) {
                $data['payment_date'] = Carbon::now()->toDateString();
            }
            
            // Crear el pago
            $payment = Payment::create($data);
            
            // Si hay facturas, adjuntarlas al pago
            if (!empty($invoices)) {
                $this->assignPaymentToInvoices($payment, $invoices);
            }
            
            DB::commit();
            return $payment;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear pago: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Asignar un pago a facturas específicas
     *
     * @param Payment $payment El pago
     * @param Collection|array $invoices Las facturas o IDs de facturas
     * @param array $amounts Opcional: montos específicos para cada factura
     * @return void
     */
    public function assignPaymentToInvoices(Payment $payment, $invoices, array $amounts = [])
    {
        // Convertir array de IDs a colección si es necesario
        if (!$invoices instanceof Collection) {
            if (is_array($invoices) && isset($invoices[0]) && is_numeric($invoices[0])) {
                $invoices = Invoice::findMany($invoices);
            }
        }
        
        $remainingAmount = $payment->amount;
        $pivotData = [];
        
        foreach ($invoices as $index => $invoice) {
            $invoiceId = $invoice instanceof Invoice ? $invoice->id : $invoice;
            
            // Si se proporcionó un monto específico para esta factura, usarlo
            if (isset($amounts[$invoiceId]) || isset($amounts[$index])) {
                $amount = $amounts[$invoiceId] ?? $amounts[$index];
            } else {
                // De lo contrario, calcular el monto automáticamente
                $invoice = $invoice instanceof Invoice ? $invoice : Invoice::find($invoiceId);
                $amount = min($remainingAmount, $invoice->pending_amount);
            }
            
            // Agregar datos para la tabla pivote
            $pivotData[$invoiceId] = ['amount' => $amount];
            $remainingAmount -= $amount;
            
            if ($remainingAmount <= 0) {
                break;
            }
        }
        
        // Sincronizar las relaciones con los montos
        if (!empty($pivotData)) {
            $payment->invoices()->sync($pivotData);
            
            // Si el pago está completo, distribuirlo automáticamente
            if ($payment->payment_status === 'Completed') {
                $payment->distributePayment();
            }
        }
    }
    
    /**
     * Completar un pago (cambia estado y distribuye)
     *
     * @param Payment $payment El pago a completar
     * @param array $paymentData Datos adicionales del pago (transacción, etc)
     * @return bool
     */
    public function completePayment(Payment $payment, array $paymentData = [])
    {
        try {
            DB::beginTransaction();
            
            // Actualizar datos del pago
            $payment->fill($paymentData);
            $payment->payment_status = 'Completed';
            $payment->save();
            
            // Distribuir el pago entre las facturas
            $success = $payment->distributePayment();
            
            if ($success) {
                DB::commit();
                return true;
            }
            
            DB::rollBack();
            return false;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al completar pago: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Calcular el total de pagos por cliente en un periodo
     *
     * @param int $clientId ID del cliente
     * @param string $startDate Fecha de inicio (Y-m-d)
     * @param string $endDate Fecha de fin (Y-m-d)
     * @return float
     */
    public function calculateClientTotalPayments(int $clientId, ?string $startDate = null, ?string $endDate = null)
    {
        $query = Payment::where('client_id', $clientId)
            ->where('payment_status', 'Completed');
            
        if ($startDate) {
            $query->whereDate('payment_date', '>=', $startDate);
        }
        
        if ($endDate) {
            $query->whereDate('payment_date', '<=', $endDate);
        }
        
        return $query->sum('amount');
    }
}
