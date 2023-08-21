<?php

namespace App\Http\Cache;

class Limit extends \Illuminate\Cache\RateLimiting\Limit
{
    /**
     * Create a new rate limit using seconds as decay time.
     *
     * @param  int  $decaySeconds
     * @param  int  $maxAttempts
     * @return static
     */
    public static function perSeconds($decaySeconds, $maxAttempts)
    {
        return new static('', $maxAttempts, $decaySeconds/60.0);
    }
}
