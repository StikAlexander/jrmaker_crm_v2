<?php

namespace App\Filament\Resources\CollaboratorUserResource\Pages;

use App\Filament\Resources\CollaboratorUserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Settings\MailSettings;
use Exception;
use Filament\Facades\Filament;
use Filament\Notifications\Auth\VerifyEmail;
use Filament\Notifications\Notification;

class EditCollaboratorUser extends EditRecord
{
    protected static string $resource = CollaboratorUserResource::class;

    protected function resendVerificationEmail(): void
    {
        $user = $this->record;
        $settings = app(MailSettings::class);

        if (! method_exists($user, 'notify')) {
            $userClass = $user::class;
            throw new Exception("Model [{$userClass}] does not have a [notify()] method.");
        }

        $notification = new VerifyEmail();
        $notification->url = Filament::getVerifyEmailUrl($user);

        $settings->loadMailSettingsToConfig();
        $user->notify($notification);

        Notification::make()
            ->title(__('resource.user.notifications.notification_resent.title'))
            ->success()
            ->send();
    }
}
