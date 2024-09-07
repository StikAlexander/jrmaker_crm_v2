<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class VoucherPayment extends Model 
{
    use HasFactory;

    protected $fillable = [
        'voucher_number',
        'issue_date',
        'due_date',
        'created_by',
        'confirmed_by',
        'client_id',
        'payment_date',
        'amount',
        'payment_link',  // Nuevo campo para link de pago
        'payment_status',  // Nuevo campo para estado del pago
        'api_response',  // Nuevo campo para respuesta API
        'confirmation_status',
    ];

    protected static function boot()
    {
        parent::boot();
    
        static::creating(function ($model) {
            $lastVoucherNumber = static::max('voucher_number');
            $model->voucher_number = $lastVoucherNumber ? $lastVoucherNumber + 1 : 1;

            if (empty($model->issue_date)) {
                $model->issue_date = Carbon::now()->toDateString(); 
            }

            $issueDate = Carbon::parse($model->issue_date);
            $model->due_date = $issueDate->addDays(5)->format('Y-m-d');
        });
    }

    // Relaciones
    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function invoices()
    {
        return $this->belongsToMany(Invoice::class, 'voucher_payment_invoice')->withPivot('amount');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function confirmedBy()
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    // Relación con PaymentAttempt
    public function paymentAttempts()
    {
        return $this->hasMany(PaymentAttempt::class);
    }
}
