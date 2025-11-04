<?php

declare(strict_types=1);

require_once __DIR__ . '/../../app/bootstrap.php';

use ClickCart\Helpers;

$user = Helpers\auth_user();
if (!$user || $user['role'] !== 'seller') {
    Helpers\respond_json(['error' => 'Unauthorized'], 401);
}

$since = (int)($_GET['since'] ?? (time() - 600));
$sinceTime = date('Y-m-d H:i:s', $since);

$db = Helpers\db();
$stmt = $db->prepare('SELECT DISTINCT o.order_number, o.total_amount, o.status, o.created_at
                       FROM orders o
                       INNER JOIN order_items oi ON oi.order_id = o.id
                       WHERE oi.seller_id = :seller_id AND o.created_at > :since AND o.payment_status = "paid"
                       ORDER BY o.created_at DESC');
$stmt->execute([
    'seller_id' => $user['id'],
    'since' => $sinceTime,
]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

Helpers\respond_json([
    'newOrders' => $orders,
    'now' => time(),
]);
