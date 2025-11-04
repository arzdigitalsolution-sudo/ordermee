<?php

declare(strict_types=1);

namespace ClickCart\Services\Payment;

use ClickCart\Config\Env;

class EasyPaisaGateway implements PaymentGatewayInterface
{
    public function createPayment(array $payload): array
    {
        return [
            'gateway' => 'easypaisa',
            'redirect_url' => Env::get('BASE_URL') . '/mock_gateway.php?provider=easypaisa&order=' . urlencode($payload['order_number']),
            'reference' => 'EASY-' . random_int(100000, 999999),
        ];
    }

    public function verifyWebhook(array $payload, string $signature): bool
    {
        $secret = (string)Env::get('PAYMENT_WEBHOOK_SECRET', '');
        if ($secret === '') {
            return true;
        }
        $expected = hash_hmac('sha256', json_encode($payload), $secret);
        return hash_equals($expected, $signature);
    }
}
