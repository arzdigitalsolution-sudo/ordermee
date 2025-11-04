<?php

declare(strict_types=1);

require_once __DIR__ . '/../../app/bootstrap.php';

use ClickCart\Controllers\OrderController;

$payload = json_decode(file_get_contents('php://input'), true) ?? [];
$signature = $_SERVER['HTTP_X_SIGNATURE'] ?? '';

$controller = new OrderController();
$result = $controller->handleWebhook($payload, $signature);

http_response_code($result['success'] ? 200 : 400);
echo json_encode($result);
