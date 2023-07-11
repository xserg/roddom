<?php

namespace App\Filament\Resources\UserResource\Widgets;

use App\Models\Category;
use App\Models\Lecture;
use App\Models\User;
use App\Services\CategoryService;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\DB;

class LectureViews extends Widget
{
    public ?User $record = null;

    protected static string $view = 'filament.resources.user-resource.widgets.lecture-views';

    protected function getViewData(): array
    {
        $tableName = $this->record->watchedLecturesHistory()->getTable();
        $lecturesViewedTodayCount = $this->record->watchedLecturesHistory()
            ->whereDate($tableName . '.created_at', today())
            ->distinct('lecture_id')
            ->count();
        $lecturesViewedCount = $this->record->watchedLecturesHistory()
            ->distinct('lecture_id')
            ->count();

        $totalViewsCount = $this->record->watchedLecturesHistory()
            ->count();
        $totalViewsTodayCount = $this->record->watchedLecturesHistory()
            ->whereDate($tableName . '.created_at', today())
            ->count();
        $mostViewedLectureRaw = $this->record->watchedLectures()
            ->groupBy(['lecture_id', 'user_id'])
            ->select(DB::raw('lecture_id, user_id, COUNT(*) as count'))
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
