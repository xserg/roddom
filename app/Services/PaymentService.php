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
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use YooKassa\Client;

class PaymentService
{
    public function getClient()
    {
        $client = new Client();
        $client->setAuth(config('services.yookassa.shop_id'), config('services.yookassa.secret_key'));

        return $client;
    }

    public function createPayment(float $amount, array $options = [])
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
            ], uniqid('', true));

        return $client->getConfirmation()->getConfirmationUrl();
    }

    public function confirmOrder(Order $order, Period $period, ?string $description = null): void
    {
        $subscriptionAttributes = $this->getSubscriptionAttributes(
            $order, $period
        );

        $subscription = new Subscription($subscriptionAttributes);

        DB::transaction(function () use (
            $order,
            $subscription,
            $description
        ) {
            $order->status = PaymentStatusEnum::CONFIRMED;
            $subscription->description = $description;
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
            $subscription->save();
        });

        $this->rewardReferrersForBuying($order, $order->user);
        $appInfo = AppInfo::first();
        $successfulPurchaseText = $appInfo?->successful_purchase_text ?? 'Спасибо за покупку';
        $email = $order->userEmail();
        $image = Storage::url($appInfo->successful_purchase_image);
        $appLink = $appInfo?->app_link_share_link ?? config('app.frontend_url');
        $appName = $appInfo?->app_title ?? config('app.name');

        Mail::to($email)
            ->send((new PurchaseSuccess(
                'Успешная покупка',
                $appLink,
                $appName,
                $successfulPurchaseText,
                $subscription->entity_title,
                $subscription->start_date->isoFormat('HH:mm DD.MM.YYYY'),
                $subscription->end_date->isoFormat('HH:mm DD.MM.YYYY'),
                $image
            )));
    }

    private function getSubscriptionAttributes(
        Order  $order,
        Period $period,
    ): array {
        return [
            'user_id' => $order->user_id,
            'subscriptionable_type' => $order->subscriptionable_type,
            'subscriptionable_id' => $order->subscriptionable_id,
            'lectures_count' => $order->lectures_count,
            'period_id' => $period->id,
            'total_price' => $order->price,
            'price_to_pay' => $order->price_to_pay,
            'points' => $order->points,
            'start_date' => now(),
            'end_date' => now()->addDays($period->length),
            'exclude' => $order->exclude
        ];
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
