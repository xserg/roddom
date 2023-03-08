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
#[OA\Parameter(
    name: 'id',
    description: 'id лекции, которую хотим купить',
    in: 'path',
    required: true,
    schema: new OA\Schema(type: 'integer'),
    example: '137'
)]
#[OA\Parameter(
    name: 'period',
    description: 'на какой срок хотим купить лекцию. Есть три варианта: 1, 14, 30',
    in: 'path',
    required: true,
    schema: new OA\Schema(type: 'integer'),
    example: '14'
)]
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
        $isPurchasedStrict = $this->lectureService->isLectureStrictPurchased($lectureId, auth()->user());

        if ($isPurchasedStrict) {
            return response()->json([
                'message' => 'Lecture with id ' . $request->id . ' is already purchased.'
            ], Response::HTTP_FORBIDDEN);
        }

        $isFree = $this->lectureService->isFree($lectureId);

        if ($isFree) {
            return response()->json([
                'message' => 'You cannot purchase free lecture'
            ], Response::HTTP_FORBIDDEN);
        }

        /**
         * тут старт оплаты
         */

        $paymentSuccess = true;
        if($paymentSuccess){
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
                'message' => 'Подписка на лекцию успешно оформлена',
                'subscription' => new SubscriptionResource($subscription)
            ]);
        } else {
            return response()->json([
                'message' => 'Подписка не была оформлена. ',
            ]);
        }
    }
}
