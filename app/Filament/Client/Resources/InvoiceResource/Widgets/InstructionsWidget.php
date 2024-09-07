<?php

namespace App\Filament\Client\Resources\InvoiceResource\Widgets;

use Filament\Widgets\Widget;

class InstructionsWidget extends Widget
{
    // Define la vista sin sobrescribir el método render()
    protected static string $view = 'filament.client.resources.invoice-resource.widgets.instructions-widget';
}
