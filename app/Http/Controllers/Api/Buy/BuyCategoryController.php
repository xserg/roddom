<?php

namespace App\Http\Controllers\Api\Buy;

use App\Http\Controllers\Controller;
use App\Http\Requests\Buy\BuyCategoryRequest;
use App\Models\Category;
use App\Models\Order;
use App\Models\Period;
use App\Repositories\CategoryRepository;
use App\Repositories\PeriodRepository;
use App\Services\CategoryService;
use App\Services\PaymentService;
use App\Traits\MoneyConversion;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;

#[OA\Post(
    path: '/category/{id}/buy/{period}',
    description: 'Покупка подкатегории лекций(входящие все в нее лекции) на период 1, 14, 30 дней',
    summary: 'Покупка подкатегории',
    security: [['bearerAuth' => []]],
    tags: ['category'])
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
#[OA\Response(
    response: Response::HTTP_OK,
    description: 'OK',
    content: new OA\JsonContent(
        example: [
            'link' => 'https://yoomoney.ru/checkout/payments/',
        ]
    )
)]
class BuyCategoryController extends Controller
{
    use MoneyConversion;

    public function __construct(
        private CategoryService    $categoryService,
        private CategoryRepository $categoryRepository,
        private PaymentService     $paymentService,
        private PeriodRepository   $periodRepository
    ) {
    }

    public function __invoke(
        BuyCategoryRequest $request,
        int                $categoryId,
        int                $period
    ) {
        $periodId = $this->periodRepository->getPeriodByLength($period)->id;
        $isPurchased = $this->categoryService->isCategoryPurchased($categoryId);
        $relations = [
            'childrenCategories.categoryPrices.period',
            'childrenCategories.parentCategory',
            'childrenCategories.categoryPrices',
            'childrenCategories.lectures.category.categoryPrices',
            'childrenCategories.lectures.pricesInPromoPacks',
            'childrenCategories.lectures.pricesForLectures',
            'childrenCategories.lectures.pricesPeriodsInPromoPacks',
            'childrenCategories.lectures.paymentType',
            'childrenCategories.lectures.contentType',
        ];
        $category = $this->categoryRepository->getCategoryById($categoryId, $relations);

        if ($category->isSub()) {
            $price = self::coinsToRoubles($this->categoryService->calculateSubCategoryPriceForPeriod($category, $periodId));
        } else {
            $price = self::coinsToRoubles($this->categoryService->calculateMainCategoryPriceForPeriod($category, $periodId));
        }

        if ($isPurchased) {
            return response()->json([
                'message' => 'Category with id ' . $categoryId . ' is already purchased.',
            ], Response::HTTP_FORBIDDEN);
        }

        $order = Order::create([
            'user_id' => auth()->id(),
            'price' => $price,
            'subscriptionable_type' => Category::class,
            'subscriptionable_id' => $categoryId,
            'period' => $period,
        ]);

        if ($order) {
            $link = $this->paymentService->createPayment(
                $price,
                ['order_id' => $order->id]
            );

            return response()->json([
                'link' => $link,
            ], Response::HTTP_OK);
        }

        //        $paymentSuccess = true;
        //
        //        if($paymentSuccess){
        //            $attributes = [
        //                'user_id' => auth()->user()->id,
        //                'subscriptionable_type' => Category::class,
        //                'subscriptionable_id' => $categoryId,
        //                'period_id' => Period::firstWhere('length', '=', $period)->id,
        //                'start_date' => now(),
        //                'end_date' => now()->addDays($period)
        //            ];
        //
        //            $subscription = new Subscription($attributes);
        //            $subscription->save();
        //
        //            return response()->json([
        //                'message' => 'Подписка на категорию успешно оформлена',
        //                'subscription' => new SubscriptionResource($subscription)
        //            ]);
        //        } else {
        //            return response()->json([
        //                'message' => 'Подписка не была оформлена. ',
        //            ]);
        //        }
    }
}
