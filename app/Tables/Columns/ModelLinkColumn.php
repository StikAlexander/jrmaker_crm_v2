<?php

namespace App\Tables\Columns;

use Filament\Facades\Filament;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Str;

class ModelLinkColumn extends TextColumn
{
    protected string $viewType = 'view';
    protected ?string $overwriteName = null;

    public function overwriteName($name): static
    {
        $this->overwriteName = $name;
        $this->setUpLink();
        return $this;
    }

    public function setViewType(string $viewType): static
    {
        $this->viewType = $viewType;
        $this->setUpLink();
        return $this;
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpLink();
    }

    protected function setUpLink(): void
    {
        $this->url(function ($record) {
            if ($record === null) {
                return null;
            }

            $name = $this->overwriteName ?? $this->getName();
            $relationship = Str::before($name, '.');
            $relatedRecord = $record->{$relationship};

            if ($relatedRecord === null) {
                return null;
            }

            // Encuentra el recurso correspondiente al modelo relacionado
            $selectedResource = collect(Filament::getResources())
                ->first(function ($resource) use ($relatedRecord) {
                    $modelClass = $resource::getModel();
                    return $relatedRecord instanceof $modelClass;
                });

            if ($selectedResource === null || !method_exists($selectedResource, 'getUrl')) {
                return null;
            }

            $availablePages = $selectedResource::getPages();
            if (!array_key_exists($this->viewType, $availablePages)) {
                return null;
            }

            return $selectedResource::getUrl($this->viewType, [
                'record' => $relatedRecord->getKey(),
            ]);
        });
    }
}
