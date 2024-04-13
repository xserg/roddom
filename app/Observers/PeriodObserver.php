<?php
declare(strict_types=1);

namespace App\Observers;

use App\Models\Period;
use Illuminate\Support\Facades\Cache;

class PeriodObserver
{
    public function saved(Period $period): void
    {
        $this->forget();
    }

    public function deleted(Period $period): void
    {
        $this->forget();
    }

    public function restored(Period $period): void
    {
        $this->forget();
    }

    public function forceDeleted(Period $period): void
    {
        $this->forget();
    }

    private function forget(): void
    {
        Cache::forget('periods');
    }
}
