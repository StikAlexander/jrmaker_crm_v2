<?php

namespace App\Filament\Resources\AdminUserResource\Pages;

use App\Filament\Resources\AdminUserResource;
use Filament\Actions;
use Exception;
use App\Settings\MailSettings;
use Filament\Resources\Pages\EditRecord;
use Filament\Facades\Filament;
use Filament\Notifications\Auth\VerifyEmail;
use Filament\Notifications\Notification;

class EditAdminUser extends EditRecord
{
    protected static string $resource = AdminUserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

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
