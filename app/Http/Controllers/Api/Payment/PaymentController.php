<?php

namespace App\Http\Controllers\Api\Payment;

use App\Enums\PaymentStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Repositories\PeriodRepository;
use App\Services\PaymentService;
use Illuminate\Http\Request;
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

        if (
            (isset($payment->status) && $payment->status === 'succeeded')
            && $payment->paid === true
        ) {
            $metadata = $payment->metadata;

            if (! isset($metadata->order_id)) {
                return;
            }

            $orderId = (int) $metadata->order_id;
            $order = Order::query()->findOrFail($orderId);

            if ($order->isConfirmed()) {
                return;
            }

            $this->paymentService->confirmOrder($order, 'Покупка | Юкасса');
        }
    }
}
