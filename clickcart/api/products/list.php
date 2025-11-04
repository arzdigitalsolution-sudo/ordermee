<?php

declare(strict_types=1);

require_once __DIR__ . '/../../app/bootstrap.php';

use ClickCart\Models\Product;
use ClickCart\Helpers;

header('Content-Type: application/json');

$productModel = new Product();
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = min(50, max(1, (int)($_GET['per_page'] ?? 12)));
$offset = ($page - 1) * $perPage;

$products = $productModel->listPublished($perPage, $offset);
$total = $productModel->countPublished();

echo json_encode([
    'data' => $products,
    'pagination' => Helpers\paginate($total, $perPage, $page),
]);
