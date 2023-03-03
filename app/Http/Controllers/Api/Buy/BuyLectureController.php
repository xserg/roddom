<?php

namespace App\Http\Controllers\Api\Buy;

use App\Http\Controllers\Controller;
use App\Http\Requests\Buy\BuyLectureRequest;
use App\Models\Lecture;
use App\Models\Period;
use App\Models\Subscription;
use App\Repositories\LectureRepository;
use App\Services\LectureService;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/lecture/{id}/buy/{period}',
    description: "Покупка отдельной лекции на период 1, 14, 30 дней",
    summary: "Покупка отдельной лекции",
    security: [["bearerAuth" => []]],
    tags: ["buy"])
]
class BuyLectureController extends Controller
{
    public function __construct(
        private LectureRepository $lectureRepository,
        private LectureService    $lectureService
    )
    {
    }

    public function __invoke(
        BuyLectureRequest $request,
        int               $id,
        int               $period
    )
    {
        $lecture = $this->lectureRepository->getLectureById($id);
        $isPurchasedStrict = $this->lectureService->isLecturePurchased($lecture->id);

        if ($isPurchasedStrict) {
            return response()->json([
                'message' => 'Lecture with id ' . $request->id . ' is already purchased.'
            ], Response::HTTP_FORBIDDEN);
        }

        $isFree = $this->lectureService->isFree($lecture);

        if ($isFree) {
            return response()->json([
                'message' => 'You cannot purchase free lecture'
            ], Response::HTTP_FORBIDDEN);
        }

        $lectureId = $id;

        $attributes = [
            'user_id' => auth()->user()->id,
            'subscriptionable_type' => Lecture::class,
            'subscriptionable_id' => $lectureId,
            'period_id' => Period::firstWhere('length', '=', $period)->id,
            'start_date' => now(),
            'end_date' => now()->addDays($period)
        ];

        $subscription = new Subscription($attributes);
        $subscription->save();

        return response()->json([
            'req' => $request->id
        ]);
    }
}
