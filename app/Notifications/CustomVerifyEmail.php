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
            ->subject(__('Verify Your Email Address'))
            ->line(__('Click the button below to verify your email address.'))
            ->action(__('Verify Email Address'), $this->url)
            ->line(__('If you did not create an account, no further action is required.'));
    }
}
