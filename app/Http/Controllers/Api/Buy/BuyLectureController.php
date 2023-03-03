<?php

namespace App\Http\Controllers\Api\Buy;

use App\Http\Controllers\Controller;
use App\Http\Requests\Buy\BuyLectureRequest;
use App\Http\Resources\SubscriptionResource;
use App\Models\Lecture;
use App\Models\Period;
use App\Models\Subscription;
use App\Repositories\LectureRepository;
use App\Services\LectureService;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;

#[OA\Post(
    path: '/lecture/{id}/buy/{period}',
    description: "Покупка отдельной лекции на период 1, 14, 30 дней",
    summary: "Покупка отдельной лекции",
    security: [["bearerAuth" => []]],
    tags: ["lecture"])
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
        int               $lectureId,
        int               $period
    )
    {
        $lecture = $this->lectureRepository->getLectureById($lectureId);
        $isPurchasedStrict = $this->lectureService->isLecturePurchased($lectureId);

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
            'subscription' => new SubscriptionResource($subscription)
        ]);
    }
}
