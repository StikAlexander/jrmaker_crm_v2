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
    /**
     * Crear un nuevo pago y asignarlo a facturas
     *
     * @param array $data Los datos del pago
     * @param Collection|array $invoices Las facturas a las que se asignará el pago
     * @return Payment
     * @throws \App\Exceptions\PaymentException Si hay un error al crear el pago
     */
    public function createPayment(array $data, $invoices = [])
    {
        // Validación previa de datos requeridos
        if (empty($data['client_id'])) {
            throw new \InvalidArgumentException("El campo 'client_id' es obligatorio para crear un pago.");
        }

        if (empty($data['amount']) || !is_numeric($data['amount']) || $data['amount'] <= 0) {
            throw new \InvalidArgumentException("El monto del pago debe ser un número positivo.");
        }

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
            
            Log::info("Pago creado con éxito. ID: {$payment->id}, Monto: {$payment->amount}");
            return $payment;
        } catch (\Exception $e) {
            DB::rollBack();
            
            // Registro detallado del error
            Log::error('Error al crear pago', [
                'datos' => array_diff_key($data, array_flip(['api_response'])), // Excluye datos sensibles
                'mensaje' => $e->getMessage(),
                'archivo' => $e->getFile(),
                'línea' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Lanzar excepción personalizada para manejo superior
            throw new \App\Exceptions\PaymentException("Error al procesar el pago: " . $e->getMessage(), 0, $e);
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
    /**
     * Completar un pago (cambia estado y distribuye)
     *
     * @param Payment $payment El pago a completar
     * @param array $paymentData Datos adicionales del pago (transacción, etc)
     * @return bool
     * @throws \App\Exceptions\PaymentException Si hay un error al completar el pago
     */
    public function completePayment(Payment $payment, array $paymentData = [])
    {
        // Validaciones previas
        if (!$payment->id) {
            throw new \InvalidArgumentException("El pago debe estar guardado en la base de datos.");
        }

        // Verificar si ya está completado
        if ($payment->payment_status === 'Completed') {
            Log::info("El pago ID: {$payment->id} ya está en estado Completed. No se requiere acción.");
            return true;
        }

        // Verificar si tiene facturas asociadas
        if ($payment->invoices->isEmpty()) {
            throw new \App\Exceptions\PaymentException("El pago ID: {$payment->id} no tiene facturas asociadas y no puede ser completado.");
        }

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
                Log::info("Pago completado con éxito. ID: {$payment->id}, Monto: {$payment->amount}");
                return true;
            }
            
            DB::rollBack();
            throw new \App\Exceptions\PaymentException("No se pudo distribuir el pago ID: {$payment->id} entre las facturas.");
        } catch (\App\Exceptions\PaymentException $e) {
            DB::rollBack();
            // Reenviar excepciones de pago
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            
            // Registro detallado del error
            Log::error('Error al completar pago', [
                'payment_id' => $payment->id,
                'mensaje' => $e->getMessage(),
                'archivo' => $e->getFile(),
                'línea' => $e->getLine()
            ]);
            
            throw new \App\Exceptions\PaymentException("Error al completar el pago: " . $e->getMessage(), 0, $e);
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
