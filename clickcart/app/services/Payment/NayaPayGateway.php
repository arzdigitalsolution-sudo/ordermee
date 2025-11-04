<?php

declare(strict_types=1);

namespace ClickCart\Services\Payment;

use ClickCart\Config\Env;

class NayaPayGateway implements PaymentGatewayInterface
{
    public function createPayment(array $payload): array
    {
        return [
            'gateway' => 'nayapay',
            'redirect_url' => Env::get('BASE_URL') . '/mock_gateway.php?provider=nayapay&order=' . urlencode($payload['order_number']),
            'reference' => 'NAYA-' . random_int(100000, 999999),
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
