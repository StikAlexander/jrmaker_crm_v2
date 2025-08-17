<?php

namespace App\Constants;

class PaymentStatus
{
    public const PENDING = 'Pending';
    public const COMPLETED = 'Completed';
    public const FAILED = 'Failed';
    public const DECLINED = 'Declined';
    public const ERROR = 'Error';
    public const CANCELLED = 'Cancelled';
    public const VOIDED = 'Voided';
    
    /**
     * Opciones de estado para los selectores
     */
    public static function getStatusOptions(): array
    {
        return [
            self::PENDING => 'Pendiente',
            self::COMPLETED => 'Completado',
            self::FAILED => 'Fallido',
            self::DECLINED => 'Rechazado',
            self::ERROR => 'Error',
            self::CANCELLED => 'Cancelado',
            self::VOIDED => 'Anulado',
        ];
    }
    
    /**
     * Color para cada estado (para badges/indicadores)
     */
    public static function getStatusColor(string $status): string
    {
        return match ($status) {
            self::PENDING => 'warning',
            self::COMPLETED => 'success',
            self::FAILED => 'danger',
            self::DECLINED => 'danger',
            self::ERROR => 'danger',
            self::CANCELLED => 'gray',
            self::VOIDED => 'gray',
            default => 'secondary',
        };
    }
    
    /**
     * Texto localizado para cada estado
     */
    public static function getStatusLabel(string $status): string
    {
        return self::getStatusOptions()[$status] ?? $status;
    }
    
    /**
     * Formatea un valor numérico como moneda
     */
    public static function formatCurrency($amount): string
    {
        return '$' . number_format($amount, 0, ',', '.');
    }
    
    /**
     * Valida si un estado es válido
     */
    public static function isValidStatus(string $status): bool
    {
        return in_array($status, [
            self::PENDING, 
            self::COMPLETED, 
            self::FAILED,
            self::DECLINED,
            self::ERROR,
            self::CANCELLED,
            self::VOIDED
        ]);
    }
    
    /**
     * Determina si un pago está finalizado (exitoso o fallido)
     */
    public static function isFinalized(string $status): bool
    {
        return in_array($status, [
            self::COMPLETED, 
            self::FAILED,
            self::DECLINED,
            self::ERROR,
            self::CANCELLED,
            self::VOIDED
        ]);
    }
    
    /**
     * Determina si un pago puede ser procesado
     */
    public static function canBeProcessed(string $status): bool
    {
        return $status === self::PENDING;
    }
}
