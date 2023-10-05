<?php

namespace App\Services;

use App\Dto\DiscountsDto;
use App\Enums\PaymentStatusEnum;
use App\Mail\PurchaseSuccess;
use App\Models\AppInfo;
use App\Models\Order;
use App\Models\Period;
use App\Models\RefInfo;
use App\Models\RefPointsPayments;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use YooKassa\Client;

class PaymentService
{
    public function __construct(
        private readonly SubscriptionService $subscriptionService
    ) {
    }

    public function getClient()
    {
        $client = new Client();
        $client->setAuth(config('services.yookassa.shop_id'), config('services.yookassa.secret_key'));

        return $client;
    }

    public function createPayment(float $amount, array $options = []): string
    {
        $client = $this->getClient()
            ->createPayment([
                'amount' => [
                    'value' => $amount,
                    'currency' => 'RUB',
                ],
                'capture' => false,
                'confirmation' => [
                    'type' => 'redirect',
                    'return_url' => config('app.back2site'),
                ],
                'metadata' => [
                    'order_id' => $options['order_id'],
                ],
                'receipt' => [
                    'customer' => [
                        'email' => $options['buyer_email']
                    ],
                    'items' => [
                        [
                            'description' => $options['description'], // название товара
                            'amount' => $options['amount'], // цена товара
                            'vat_code' => 2, // ндс ставка, код варианта https://yookassa.ru/developers/payment-acceptance/receipts/54fz/parameters-values#vat-codes
                            'quantity' => $options['quantity'], // количество
                        ]
                    ]
                ]
            ], uniqid('', true));

        return $client->getConfirmation()->getConfirmationUrl();
    }

    public function confirmOrder(Order $order, ?string $description = null): void
    {
        DB::transaction(function () use (
            $order,
            $description
        ) {
            $order->status = PaymentStatusEnum::CONFIRMED;
            /**
             * @var User $orderedUser
             */
            $orderedUser = $order->user;
            if ($order->points) {
                $orderedUser->refPoints()->decrement('points', $order->points);
                $orderedUser->refPointsMadePayments()->create([
                    'ref_points' => $order->points,
                    'reason' => RefPointsPayments::REASON_BUY,
                    'price' => $order->price,
                    'price_to_pay' => $order->price_to_pay
                ]);
            }
            $order->save();
            $this->subscriptionService->createSubscription($order, ['description' => $description]);
        });

        $this->rewardReferrersForBuying($order, $order->user);
    }


    public function rewardReferrersForBuying(Order $order, User $buyer): void
    {
        $refInfo = RefInfo::query()->first();
        $residualAmount = $order->price - $order->points;

        if ($residualAmount > 0) {
            $relationships = [
                1 => $buyer->referrer(),
                2 => $buyer->referrerSecondLevel(),
                3 => $buyer->referrerThirdLevel(),
                4 => $buyer->referrerFourthLevel(),
                5 => $buyer->referrerFifthLevel(),
            ];

            foreach ($relationships as $depth => $relationship) {
                if ($relationship->doesntExist()) {
                    break;
                }

                /** @var User $referrer */
                $referrer = $relationship->first();

                if ($depth > 1 && $referrer->ref_type->isHorizontal()) {
                    continue;
                }

                if ($depth === 1 && $referrer->ref_type->isHorizontal()) {
                    $percent = $refInfo->firstWhere('depth_level', 1.1)?->percent ?? 20;
                } else {
                    $percent = $refInfo->firstWhere('depth_level', $depth)?->percent ?? 5;
                }

                $pointsToGet = $residualAmount * ($percent / 100);

                $referrer->refPointsGetPayments()->create([
                    'payer_id' => $order->user->id,
                    'reason' => RefPointsPayments::REASON_BUY,
                    'ref_points' => $pointsToGet,
                    'price' => $order->price,
                    'price_to_pay' => $order->price_to_pay,
                    'depth_level' => $depth,
                    'percent' => $percent,
                ]);

                if ($referrer->refPoints()->exists()) {
                    $refPoints = $referrer->refPoints;
                    $refPoints->points += $pointsToGet;
                    $refPoints->save();
                } else {
                    $referrer->refPoints()->create(['points' => $pointsToGet]);
                }
            }
        }

//        adjacency-list

//        if (
//            $buyer->ancestors()
//                ->whereDepth('>', -6)
//                ->whereDepth('<', -1)
//                ->exists()
//        ) {
//            $ancestors = $buyer->ancestors()
//                ->whereDepth('>', -6)
//                ->whereDepth('<', -1)
//                ->get();
//
//            $ancestors->each(function ($ancestor) use ($order, $refInfo) {
//                $percent = $refInfo->firstWhere('depth_level', 2)->percent;
//                $residualAmount = $order->price - $order->points;
//
//                if ($residualAmount <= 0) {
//                    return;
//                }
//
//                $pointsToGet = $residualAmount * ($percent / 100);
//                $ancestor->refPointsGetPayments()->create([
//                    'payer_id' => $order->user->id,
//                    'reason' => RefPointsPayments::REASON_BUY,
//                    'ref_points' => $pointsToGet,
//                    'price' => $order->price,
//                    'depth_level' => 1,
//                    'percent' => $percent,
//                ]);
//
//                if ($ancestor->refPoints()->exists()) {
//                    $refPoints = $ancestor->refPoints;
//                    $refPoints->points += $pointsToGet;
//                    $refPoints->save();
//                } else {
//                    $ancestor->refPoints()->create(['points' => $pointsToGet]);
//                }
//            });
//        }
    }

    public function resolveDiscounts(
        Collection $purchasedLectures,
        Collection $lecturesToPurchase,
        int        $initialPrice,
        ?int       $initialPricePromo = null,
    ): DiscountsDto {

        $purchasedIds = $purchasedLectures->pluck('id');
        $toPurchasedIds = $lecturesToPurchase->pluck('id');

        $lecturesToExcludeIds = $toPurchasedIds->intersect($purchasedIds);
        $intersectCount = $lecturesToExcludeIds->count();

        if ($intersectCount === 0) {
            return new DiscountsDto();
        }

        $categoryLecturesCount = $lecturesToPurchase->count();

        $intersectPercent = $intersectCount * 100 / $categoryLecturesCount;

        $discountedOn = (int) ($initialPrice * ($intersectPercent / 100));
        $discountedOnPromo = (int) ($initialPricePromo * ($intersectPercent / 100));

        return new DiscountsDto(
            true,
            $intersectPercent,
            $intersectCount,
            $discountedOn,
            $discountedOnPromo,
            $lecturesToExcludeIds
        );
    }
}
