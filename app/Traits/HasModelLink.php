<?php

namespace App\Traits;

use Filament\Facades\Filament;

trait HasModelLink
{
    /**
     * Generates a URL to the related model's resource.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $record
     * @param  string  $columnName
     * @param  string  $viewType
     * @return string|null
     */
    protected function generateModelLink($record, $columnName, $viewType = 'view')
    {
        $relationship = \Illuminate\Support\Str::before($columnName, '.');
        $relatedRecord = $record->{$relationship};

        if ($relatedRecord === null) {
            return null;
        }

        $selectedResource = collect(Filament::getResources())
            ->first(function ($resource) use ($relatedRecord) {
                $modelClass = $resource::getModel();
                return $relatedRecord instanceof $modelClass;
            });

        if ($selectedResource === null || !method_exists($selectedResource, 'getUrl')) {
            return null;
        }

        $availablePages = $selectedResource::getPages();
        if (!array_key_exists($viewType, $availablePages)) {
            return null;
        }

        return $selectedResource::getUrl($viewType, ['record' => $relatedRecord->getKey()]);
    }
}
