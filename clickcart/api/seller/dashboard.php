<?php

declare(strict_types=1);

require_once __DIR__ . '/../../app/bootstrap.php';

use ClickCart\Controllers\SellerController;
use ClickCart\Helpers;

$user = Helpers\auth_user();
if (!$user || $user['role'] !== 'seller') {
    Helpers\respond_json(['error' => 'Unauthorized'], 401);
}

$controller = new SellerController();
$data = $controller->dashboardData((int)$user['id']);

Helpers\respond_json($data);
