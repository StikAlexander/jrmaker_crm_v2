<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UserCommunicationMail extends Mailable
{
    use SerializesModels;  // Eliminamos Queueable

    public $cliente;
    public $plantilla;

    public function __construct($cliente, $plantilla)
    {
        $this->cliente = $cliente;
        $this->plantilla = $plantilla;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->getSubject(),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: $this->getView(),  
            with: ['cliente' => $this->cliente]  
        );
    }

    protected function getView(): string
    {
        switch ($this->plantilla) {
            case 'solicitud_certificados':
                return 'emails.solicitud_certificados';  
            case 'publicidad':
                return 'emails.publicidad';
            default:
                return 'emails.default';
        }
    }

    protected function getSubject(): string
    {
        switch ($this->plantilla) {
            case 'solicitud_certificados':
                return 'Solicitud de Certificados de Retención';
            case 'publicidad':
                return 'Promociones Especiales de Fin de Año';
            default:
                return 'Comunicación General';
        }
    }

    public function attachments(): array
    {
        return [];
    }
}
