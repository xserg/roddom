<?php

namespace App\Services;

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
}
