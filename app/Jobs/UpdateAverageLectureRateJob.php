<?php

namespace App\Jobs;

use App\Models\Lector;
use App\Models\LectorAverageRate;
use App\Models\Lecture;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateAverageLectureRateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private readonly Lecture $lecture)
    {
    }

    public function handle(): void
    {
        $average = $this->lecture->rates()->average('rating');
        $this->lecture->averageRate()->updateOrCreate([
            'lecture_id' => $this->lecture->id
        ], [
            'rating' => $average
        ]);
    }
}
