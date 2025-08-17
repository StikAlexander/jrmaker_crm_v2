<?php

namespace App\Constants;

class InvoiceStatus
{
    public const PENDING = 'Pending';
    public const PAID = 'Paid';
    public const CANCELLED = 'Cancelled';
    
    public const INVOICE_PREFIX = 'FEVD';
    
    /**
     * Opciones de estado para los selectores
     */
    public static function getStatusOptions(): array
    {
        return [
            self::PENDING => 'Pendiente',
            self::PAID => 'Pagada',
            self::CANCELLED => 'Cancelada',
        ];
    }
    
    /**
     * Color para cada estado (para badges/indicadores)
     */
    public static function getStatusColor(string $status): string
    {
        return match ($status) {
            self::PENDING => 'warning',
            self::PAID => 'success',
            self::CANCELLED => 'danger',
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
     * Determina el estado de una factura basado en sus montos
     */
    public static function determineStatus(float $totalAmount, float $totalPaid, bool $isCancelled = false): string
    {
        if ($isCancelled) {
            return self::CANCELLED;
        }
        
        $pendingAmount = $totalAmount - $totalPaid;
        return ($pendingAmount <= 0) ? self::PAID : self::PENDING;
    }
    
    /**
     * Valida si un estado es válido
     */
    public static function isValidStatus(string $status): bool
    {
        return in_array($status, [self::PENDING, self::PAID, self::CANCELLED]);
    }
    
    /**
     * Determina si una factura puede ser cancelada
     */
    public static function canBeCancelled(string $status): bool
    {
        return $status === self::PENDING;
    }
}
