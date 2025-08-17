<?php

namespace App\Constants;

class InvoiceStatus
{
    public const PENDING = 'Pending';
    public const PAID = 'Paid';
    public const CANCELLED = 'Cancelled';
    
    public const INVOICE_PREFIX = 'FEVD';
    
    public static function getStatusOptions(): array
    {
        return [
            self::PENDING => 'Pendiente',
            self::PAID => 'Pagada',
            self::CANCELLED => 'Cancelada',
        ];
    }
    
    public static function getStatusColor(string $status): string
    {
        return match ($status) {
            self::PENDING => 'warning',
            self::PAID => 'success',
            self::CANCELLED => 'danger',
            default => 'secondary',
        };
    }
    
    public static function getStatusLabel(string $status): string
    {
        return self::getStatusOptions()[$status] ?? $status;
    }
    
    public static function formatCurrency($amount): string
    {
        return '$' . number_format($amount, 0, ',', '.');
    }
}
