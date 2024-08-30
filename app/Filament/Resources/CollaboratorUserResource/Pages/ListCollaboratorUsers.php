<?php

namespace App\Filament\Resources\CollaboratorUserResource\Pages;

use App\Filament\Resources\CollaboratorUserResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCollaboratorUsers extends ListRecords
{
    protected static string $resource = CollaboratorUserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
