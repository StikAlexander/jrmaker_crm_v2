<?php

namespace App\Services;

use App\Constants\InvoiceStatus;
use App\Models\Invoice;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class InvoiceService
{
    public function isInvoiceNumberUnique(string $invoiceNumber, ?int $currentId = null): bool
    {
        return !Invoice::query()->where('invoice_number', $invoiceNumber)
            ->when($currentId, fn ($query) => $query->where('id', '!=', $currentId))
            ->exists();
    }
    
    public function calculateDueDate(\Carbon\Carbon $issueDate): \Carbon\Carbon
    {
        return $issueDate->copy()->addDays(30);
    }
    
    public function getInvoiceDisplayNumber(string $invoiceNumber): string
    {
        return InvoiceStatus::INVOICE_PREFIX . $invoiceNumber;
    }
    
    public function getInvoiceStatus(float $totalAmount, float $totalPaid): string
    {
        $pendingAmount = $totalAmount - $totalPaid;
        
        return $pendingAmount <= 0 ? InvoiceStatus::PAID : InvoiceStatus::PENDING;
    }
    
    public function getCachedClientsList()
    {
        return Cache::remember('clients_list', \Illuminate\Support\Carbon::now()->addHour(), function () {
            return User::query()->whereHas('roles', function ($query) {
                $query->where('name', 'client');
            })->pluck('name', 'id');
        });
    }
}
