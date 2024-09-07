<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'voucher_payment_id',
        'status',  // Estado del intento de pago (Pending, Completed, Failed)
        'response',  // Respuesta de la API de pagos
    ];

    // Relación con VoucherPayment
    public function voucherPayment()
    {
        return $this->belongsTo(VoucherPayment::class);
    }
}
