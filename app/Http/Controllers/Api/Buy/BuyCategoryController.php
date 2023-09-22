<?php

namespace App\Http\Controllers\Api\Buy;

use App\Exceptions\Custom\UserCannotBuyAlreadyBoughtCategoryException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Buy\BuyCategoryRequest;
use App\Models\Category;
use App\Models\Order;
use App\Repositories\CategoryRepository;
use App\Repositories\PeriodRepository;
use App\Services\CategoryService;
use App\Services\PurchaseService;
use App\Services\PaymentService;
use App\Traits\MoneyConversion;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

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
        private PeriodRepository   $periodRepository,
        private PaymentService     $paymentService,
        private PurchaseService    $purchaseService
    ) {
    }

    public function __invoke(
        BuyCategoryRequest $request,
        int                $categoryId,
        int                $period
    ) {
        $order = $this->createOrder($request, $categoryId, $period);

        $link = $this->paymentService->createPayment(
            self::coinsToRoubles($order->price_to_pay),
            ['order_id' => $order->id]
        );

        return response()->json(['link' => $link]);
    }

    public function prepareOrderForTinkoff(
        BuyCategoryRequest $request,
        int                $categoryId,
        int                $period
    ) {
        $order = $this->createOrder($request, $categoryId, $period);

        return response()->json([$order->code]);
    }

    private function createOrder(BuyCategoryRequest $request, int $categoryId, int $period): Order
    {
        $periodId = $this->periodRepository->getPeriodByLength($period)->id;
        $isPurchased = $this->categoryService->isCategoryPurchased($categoryId);

        if ($isPurchased) {
            throw new UserCannotBuyAlreadyBoughtCategoryException();
        }

        $price = $this->categoryService->getCategoryPriceForPeriod($categoryId, $periodId);
        $refPointsToSpend = self::roublesToCoins($request->validated('ref_points', 0));

        return $this->purchaseService->resolveOrder(
            auth()->id(),
            Category::class,
            $categoryId,
            $price,
            $period,
            $refPointsToSpend
        );
    }
}
