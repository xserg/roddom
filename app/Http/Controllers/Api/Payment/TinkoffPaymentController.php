<?php

namespace App\Http\Controllers\Api\Payment;

use App\Enums\PaymentStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Repositories\PeriodRepository;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TinkoffPaymentController extends Controller
{
    public function __construct(
        private PaymentService   $paymentService,
        private PeriodRepository $periodRepository,
    ) {
    }

    public function __invoke(Request $request)
    {
        Log::warning('--------------------------------------');
        foreach ($request->all() as $key => $value){
            Log::warning($key);
            Log::error($value);
        }

        Log::warning('-------------------------------------------');
//        $notification = match ($requestBody['event']) {
//            NotificationEventType::PAYMENT_SUCCEEDED => new NotificationSucceeded($requestBody),
//            NotificationEventType::PAYMENT_WAITING_FOR_CAPTURE => new NotificationWaitingForCapture($requestBody),
//            NotificationEventType::PAYMENT_CANCELED => new NotificationCanceled($requestBody),
//        };
//
//        $payment = $notification->getObject();
//
//        if (isset($payment->status) && $payment->status === 'canceled') {
//            $metadata = $payment->metadata;
//
//            if (isset($metadata->order_id)) {
//                $orderId = (int) $metadata->order_id;
//                $order = Order::query()->findOrFail($orderId);
//
//                $order->status = PaymentStatusEnum::FAILED;
//                $order->save();
//            }
//        }
//
//        if (isset($payment->status) && $payment->status === 'waiting_for_capture') {
//            $this->paymentService->getClient()->capturePayment([
//                'amount' => $payment->amount,
//            ], $payment->id, uniqid('', true));
//        }
//
//        if (
//            (isset($payment->status) && $payment->status === 'succeeded')
//            && $payment->paid === true
//        ) {
//            $metadata = $payment->metadata;
//
//            if (! isset($metadata->order_id)) {
//                return;
//            }
//
//            $orderId = (int) $metadata->order_id;
//            $order = Order::query()->findOrFail($orderId);
//
//            if ($order->isConfirmed()) {
//                return;
//            }
//
//            $period = $this->periodRepository->getPeriodByLength($order->period);
//
//            $this->paymentService->confirmOrder($order, $period);
//        }
    }
}
