<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;

class AddLectureToWatchHistory implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public int  $lectureId
    ) {
        //
    }

    public function handle(): void
    {
        if (
            $this->user->watchedLecturesHistory()
                ->wherePivot('lecture_id', $this->lectureId)
                ->wherePivot('created_at', '>', now()->subSeconds(5))
                ->exists()
        ) {
            return;
        }

        $this->user->watchedLecturesHistory()->attach($this->lectureId);
    }
}
