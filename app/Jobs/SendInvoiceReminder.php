<?php

namespace App\Jobs;

use App\Models\Invoice;
use App\Mail\InvoiceReminder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendInvoiceReminder implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $invoice;

    public function __construct(Invoice $invoice)
    {
        $this->invoice = $invoice;
    }

    public function handle()
    {
        Log::info('Procesando la factura: ' . $this->invoice->invoice_number);
        
        if ($this->invoice->client && $this->invoice->client->email) {
            Log::info('Enviando correo a: ' . $this->invoice->client->email);
            Mail::to($this->invoice->client->email)
                ->send(new InvoiceReminder($this->invoice, 'Recordatorio de factura'));
        } else {
            Log::warning('Cliente no tiene email válido.');
        }
    }
    
}
