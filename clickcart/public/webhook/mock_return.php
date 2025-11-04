<?php

declare(strict_types=1);

require_once __DIR__ . '/../../app/bootstrap.php';

use ClickCart\Controllers\OrderController;
use ClickCart\Helpers;

$orderNumber = $_GET['order'] ?? null;
if (!$orderNumber) {
    die('Missing order number');
}

$controller = new OrderController();
$payload = [
    'order_number' => $orderNumber,
    'gateway' => 'mock',
    'status' => 'paid',
];
$controller->handleWebhook($payload, '');

Helpers\flash('success', 'Payment simulated successfully for order ' . $orderNumber);
Helpers\redirect(Helpers\app_url('dashboard/orders.php'));
