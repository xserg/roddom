<?php

namespace App\Jobs;

use App\Models\User;
use DateTimeInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class AddLectureToWatchHistory implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public User              $user,
        public int               $lectureId,
        public DateTimeInterface $dateTime
    ) {
        //
    }

    public function handle(): void
    {
        // защита от мультикликов (вообще-то надо на фронте эту проблему тротлингом решить)

        // если за 5 секунд до момента просмотра лекции уже
        // есть запись о просмотре, тогда игнорим просмотр
        if ($this->userHasWatchedLectureWithinTimeFrame(5)) {
            return;
        }

        // добавляем с датойвремя просмотра
        $this->user->watchedLecturesHistory()->attach($this->lectureId, ['created_at' => $this->dateTime]);
    }

    private function userHasWatchedLectureWithinTimeFrame(int $seconds): bool
    {
        return $this->user->watchedLecturesHistory()
            ->wherePivot('lecture_id', $this->lectureId)
            ->wherePivot('created_at', '>=', $this->getSubtractDateTime($seconds))
            ->wherePivot('created_at', '<=', $this->dateTime)
            ->exists();
    }

    private function getSubtractDateTime(int $seconds): Carbon
    {
        return $this->dateTime->copy()->subSeconds($seconds);
    }
}
