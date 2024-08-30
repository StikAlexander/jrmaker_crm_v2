<?php

namespace App\Filament\Resources\AdminUserResource\Pages;

use App\Filament\Resources\AdminUserResource;
use App\Models\User;
use App\Notifications\CustomVerifyEmail;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use App\Settings\MailSettings;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;

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

        $notification = new CustomVerifyEmail(Filament::getVerifyEmailUrl($user));

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