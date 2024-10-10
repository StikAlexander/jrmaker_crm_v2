<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection; // Importar Collection

class InvoiceReminder extends Mailable
{
    use Queueable, SerializesModels;

    public $invoices;
    public $subject;

    /**
     * Create a new message instance.
     *
     * @param Collection $invoices
     * @param string $subject
     */
    public function __construct(Collection $invoices, string $subject)
    {
        $this->invoices = $invoices;  // Ahora aceptamos una colección de facturas
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
                        'invoices' => $this->invoices,
                    ]);
    }
}
