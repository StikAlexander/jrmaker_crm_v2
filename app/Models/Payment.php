<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\FacadesLog;

class Payment extends Model 
{
    use HasFactory;

    protected $fillable = [
        'payment_number',
        'client_id',
        'payment_date',
        'amount',
        'payment_link',
        'payment_status',
        'api_response',
        'external_reference',
        'transaction_id',  
        'payment_method_type',  
        'reference',  
        'payment_link_id',  
    ];

    protected static function boot()
    {
        parent::boot();

        // Genera el número de pago al crear un nuevo pago
        static::creating(function ($model) {
            $lastPaymentNumber = static::max('payment_number');
            $model->payment_number = $lastPaymentNumber ? $lastPaymentNumber + 1 : 1;
        });

        // Ejecuta la lógica de distribución de pagos cuando el estado cambia a "Completed"
        static::updated(function ($payment) {
            if ($payment->isDirty('payment_status') && $payment->payment_status === 'Completed') {
                // Llama a la función para distribuir el monto entre las facturas
                $payment->distributePayment();
            }
        });
    }

    // Relaciones
    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function invoices()
    {
        return $this->belongsToMany(Invoice::class, 'payment_invoice')->withPivot('amount');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Distribuir el monto del pago entre las facturas asociadas.
     */
    public function distributePayment()
    {
        $remainingAmount = $this->amount;
        Log::info('Iniciando distribución de pago para Payment ID: ' . $this->id);
    
        foreach ($this->invoices as $invoice) {
            // Si la factura ya está pagada, omitimos la actualización
            if ($invoice->status === 'Paid') {
                Log::info('Factura ID: ' . $invoice->id . ' ya está pagada. Se omite.');
                continue;
            }
    
            Log::info('Procesando factura ID: ' . $invoice->id . ' con monto pendiente: ' . $invoice->pending_amount);
    
            $invoicePendingAmount = $invoice->pending_amount;
    
            // Calcular el monto a pagar para esta factura
            $paymentAmount = min($remainingAmount, $invoicePendingAmount);
            Log::info('Monto a pagar para esta factura: ' . $paymentAmount);
    
            // Actualizar los campos de la factura
            $invoice->total_paid += $paymentAmount;
            $invoice->pending_amount = max($invoice->total_amount - $invoice->total_paid, 0);
            Log::info('Monto pagado total de la factura ahora es: ' . $invoice->total_paid);
            Log::info('Monto pendiente de la factura ahora es: ' . $invoice->pending_amount);
    
            // Cambia el estado de la factura a 'Paid' si ya no tiene saldo pendiente
            if ($invoice->pending_amount <= 0) {
                $invoice->status = 'Paid';
                Log::info('Factura ID: ' . $invoice->id . ' marcada como Pagada');
            }
    
            // Guardar los cambios en la factura
            $invoice->save();
    
            // Actualizar la tabla pivote con el monto pagado
            $this->invoices()->updateExistingPivot($invoice->id, ['amount' => $paymentAmount]);
    
            // Reducir el monto restante
            $remainingAmount -= $paymentAmount;
    
            // Salir si no queda monto por distribuir
            if ($remainingAmount <= 0) {
                log::info('Monto total distribuido. Saliendo de la distribución.');
                break;
            }
        }
    
        Log::info('Pago distribuido exitosamente para Payment ID: ' . $this->id);
    }
    
}
