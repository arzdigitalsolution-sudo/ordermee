<?php

declare(strict_types=1);

namespace ClickCart\Models;

use PDO;

class Order extends BaseModel
{
    public function create(array $data): int
    {
        $sql = 'INSERT INTO orders (order_number, buyer_id, total_amount, platform_fee, payment_method, payment_status, status, shipping_address, shipping_phone)
                VALUES (:order_number, :buyer_id, :total_amount, :platform_fee, :payment_method, :payment_status, :status, :shipping_address, :shipping_phone)';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'order_number' => $data['order_number'],
            'buyer_id' => $data['buyer_id'],
            'total_amount' => $data['total_amount'],
            'platform_fee' => $data['platform_fee'] ?? 0,
            'payment_method' => $data['payment_method'],
            'payment_status' => $data['payment_status'] ?? 'pending',
            'status' => $data['status'] ?? 'new',
            'shipping_address' => $data['shipping_address'] ?? null,
            'shipping_phone' => $data['shipping_phone'] ?? null,
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function findByNumber(string $orderNumber): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM orders WHERE order_number = :order_number LIMIT 1');
        $stmt->execute(['order_number' => $orderNumber]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        return $order ?: null;
    }

    public function find(int $orderId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM orders WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $orderId]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        return $order ?: null;
    }

    public function updateStatus(int $orderId, int $sellerId, string $status, ?string $shippingCourier = null, ?string $trackingNumber = null): bool
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM order_items WHERE order_id = :order_id AND seller_id = :seller_id');
        $stmt->execute([
            'order_id' => $orderId,
            'seller_id' => $sellerId,
        ]);

        if ((int)$stmt->fetchColumn() === 0) {
            return false;
        }

        $stmt = $this->db->prepare('UPDATE orders SET status = :status, shipping_courier = :shipping_courier, tracking_number = :tracking_number WHERE id = :id');
        return $stmt->execute([
            'status' => $status,
            'shipping_courier' => $shippingCourier,
            'tracking_number' => $trackingNumber,
            'id' => $orderId,
        ]);
    }

    public function markPaid(int $orderId): bool
    {
        $stmt = $this->db->prepare('UPDATE orders SET payment_status = "paid" WHERE id = :id');
        return $stmt->execute(['id' => $orderId]);
    }

    public function listBySeller(int $sellerId, string $status = null, int $limit = 20): array
    {
        $sql = 'SELECT o.*, oi.qty, oi.price, oi.platform_fee_applied, p.title AS product_title 
                FROM orders o
                INNER JOIN order_items oi ON oi.order_id = o.id
                INNER JOIN products p ON p.id = oi.product_id
                WHERE oi.seller_id = :seller_id';

        $params = ['seller_id' => $sellerId];
        if ($status) {
            $sql .= ' AND o.status = :status';
            $params['status'] = $status;
        }

        $sql .= ' ORDER BY o.created_at DESC LIMIT :limit';
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        foreach ($params as $key => $value) {
            if ($key === 'status') {
                $stmt->bindValue(':' . $key, $value, PDO::PARAM_STR);
            } else {
                $stmt->bindValue(':' . $key, $value, PDO::PARAM_INT);
            }
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function recentOrders(int $sellerId, int $limit = 5): array
    {
        $stmt = $this->db->prepare('SELECT o.order_number, o.total_amount, o.status, o.created_at 
                                     FROM orders o 
                                     INNER JOIN order_items oi ON oi.order_id = o.id 
                                     WHERE oi.seller_id = :seller_id 
                                     ORDER BY o.created_at DESC LIMIT :limit');
        $stmt->bindValue(':seller_id', $sellerId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
