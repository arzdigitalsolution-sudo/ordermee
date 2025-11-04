<?php

declare(strict_types=1);

namespace ClickCart\Controllers;

use ClickCart\Helpers;
use ClickCart\Helpers\Cart;
use ClickCart\Models\Order;
use ClickCart\Models\OrderItem;
use ClickCart\Models\User;
use ClickCart\Models\Product;
use ClickCart\Services\Payment\PaymentGatewayFactory;

class OrderController
{
    public function __construct(
        private Order $orderModel = new Order(),
        private OrderItem $orderItemModel = new OrderItem(),
        private User $userModel = new User(),
        private Product $productModel = new Product()
    ) {
    }

    public function createOrder(int $buyerId, array $input): array
    {
        $cart = Cart\get_cart();
        if (!$cart) {
            return ['success' => false, 'message' => 'Cart is empty'];
        }

        $validatedItems = [];
        foreach ($cart as $item) {
            $product = $this->productModel->find((int)$item['product_id']);
            if (!$product || $product['status'] !== 'published') {
                return ['success' => false, 'message' => 'One or more products are unavailable'];
            }

            if ((int)$product['quantity'] < (int)$item['qty']) {
                return ['success' => false, 'message' => 'Insufficient stock for ' . $product['title']];
            }

            $price = (float)($product['sale_price'] ?: $product['price']);
            $validatedItems[] = [
                'product_id' => (int)$product['id'],
                'seller_id' => (int)$product['seller_id'],
                'title' => $product['title'],
                'price' => $price,
                'qty' => (int)$item['qty'],
            ];
        }

        $grouped = [];
        foreach ($validatedItems as $item) {
            $grouped[$item['seller_id']][] = $item;
        }

        $platformFeeTotal = 0;
        $orderItems = [];
        $grandTotal = 0.0;
        $feeNotes = [];

        foreach ($grouped as $sellerId => $items) {
            $seller = $this->userModel->findById((int)$sellerId);
            if (!$seller) {
                continue;
            }

            $sellerSubtotal = 0;
            foreach ($items as $item) {
                $sellerSubtotal += $item['price'] * $item['qty'];
                $orderItems[] = [
                    'product_id' => $item['product_id'],
                    'seller_id' => $sellerId,
                    'qty' => $item['qty'],
                    'price' => $item['price'],
                    'platform_fee_applied' => 0,
                ];
            }

            $applyFee = ($seller['total_sales'] ?? 0) >= 10 && (int)$seller['platform_fee_paid'] === 0;
            if ($applyFee) {
                $platformFeeTotal += 100;
                $feeNotes[] = ['seller_id' => $sellerId, 'brand_name' => $seller['brand_name']];
                foreach ($orderItems as &$orderItem) {
                    if ($orderItem['seller_id'] === $sellerId) {
                        $orderItem['platform_fee_applied'] = 1;
                    }
                }
                unset($orderItem);
            }

            $grandTotal += $sellerSubtotal;
        }

        $grandTotal += $platformFeeTotal;

        $orderNumber = 'CC-' . strtoupper(bin2hex(random_bytes(4)));

        $orderId = $this->orderModel->create([
            'order_number' => $orderNumber,
            'buyer_id' => $buyerId,
            'total_amount' => $grandTotal,
            'platform_fee' => $platformFeeTotal,
            'payment_method' => $input['payment_method'],
            'payment_status' => 'pending',
            'status' => 'new',
            'shipping_address' => $input['shipping_address'] ?? null,
            'shipping_phone' => $input['shipping_phone'] ?? null,
        ]);

        foreach ($orderItems as $item) {
            $this->orderItemModel->create($orderId, $item);
        }

        $gateway = PaymentGatewayFactory::make($input['payment_method']);
        $payment = $gateway->createPayment([
            'order_id' => $orderId,
            'order_number' => $orderNumber,
            'amount' => $grandTotal,
            'callback_url' => Helpers\app_url('webhook/mock_return.php'),
        ]);

        Cart\clear_cart();

        return [
            'success' => true,
            'order_id' => $orderId,
            'order_number' => $orderNumber,
            'redirect_url' => $payment['redirect_url'],
            'payment_reference' => $payment['reference'] ?? null,
            'platform_fee_total' => $platformFeeTotal,
            'fee_notes' => $feeNotes,
        ];
    }

    public function handleWebhook(array $payload, string $signature = ''): array
    {
        $orderNumber = $payload['order_number'] ?? null;
        if (!$orderNumber) {
            return ['success' => false, 'message' => 'Order number missing'];
        }

        $order = $this->orderModel->findByNumber($orderNumber);
        if (!$order) {
            return ['success' => false, 'message' => 'Order not found'];
        }

        $gateway = PaymentGatewayFactory::make($payload['gateway'] ?? 'mock');
        if (!$gateway->verifyWebhook($payload, $signature)) {
            return ['success' => false, 'message' => 'Invalid signature'];
        }

        $this->orderModel->markPaid((int)$order['id']);

        $items = $this->orderItemModel->getByOrder((int)$order['id']);
        foreach ($items as $item) {
            $this->userModel->incrementSales((int)$item['seller_id'], (int)$item['qty']);
            if ((int)$item['platform_fee_applied'] === 1) {
                $this->userModel->markPlatformFeePaid((int)$item['seller_id']);
            }
            $this->productModel->reduceStock((int)$item['product_id'], (int)$item['qty']);
        }

        // Email notifications would be triggered here (omitted for brevity)

        return ['success' => true];
    }
}
