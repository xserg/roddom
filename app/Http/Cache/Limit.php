<?php

namespace App\Http\Cache;

class Limit extends \Illuminate\Cache\RateLimiting\Limit
{
    /**
     * Create a new limit instance.
     *
     * @param  mixed|string  $key
     * @param  int  $maxAttempts
     * @param  int|float  $decayMinutes
     * @return void
     */
    public function __construct($key = '', int $maxAttempts = 60, $decayMinutes = 1)
    {
        $this->key = $key;
        $this->maxAttempts = $maxAttempts;
        $this->decayMinutes = $decayMinutes;
    }
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
