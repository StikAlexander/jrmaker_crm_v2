<?php

namespace App\Filament\Resources\CollaboratorUserResource\Pages;

use App\Filament\Resources\CollaboratorUserResource;
use App\Models\User;
use Filament\Forms\Components\Placeholder;
use Filament\Resources\Pages\ViewRecord;



class ViewCollaboratorUser extends ViewRecord
{
    protected static string $resource = CollaboratorUserResource::class;

    protected function getFormSchema(): array
    {
        return [
            Placeholder::make('created_at')
                ->label('Fecha de creación')
                ->content(fn (?User $record): ?string => $record?->created_at?->diffForHumans())
                ->columnSpan(1),

            Placeholder::make('updated_at')
                ->label('Fecha de actualización')
                ->content(fn (?User $record): ?string => $record?->updated_at?->diffForHumans())
                ->columnSpan(1),

            Placeholder::make('created_by')
                ->label('Creado por')
                ->content(fn (?User $record): string => $record ? ($record->createdBy?->name ?? '-') : '-')
                ->columnSpan(2),
        ];
    }
}
