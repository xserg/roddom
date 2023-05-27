<?php

namespace App\Http\Controllers\Api\Payment;

use App\Enums\PaymentStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\AppInfo;
use App\Models\Category;
use App\Models\Lecture;
use App\Models\Order;
use App\Models\Promo;
use App\Models\Subscription;
use App\Models\User;
use App\Repositories\PeriodRepository;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use YooKassa\Common\Exceptions\ApiException;
use YooKassa\Common\Exceptions\BadApiRequestException;
use YooKassa\Common\Exceptions\ExtensionNotFoundException;
use YooKassa\Common\Exceptions\ForbiddenException;
use YooKassa\Common\Exceptions\InternalServerError;
use YooKassa\Common\Exceptions\NotFoundException;
use YooKassa\Common\Exceptions\ResponseProcessingException;
use YooKassa\Common\Exceptions\TooManyRequestsException;
use YooKassa\Common\Exceptions\UnauthorizedException;
use YooKassa\Model\Notification\NotificationSucceeded;
use YooKassa\Model\Notification\NotificationWaitingForCapture;
use YooKassa\Model\NotificationEventType;

class PaymentController extends Controller
{
    public function __construct(
        private PaymentService   $paymentService,
        private PeriodRepository $periodRepository
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

        $notification = ($requestBody['event'] === NotificationEventType::PAYMENT_SUCCEEDED)
            ? new NotificationSucceeded($requestBody)
            : new NotificationWaitingForCapture($requestBody);
        $payment = $notification->getObject();

        if (isset($payment->status) && $payment->status === 'waiting_for_capture') {
            $this->paymentService->getClient()->capturePayment([
                'amount' => $payment->amount,
            ], $payment->id, uniqid('', true));
        }

        if (isset($payment->status) && $payment->status === 'succeeded') {
            if ((bool) $payment->paid === true) {
                $metadata = (object) $payment->metadata;

                if (isset($metadata->order_id)) {

                    DB::transaction(function () use ($metadata) {
                        $orderId = (int) $metadata->order_id;
                        $order = Order::query()->findOrFail($orderId);
                        $order->status = PaymentStatusEnum::CONFIRMED;
                        $order->save();

                        //тут создаем подписку

                        $period = $this->periodRepository->getPeriodByLength($order->period);

                        $attributes = [
                            'user_id' => $order->user_id,
                            'subscriptionable_type' => $order->subscriptionable_type,
                            'subscriptionable_id' => $order->subscriptionable_id,
                            'period_id' => $period->id,
                            'total_price' => $order->price,
                            'start_date' => now(),
                            'end_date' => now()->addDays($period->length),
                        ];

                        $subscription = new Subscription($attributes);
                        if (! $subscription->save()) {
                            Log::warning($subscription);
                        }

                        $subscriptionableName = $this->getSubscriptionableName($order);

                        Mail::to(User::query()->find($order->user_id)->email)
                            ->send(new \App\Mail\PurchaseSuccess(
                                AppInfo::query()->first()->successful_purchase_text,
                                $subscriptionableName,
                                $attributes['start_date'],
                                $attributes['end_date']
                            ));
                    });
                }
            }
        }
    }

    private function getSubscriptionableName(Order $order): string
    {
        if ($order->subscriptionable_type == Lecture::class) {
            return 'Лекция: ' . Lecture::query()->find($order->subscriptionable_id)->title;
        } elseif ($order->subscriptionable_type == Category::class) {
            return 'Категория: ' . Category::query()->find($order->subscriptionable_id)->title;
        } elseif ($order->subscriptionable_type == Promo::class) {
            return 'Промопак лекций';
        }

        return 'Лекция';
    }
}
