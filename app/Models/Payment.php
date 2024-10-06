<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Events\PaymentCompleted;

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

        // Dispara el evento cuando se actualiza el estado del pago a "Completed"
        static::updated(function ($payment) {
            if ($payment->payment_status === 'Completed') {
                event(new PaymentCompleted($payment));
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
}
