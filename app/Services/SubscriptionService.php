<?php

namespace App\Services;

use App\Mail\PurchaseSuccess;
use App\Models\AppInfo;
use App\Models\Order;
use App\Models\Subscription;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class SubscriptionService
{
    public function createSubscription(Order $order, array $options = []): Subscription
    {
        $subscriptionAttributes = $this->getSubscriptionAttributes($order);
        $subscription = new Subscription(array_merge($subscriptionAttributes, $options));
        $subscription->save();

        $this->sendMail($subscription);

        return $subscription;
    }

    private function getSubscriptionAttributes(
        Order $order,
    ): array {
        return [
            'user_id' => $order->user_id,
            'subscriptionable_type' => $order->subscriptionable_type,
            'subscriptionable_id' => $order->subscriptionable_id,
            'lectures_count' => $order->lectures_count,
            'period_id' => $order->getPeriod()->id,
            'total_price' => $order->price,
            'price_to_pay' => $order->price_to_pay,
            'points' => $order->points,
            'start_date' => now(),
            'end_date' => now()->addDays($order->period),
            'exclude' => $order->exclude
        ];
    }

    private function sendMail(Subscription $subscription): void
    {
        $appInfo = AppInfo::first();
        $successfulPurchaseText = $appInfo?->successful_purchase_text ?? 'Спасибо за покупку';
        $email = $subscription->userEmail();
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
}
