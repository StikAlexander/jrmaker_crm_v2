<?php

namespace App\Models;

use App\Constants\InvoiceStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;

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
    
    protected $appends = [
        'display_number',
        'formatted_total_amount',
        'formatted_total_paid',
        'formatted_pending_amount',
    ];

    protected static function boot()
    {
        parent::boot();
    
        static::creating(function ($model) {
            // Establecer fecha de vencimiento a 30 días después de la emisión
            if (!empty($model->issue_date)) {
                $model->due_date = Carbon::parse($model->issue_date)->addDays(30);
            }
            
            // Estado inicial: pendiente
            $model->status = InvoiceStatus::PENDING;
        });
    
        static::saving(function ($model) {
            // Evita cambios si la factura está en estado pagado
            if ($model->getOriginal('status') === InvoiceStatus::PAID && $model->isDirty('status')) {
                throw new \Exception('No se puede cambiar el estado de una factura que ya está pagada.');
            }
            
            // Recalcular el monto pendiente
            if (!empty($model->total_amount)) {
                $model->pending_amount = $model->total_amount - ($model->total_paid ?? 0);
                
                // Si no está cancelada, actualizar el estado basado en los montos
                if ($model->status !== InvoiceStatus::CANCELLED) {
                    $model->status = InvoiceStatus::determineStatus(
                        $model->total_amount, 
                        $model->total_paid
                    );
                }
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
        return $this->belongsToMany(Payment::class, 'payment_invoice')->withPivot('amount');
    }
    
    // Accessors y Mutators
    protected function displayNumber(): Attribute
    {
        return Attribute::make(
            get: fn () => InvoiceStatus::INVOICE_PREFIX . $this->invoice_number,
        );
    }
    
    protected function formattedTotalAmount(): Attribute
    {
        return Attribute::make(
            get: fn () => InvoiceStatus::formatCurrency($this->total_amount),
        );
    }
    
    protected function formattedTotalPaid(): Attribute
    {
        return Attribute::make(
            get: fn () => InvoiceStatus::formatCurrency($this->total_paid),
        );
    }
    
    protected function formattedPendingAmount(): Attribute
    {
        return Attribute::make(
            get: fn () => InvoiceStatus::formatCurrency($this->pending_amount),
        );
    }
    
    // Métodos de utilidad
    public function isPaid(): bool
    {
        return $this->status === InvoiceStatus::PAID;
    }
    
    public function isPending(): bool
    {
        return $this->status === InvoiceStatus::PENDING;
    }
    
    public function isCancelled(): bool
    {
        return $this->status === InvoiceStatus::CANCELLED;
    }
    
    public function canBeCancelled(): bool
    {
        return InvoiceStatus::canBeCancelled($this->status);
    }
    
    public function isOverdue(): bool
    {
        return $this->isPending() && Carbon::now()->isAfter($this->due_date);
    }
    
    public function getDaysUntilDue(): int
    {
        if ($this->isPaid() || $this->isCancelled()) {
            return 0;
        }
        
        return max(0, Carbon::now()->diffInDays($this->due_date, false));
    }
    
    public function getDaysOverdue(): int
    {
        if (!$this->isOverdue()) {
            return 0;
        }
        
        return Carbon::now()->diffInDays($this->due_date);
    }
}
