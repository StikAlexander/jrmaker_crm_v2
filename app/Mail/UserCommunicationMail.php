<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;  
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UserCommunicationMail extends Mailable implements ShouldQueue  
{
    use Queueable, SerializesModels;

    public $cliente;
    public $plantilla;

    /**
     * Create a new message instance.
     *
     * @param $cliente
     * @param $plantilla
     */
    public function __construct($cliente, $plantilla)
    {
        $this->cliente = $cliente;
        $this->plantilla = $plantilla;
    }

    /**
     * Get the message envelope.
     *
     * @return Envelope
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'User Communication: ' . $this->getSubject(),
        );
    }

    /**
     * Get the message content definition.
     *
     * @return Content
     */
    public function content(): Content
    {
        return new Content(
            view: $this->getView(),
            with: ['cliente' => $this->cliente],
        );
    }

    /**
     * Determine which view to use based on the selected template.
     *
     * @return string
     */
    protected function getView(): string
    {
        switch ($this->plantilla) {
            case 'solicitud_certificados':
                return 'emails.solicitud_certificados';  // Vista Blade para solicitud de certificados
            case 'publicidad':
                return 'emails.publicidad';  // Vista Blade para correo publicitario
            default:
                return 'emails.default';  // Vista predeterminada si no se selecciona plantilla
        }
    }

    /**
     * Get the subject based on the template.
     *
     * @return string
     */
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

    /**
     * Attachments for the email.
     *
     * @return array
     */
    public function attachments(): array
    {
        return [];
    }
}
