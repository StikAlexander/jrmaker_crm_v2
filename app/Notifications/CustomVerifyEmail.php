<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class CustomVerifyEmail extends BaseVerifyEmail
{
    public string $url;

    public function __construct(string $url)
    {
        $this->url = $url;
    }

    protected function buildMailMessage($url)
    {
        return (new MailMessage)
            ->subject(__('Verifica tu dirección de correo electrónico'))
            ->line(__('Haz clic en el botón de abajo para verificar tu dirección de correo electrónico.'))
            ->action(__('Verificar dirección de correo electrónico'), $this->url)
            ->line(__('Si no creaste una cuenta, no se requiere ninguna acción adicional.'));
    }
}

