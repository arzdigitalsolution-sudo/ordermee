<?php

declare(strict_types=1);

namespace ClickCart\Services\Payment;

interface PaymentGatewayInterface
{
    public function createPayment(array $payload): array;

    public function verifyWebhook(array $payload, string $signature): bool;
}
