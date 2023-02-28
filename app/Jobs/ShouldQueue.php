<?php

namespace App\Jobs;

use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Queue\ShouldQueue as ShouldQueueAlias;

class ShouldQueue implements ShouldQueueAlias
{
    public function failed($exception = null)
    {
        Log::error('---');
        Log::error(__CLASS__ . ' failed');
        Log::error('---');
    }
}
