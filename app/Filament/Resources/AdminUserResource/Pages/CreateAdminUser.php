<?php

namespace App\Filament\Resources\AdminUserResource\Pages;

use App\Filament\Resources\AdminUserResource;
use App\Settings\MailSettings;
use Exception;
use Filament\Facades\Filament;
use Filament\Notifications\Auth\VerifyEmail;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class CreateAdminUser extends CreateRecord
{
    protected static string $resource = AdminUserResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterCreate(): void
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

    protected function handleRecordCreation(array $data): Model
    {
        $data['created_by_id'] = auth()->id();
        $user = User::create($data);
        $user->assignRole(['panel_user', 'admin']);

        return $user;
    }
}