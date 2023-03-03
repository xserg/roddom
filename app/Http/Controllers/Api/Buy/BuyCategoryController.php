<?php

namespace App\Http\Controllers\Api\Buy;

use App\Http\Controllers\Controller;
use App\Http\Requests\Buy\BuyCategoryRequest;
use App\Http\Resources\SubscriptionResource;
use App\Models\Category;
use App\Models\Period;
use App\Models\Subscription;
use App\Services\CategoryService;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;

#[OA\Post(
    path: '/category/{id}/buy/{period}',
    description: "Покупка подкатегории лекций(входящие все в нее лекции) на период 1, 14, 30 дней",
    summary: "Покупка подкатегории",
    security: [["bearerAuth" => []]],
    tags: ["category"])
]
#[OA\Parameter(
    name: 'id',
    description: 'id подкатегории, которую хотим купить',
    in: 'path',
    required: true,
    schema: new OA\Schema(type: 'integer'),
    example: '12'
)]
#[OA\Parameter(
    name: 'period',
    description: 'на какой срок хотим купить категорию(и все её лекции соответсвенно). Есть три варианта: 1, 14, 30',
    in: 'path',
    required: true,
    schema: new OA\Schema(type: 'integer'),
    example: '14'
)]
class BuyCategoryController extends Controller
{
    public function __construct(
        private CategoryService $categoryService
    )
    {
    }

    public function __invoke(
        BuyCategoryRequest $request,
        int               $categoryId,
        int               $period
    )
    {
        $isPurchased = $this->categoryService->isCategoryPurchased($categoryId);

        if ($isPurchased) {
            return response()->json([
                'message' => 'Category with id ' . $categoryId . ' is already purchased.'
            ], Response::HTTP_FORBIDDEN);
        }

        $attributes = [
            'user_id' => auth()->user()->id,
            'subscriptionable_type' => Category::class,
            'subscriptionable_id' => $categoryId,
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
