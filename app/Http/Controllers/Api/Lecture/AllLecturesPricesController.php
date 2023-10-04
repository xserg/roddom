<?php

namespace App\Http\Controllers\Api\Lecture;

use App\Services\LectureService;
use Illuminate\Http\Response;

class AllLecturesPricesController
{
    public function __construct(
        private LectureService $lectureService
    )
    {
    }

    public function __invoke()
    {
        return response()->json(
            $this->lectureService->getEverythingPackPricesResource(),
            Response::HTTP_OK
        );
    }
}
