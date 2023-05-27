<?php

namespace App\Http\Controllers\Api\Payment;

use App\Enums\PaymentStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\AppInfo;
use App\Models\Category;
use App\Models\Lecture;
use App\Models\Order;
use App\Models\Period;
use App\Models\Promo;
use App\Models\Subscription;
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
                    $orderId = (int) $metadata->order_id;
                    $order = Order::query()->findOrFail($orderId);

                    if($order->isConfirmed()){
                        return;
                    }

                    $period = $this->periodRepository->getPeriodByLength($order->period);
                    $subscriptionableName = $this->getSubscriptionableName($order);

                    $subscriptionAttributes = $this->getSubscriptionAttributes(
                        $order, $period, $subscriptionableName
                    );

                    $subscription = new Subscription($subscriptionAttributes);

                    DB::transaction(function () use (
                        $order,
                        $subscriptionableName,
                        $subscription,
                    ) {
                        $order->status = PaymentStatusEnum::CONFIRMED;
                        $order->save();
                        $subscription->save();
                    });

                    $successfulPurchaseText = AppInfo::query()->first()
                        ?->successful_purchase_text ?? 'Спасибо за покупку';
                    $email = $order->userEmail();

                    Mail::to($email)
                        ->send(new \App\Mail\PurchaseSuccess(
                            'Успешная покупка',
                            $successfulPurchaseText,
                            $subscriptionableName,
                            $subscription->start_date,
                            $subscription->end_date
                        ));
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

        return 'Заголовок лекции не определён';
    }

    private function getSubscriptionAttributes(
        Order  $order,
        Period $period,
        string $subscriptionableName
    ): array {
        return [
            'user_id' => $order->user_id,
            'subscriptionable_type' => $order->subscriptionable_type,
            'subscriptionable_id' => $order->subscriptionable_id,
            'period_id' => $period->id,
            'total_price' => $order->price,
            'entity_title' => $subscriptionableName,
            'start_date' => now(),
            'end_date' => now()->addDays($period->length),
        ];
    }
}
