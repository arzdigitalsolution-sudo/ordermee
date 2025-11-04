<?php

declare(strict_types=1);

namespace ClickCart\Controllers;

use ClickCart\Models\Order;
use ClickCart\Models\Product;
use ClickCart\Models\User;

class SellerController
{
    public function __construct(
        private Product $productModel = new Product(),
        private Order $orderModel = new Order(),
        private User $userModel = new User()
    ) {
    }

    public function dashboardData(int $sellerId): array
    {
        $products = $this->productModel->listBySeller($sellerId);
        $recentOrders = $this->orderModel->recentOrders($sellerId, 5);

        $totalSales = 0;
        $totalRevenue = 0.0;
        $pendingOrders = 0;

        $orders = $this->orderModel->listBySeller($sellerId, null, 100);
        foreach ($orders as $order) {
            if ($order['status'] === 'new' || $order['status'] === 'processing') {
                $pendingOrders++;
            }
            $totalSales += (int)$order['qty'];
            $totalRevenue += (float)$order['price'] * (int)$order['qty'];
        }

        $user = $this->userModel->findById($sellerId);

        return [
            'products_count' => count($products),
            'total_sales' => $user['total_sales'] ?? $totalSales,
            'total_revenue' => $totalRevenue,
            'pending_orders' => $pendingOrders,
            'recent_orders' => $recentOrders,
            'low_stock' => array_filter($products, fn ($p) => ($p['quantity'] ?? 0) < 5),
            'platform_fee_paid' => (bool)($user['platform_fee_paid'] ?? 0),
        ];
    }
}
