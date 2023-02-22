<?php

namespace App\Jobs;

use App\Models\Lecture;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class WatchLecture implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private int $lectureId,
        private User $currentUser
    )
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(UserService $userService): void
    {
        $userService->addLectureToWatched($this->lectureId, $this->currentUser);
    }
}
