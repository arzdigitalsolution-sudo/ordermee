<?php

declare(strict_types=1);

namespace ClickCart\Services\Payment;

class PaymentGatewayFactory
{
    public static function make(string $method): PaymentGatewayInterface
    {
        return match ($method) {
            'jazzcash' => new JazzCashGateway(),
            'easypaisa' => new EasyPaisaGateway(),
            'nayapay' => new NayaPayGateway(),
            default => new MockGateway(),
        };
    }
}
