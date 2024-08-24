<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'payment_support',
        'confirmation_status',
    ];

    protected static function boot()
    {
        parent::boot();
    
        static::creating(function ($model) {
            // Eliminar referencia a 'withTrashed' ya que no usamos SoftDeletes
            $lastVoucherNumber = static::max('voucher_number');
            $model->voucher_number = $lastVoucherNumber ? $lastVoucherNumber + 1 : 1;
        });
    }

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
}
