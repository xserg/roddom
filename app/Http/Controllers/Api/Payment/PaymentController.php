<?php

namespace App\Http\Controllers\Api\Payment;

use App\Enums\PaymentStatusEnum;
use App\Http\Controllers\Controller;
use App\Jobs\SyncSubscriptionItemsJob;
use App\Mail\PurchaseSuccess;
use App\Models\AppInfo;
use App\Models\Category;
use App\Models\Lecture;
use App\Models\Order;
use App\Models\Period;
use App\Models\RefInfo;
use App\Models\RefPoints;
use App\Models\RefPointsPayments;
use App\Models\Subscription;
use App\Models\User;
use App\Repositories\PeriodRepository;
use App\Services\PaymentService;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use YooKassa\Common\Exceptions\ApiException;
use YooKassa\Common\Exceptions\BadApiRequestException;
use YooKassa\Common\Exceptions\ExtensionNotFoundException;
use YooKassa\Common\Exceptions\ForbiddenException;
use YooKassa\Common\Exceptions\InternalServerError;
use YooKassa\Common\Exceptions\NotFoundException;
use YooKassa\Common\Exceptions\ResponseProcessingException;
use YooKassa\Common\Exceptions\TooManyRequestsException;
use YooKassa\Common\Exceptions\UnauthorizedException;
use YooKassa\Model\Notification\NotificationCanceled;
use YooKassa\Model\Notification\NotificationSucceeded;
use YooKassa\Model\Notification\NotificationWaitingForCapture;
use YooKassa\Model\NotificationEventType;

class PaymentController extends Controller
{
    public function __construct(
        private PaymentService   $paymentService,
        private PeriodRepository $periodRepository,
        private UserService      $userService,
    ) {
    }

    /**
     * @throws NotFoundException
     * @throws ApiException
     * @throws ResponseProcessingException
     * @throws BadApiRequestException
     * @throws ExtensionNotFoundException
     * @throws InternalServerError
     * @throws ForbiddenException
     * @throws TooManyRequestsException
     * @throws UnauthorizedException
     */
    public function __invoke(Request $request)
    {
        $source = file_get_contents('php://input');
        $requestBody = json_decode($source, true);

        $notification = match ($requestBody['event']) {
            NotificationEventType::PAYMENT_SUCCEEDED => new NotificationSucceeded($requestBody),
            NotificationEventType::PAYMENT_WAITING_FOR_CAPTURE => new NotificationWaitingForCapture($requestBody),
            NotificationEventType::PAYMENT_CANCELED => new NotificationCanceled($requestBody),
        };

        $payment = $notification->getObject();

        if (isset($payment->status) && $payment->status === 'canceled') {
            $metadata = $payment->metadata;

            if (isset($metadata->order_id)) {
                $orderId = (int) $metadata->order_id;
                $order = Order::query()->findOrFail($orderId);

                $order->status = PaymentStatusEnum::FAILED;
                $order->save();
            }
        }

        if (isset($payment->status) && $payment->status === 'waiting_for_capture') {
            $this->paymentService->getClient()->capturePayment([
                'amount' => $payment->amount,
            ], $payment->id, uniqid('', true));
        }

        if (isset($payment->status) && $payment->status === 'succeeded') {
            if ($payment->paid === true) {
                $metadata = $payment->metadata;

                if (isset($metadata->order_id)) {
                    $orderId = (int) $metadata->order_id;
                    $order = Order::query()->findOrFail($orderId);

                    if ($order->isConfirmed()) {
                        return;
                    }

                    $period = $this->periodRepository->getPeriodByLength($order->period);
                    $subscriptionAttributes = $this->getSubscriptionAttributes(
                        $order, $period
                    );

                    $subscription = new Subscription($subscriptionAttributes);

                    DB::transaction(function () use (
                        $order,
                        $subscription,
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
                            ]);
                        }
                        $order->save();
                        $subscription->save();
                    });

                    $this->userService->rewardReferrersForBuying($order, $order->user);
                    $appInfo = AppInfo::first();
                    $successfulPurchaseText = $appInfo?->successful_purchase_text ?? 'Спасибо за покупку';
                    $email = $order->userEmail();
                    $image = Storage::url($appInfo->successful_purchase_image);
                    $appLink = $appInfo?->app_link_share_link ?? config('app.frontend_url');
                    $appName = $appInfo?->app_title ?? config('app.name');

                    Mail::to($email)
                        ->send(
                            (new PurchaseSuccess(
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
            }
        }
    }

    private function getSubscriptionAttributes(
        Order  $order,
        Period $period,
    ): array {
        return [
            'user_id' => $order->user_id,
            'subscriptionable_type' => $order->subscriptionable_type,
            'subscriptionable_id' => $order->subscriptionable_id,
            'period_id' => $period->id,
            'total_price' => $order->price,
            'points' => $order->points,
            'start_date' => now(),
            'end_date' => now()->addDays($period->length),
        ];
    }
}
