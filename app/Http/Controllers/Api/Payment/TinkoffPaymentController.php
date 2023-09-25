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
        Log::warning('----------------NOT-SIGNED--------------------');
        foreach ($request->all() as $key => $value) {
            Log::warning($key);
            Log::error($value);
        }
        Log::warning('------------------NOT-SIGNED-END-------------------------');

        if ($request->status && $request->status === 'signed') {
            Log::warning('----------------SIGNED--------------------');
            foreach ($request->all() as $key => $value) {
                Log::warning($key);
                Log::error($value);
            }
            Log::warning('------------------SIGNED-END-------------------------');

            if (is_null($request->id)) {
                return;
            }

            $orderId = (string) $request->id;
            $order = Order::query()->firstWhere(['code' => $orderId]);

            if (
                is_null($order)
                || $order->isConfirmed()
                || ! $request->order_amount
            ) {
                return;
            }

            $orderAmountInCoins = (int) ($request->order_amount * 100);
            if ($order->price_to_pay !== $orderAmountInCoins) {
                $order->status = PaymentStatusEnum::FAILED;
                $order->description = 'Сумма заказа не совпадает с суммой кредита ' . "$orderAmountInCoins и $order->price";
                $order->save();

                return;
            }

            $period = $this->periodRepository->getPeriodByLength($order->period);
            $this->paymentService->confirmOrder($order, $period, 'Покупка | Тинькофф кредит/рассрочка');
        }
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
//        }
    }
}
