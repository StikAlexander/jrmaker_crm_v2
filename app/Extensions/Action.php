<?php

namespace App\Extensions;

use App\Traits\WithRateLimit;
use Filament\Actions\Action as FilamentAction;

class Action extends FilamentAction
{
    use WithRateLimit;
}
