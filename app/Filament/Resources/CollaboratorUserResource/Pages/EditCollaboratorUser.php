<?php

namespace App\Filament\Resources\CollaboratorUserResource\Pages;

use App\Filament\Resources\CollaboratorUserResource;
use Filament\Actions;
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
}
