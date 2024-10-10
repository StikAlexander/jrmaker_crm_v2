<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Invoice;

class InvoiceReminder extends Mailable
{
    use Queueable, SerializesModels;

    public $invoice;
    public $subject;

    /**
     * Create a new message instance.
     *
     * @param Invoice $invoice
     * @param string $subject
     */
    public function __construct(Invoice $invoice, string $subject)
    {
        $this->invoice = $invoice;  // Aquí aceptamos una instancia de `Invoice`
        $this->subject = $subject;

        $this->afterCommit(); // Asegurarse de que se envíe después de la transacción de base de datos
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.invoice-reminder')
                    ->subject($this->subject)
                    ->with([
                        'invoice' => $this->invoice,
                    ]);
    }
}
