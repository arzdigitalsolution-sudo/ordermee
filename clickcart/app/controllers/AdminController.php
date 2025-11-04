<?php

declare(strict_types=1);

namespace ClickCart\Controllers;

use ClickCart\Models\Order;
use ClickCart\Models\Product;
use ClickCart\Models\User;

class AdminController
{
    public function __construct(
        private User $userModel = new User(),
        private Product $productModel = new Product(),
        private Order $orderModel = new Order()
    ) {
    }

    public function listUsers(): array
    {
        return $this->userModel->listSellers();
    }

    public function resetPlatformFee(int $sellerId): void
    {
        $this->userModel->resetPlatformFee($sellerId);
    }

    public function ordersOverview(): array
    {
        // Simplified metrics for admin
        return [
            'total_orders' => $this->countOrders(),
            'pending_orders' => $this->countOrders('new'),
            'processing_orders' => $this->countOrders('processing'),
        ];
    }

    private function countOrders(?string $status = null): int
    {
        $db = \ClickCart\Helpers\db();
        $sql = 'SELECT COUNT(*) FROM orders';
        $params = [];
        if ($status) {
            $sql .= ' WHERE status = :status';
            $params['status'] = $status;
        }
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }
}
