<?php

namespace App\Filament\Resources\CollaboratorUserResource\Pages;

use App\Filament\Resources\CollaboratorUserResource;
use App\Notifications\CustomVerifyEmail;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;



class EditCollaboratorUser extends EditRecord
{
    protected static string $resource = CollaboratorUserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function resendVerificationEmail(): void
    {
        $user = $this->record;

        $notification = new CustomVerifyEmail(Filament::getVerifyEmailUrl($user));

        $user->notify($notification);

        Notification::make()
            ->title(__('resource.user.notifications.notification_resent.title'))
            ->success()
            ->send();
    }
}
