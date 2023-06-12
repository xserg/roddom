<?php

namespace App\Jobs;

use App\Models\Lector;
use App\Models\LectorAverageRate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateAverageLectorRateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private readonly Lector $lector)
    {
    }

    public function handle(): void
    {
        $average = $this->lector->rates()->average('rating');
        $this->lector->averageRate()->updateOrCreate([
            'lector_id' => $this->lector->id
        ], [
            'rating' => $average
        ]);
    }
}
