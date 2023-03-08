<?php

namespace App\Http\Controllers\Api\Buy;

use App\Http\Controllers\Controller;
use App\Http\Requests\Buy\BuyPromoRequest;
use App\Http\Resources\SubscriptionResource;
use App\Models\Period;
use App\Models\Promo;
use App\Models\Subscription;
use App\Services\PromoService;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;

#[OA\Post(
    path: '/promopack/buy/{period}',
    description: "Покупка промо пака на период 1, 14, 30 дней",
    summary: "Покупка промо пака",
    security: [["bearerAuth" => []]],
    tags: ["promo"])
]
#[OA\Parameter(
    name: 'period',
    description: 'на какой срок хотим купить промо пак(и все его лекции соответсвенно). Есть три варианта: 1, 14, 30',
    in: 'path',
    required: true,
    schema: new OA\Schema(type: 'integer'),
    example: '30'
)]
class BuyPromoController extends Controller
{
    public function __construct(
        private PromoService $promoService
    )
    {
    }

    public function __invoke(
        BuyPromoRequest $request,
        int             $period
    )
    {
        $isPurchased = $this->promoService->isPromoPurchased();

        if ($isPurchased) {
            return response()->json([
                'message' => 'Promo pack is already purchased.'
            ], Response::HTTP_FORBIDDEN);
        }


        $paymentSuccess = true;
        if ($paymentSuccess) {
            $attributes = [
                'user_id' => auth()->user()->id,
                'subscriptionable_type' => Promo::class,
                'subscriptionable_id' => Promo::first()->id,
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
