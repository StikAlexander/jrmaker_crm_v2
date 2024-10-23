<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

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

    /**
     * Hook para la creación de un nuevo pago.
     */
    protected static function boot()
    {
        parent::boot();

        // Generar número de pago al crear un nuevo registro
        static::creating(function ($model) {
            $lastPaymentNumber = static::max('payment_number');
            $model->payment_number = $lastPaymentNumber ? $lastPaymentNumber + 1 : 1;
        });

        // Distribuir el monto cuando el estado cambia a "Completed"
        static::updated(function ($payment) {
            if ($payment->isDirty('payment_status') && $payment->payment_status === 'Completed') {
                $payment->distributePayment();
            }
        });
    }

    /**
     * Relación: Un pago pertenece a un cliente.
     */
    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    /**
     * Relación: Un pago puede estar asociado a muchas facturas.
     */
    public function invoices()
    {
        return $this->belongsToMany(Invoice::class, 'payment_invoice')
            ->withPivot('amount'); // Incluye el campo 'amount' de la tabla pivote
    }

    /**
     * Relación: Pago creado por un usuario (administrador o empleado).
     */
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
            // Si la factura ya está pagada, saltamos a la siguiente
            if ($invoice->status === 'Paid') {
                Log::info('Factura ID: ' . $invoice->id . ' ya está pagada. Se omite.');
                continue;
            }

            $invoicePendingAmount = $invoice->pending_amount;
            $paymentAmount = min($remainingAmount, $invoicePendingAmount);
            Log::info('Monto a pagar para esta factura: ' . $paymentAmount);

            // Actualizar el monto pagado y pendiente
            $invoice->total_paid += $paymentAmount;
            $invoice->pending_amount = max($invoice->total_amount - $invoice->total_paid, 0);
            Log::info('Total pagado: ' . $invoice->total_paid . ', Pendiente: ' . $invoice->pending_amount);

            // Marcar factura como pagada si el monto pendiente es cero
            if ($invoice->pending_amount <= 0) {
                $invoice->status = 'Paid';
                Log::info('Factura ID: ' . $invoice->id . ' marcada como Pagada.');
            }

            $invoice->save();

            // Actualizar la tabla pivote con el monto pagado
            $this->invoices()->updateExistingPivot($invoice->id, ['amount' => $paymentAmount]);

            // Reducir el monto restante del pago
            $remainingAmount -= $paymentAmount;

            if ($remainingAmount <= 0) {
                Log::info('Monto total distribuido. Fin del proceso.');
                break;
            }
        }

        Log::info('Distribución de pago finalizada para Payment ID: ' . $this->id);
    }
}
