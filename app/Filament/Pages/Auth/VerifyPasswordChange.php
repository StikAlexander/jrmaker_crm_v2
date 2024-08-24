<?php

namespace App\Filament\Pages\Auth;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VerifyPasswordChange extends Notification
{
    use Queueable;

    protected string $code; // Cambiar de 'token' a 'code'

    public function __construct(string $code)
    {
        $this->code = $code; // Asignar el código de verificación
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Código de Verificación para Cambio de Contraseña')
            ->line('Has solicitado un cambio de contraseña. Aquí tienes tu código de verificación:')
            ->line($this->code) // Mostrar el código directamente en el correo
            ->line('Si no solicitaste este cambio, por favor ignora este correo.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'code' => $this->code,
        ];
    }
}
