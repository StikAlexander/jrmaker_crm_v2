<?php

namespace App\Traits;

trait WithRateLimit
{
    /**
     * Apply a rate limit to the action.
     * This is a placeholder implementation to fix compatibility issues.
     *
     * @param int $maxAttempts Maximum number of attempts
     * @param int $decaySeconds Seconds until the limit resets
     * @return static
     */
    public function rateLimit(int $maxAttempts = 3, int $decaySeconds = 60): static
    {
        // This is just a stub implementation to prevent errors
        // It doesn't actually implement rate limiting
        return $this;
    }
}
