<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'issue_date',
        'due_date',
        'total_amount',
        'client_id',
        'created_by',
        'status',
        'pending_amount',
        'total_paid',
        'invoice_pdf',
        'description',
    ];

    protected $casts = [
        'issue_date' => 'datetime:Y-m-d',
        'due_date' => 'datetime:Y-m-d',
    ];

    protected static function boot()
    {
        parent::boot();
    
        static::creating(function ($model) {
            if (!empty($model->issue_date)) {
                $model->due_date = Carbon::parse($model->issue_date)->addDays(30);
            }
            $model->status = 'Pending'; 
        });
    
        static::saving(function ($model) {
            // Evita cambios si la factura está en estado 'Paid'
            if ($model->status === 'Paid' && $model->isDirty('status')) {
                throw new \Exception('No se puede modificar o anular una factura que ya está pagada.');
            }

            if (!empty($model->total_amount)) {
                $model->pending_amount = $model->total_amount - ($model->total_paid ?? 0);
            }

            // Si el estado es 'Cancelled', no cambiará a otro estado
            if ($model->status === 'Cancelled') {
                return;
            }

            // Cambia el estado a 'Paid' si no hay monto pendiente
            if ($model->pending_amount <= 0) {
                $model->status = 'Paid';
            } else {
                $model->status = 'Pending';
            }
        });
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
