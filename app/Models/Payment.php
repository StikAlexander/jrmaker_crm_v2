<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Events\PaymentCompleted;

class Payment extends Model 
{
    use HasFactory;

    protected $fillable = [
        'Payment_number',
        'issue_date',
        'due_date',
        'client_id',
        'payment_date',
        'amount',
        'payment_link',  
        'payment_status',  
        'api_response',  
        'external_reference',  
    ];

    protected static function boot()
    {
        parent::boot();
    
        // Genera el número de pago y las fechas al crear un nuevo pago
        static::creating(function ($model) {
            $lastPaymentNumber = static::max('Payment_number');
            $model->Payment_number = $lastPaymentNumber ? $lastPaymentNumber + 1 : 1;

            if (empty($model->issue_date)) {
                $model->issue_date = Carbon::now()->toDateString(); 
            }

            $issueDate = Carbon::parse($model->issue_date);
            $model->due_date = $issueDate->addDays(5)->format('Y-m-d');
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
