<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue as ShouldQueueAlias;
use Illuminate\Support\Facades\Log;

class ShouldQueue implements ShouldQueueAlias
{
    public function failed($exception = null)
    {
        Log::error('---');
        Log::error(__CLASS__.' failed');
        Log::error('---');
    }
}
