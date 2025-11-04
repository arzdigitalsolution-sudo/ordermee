<?php

declare(strict_types=1);

namespace ClickCart\Models;

use PDO;

class OrderItem extends BaseModel
{
    public function create(int $orderId, array $item): int
    {
        $stmt = $this->db->prepare('INSERT INTO order_items (order_id, product_id, seller_id, qty, price, platform_fee_applied) VALUES (:order_id, :product_id, :seller_id, :qty, :price, :platform_fee_applied)');
        $stmt->execute([
            'order_id' => $orderId,
            'product_id' => $item['product_id'],
            'seller_id' => $item['seller_id'],
            'qty' => $item['qty'],
            'price' => $item['price'],
            'platform_fee_applied' => $item['platform_fee_applied'] ?? 0,
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function getByOrder(int $orderId): array
    {
        $stmt = $this->db->prepare('SELECT oi.*, p.title FROM order_items oi INNER JOIN products p ON p.id = oi.product_id WHERE order_id = :order_id');
        $stmt->execute(['order_id' => $orderId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
