<?php

namespace App\Filament\Resources\UserResource\Widgets;

use App\Models\Lecture;
use App\Models\User;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\DB;

class LectureViews extends Widget
{
    public ?User $record = null;

    protected static string $view = 'filament.resources.user-resource.widgets.lecture-views';

    protected function getViewData(): array
    {
        $lecturesViewedTodayCount = $this->record->watchedLecturesToday->count();
        $lecturesViewedCount = $this->record->watchedLectures->count();
        $totalViewsCount = $this->record->watchedLecturesHistory->count();
        $totalViewsTodayCount = $this->record->watchedLecturesHistoryToday->count();

        $mostViewedLectureRaw = $this->record->watchedLectures()
            ->select(DB::raw('lecture_id, user_id, COUNT(*) as count'))
            ->groupBy(['lecture_id', 'user_id'])
            ->orderBy('count', 'desc')
            ->first();
        $mostViewedLecture = Lecture::find($mostViewedLectureRaw?->lecture_id);

        return [
            'lecturesViewedCount' => $lecturesViewedCount,
            'lecturesViewedTodayCount' => $lecturesViewedTodayCount,
            'totalViewsCount' => $totalViewsCount,
            'totalViewsTodayCount' => $totalViewsTodayCount,
            'mostViewed' => $mostViewedLecture,
            'mostViewedCount' => $mostViewedLectureRaw?->count
        ];
    }
}
