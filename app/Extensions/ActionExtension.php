<?php

namespace App\Extensions;

use Filament\Actions\Action as BaseAction;

class ActionExtension extends BaseAction
{
    public function rateLimit(int $maxAttempts = 3, int $decaySeconds = 60): static
    {
        return $this;
    }
}
