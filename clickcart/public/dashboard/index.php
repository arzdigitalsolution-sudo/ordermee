<?php

declare(strict_types=1);

require_once __DIR__ . '/../../app/bootstrap.php';

use ClickCart\Controllers\SellerController;
use ClickCart\Helpers;

Helpers\require_auth('seller');

$user = Helpers\auth_user();
$controller = new SellerController();
$data = $controller->dashboardData((int)$user['id']);
$title = 'Seller Dashboard';

require_once __DIR__ . '/../../app/views/layouts/header.php';
?>

<div class="dashboard" data-seller-id="<?= (int)$user['id'] ?>">
    <?php $active = 'overview'; include __DIR__ . '/../../app/views/seller/sidebar.php'; ?>
    <section class="dashboard-content">
        <header>
            <h1>Welcome back, <?= Helpers\sanitize($user['brand_name'] ?? $user['name']) ?></h1>
            <p class="note">First 10 sales are free. <?php if ($data['platform_fee_paid']): ?>Platform fee has been settled.<?php else: ?>Your next sale after <?= (int)$user['total_sales'] ?> will include a one-time ₨100 platform fee.<?php endif; ?></p>
        </header>

        <div class="stats-grid">
            <div class="stat-card">
                <span class="label">Products</span>
                <span class="value"><?= (int)$data['products_count'] ?></span>
            </div>
            <div class="stat-card">
                <span class="label">Total Sales</span>
                <span class="value"><?= (int)$data['total_sales'] ?></span>
            </div>
            <div class="stat-card">
                <span class="label">Revenue</span>
                <span class="value">₨<?= number_format($data['total_revenue'], 2) ?></span>
            </div>
            <div class="stat-card">
                <span class="label">Pending Orders</span>
                <span class="value"><?= (int)$data['pending_orders'] ?></span>
            </div>
        </div>

        <section class="card">
            <h2>Recent Orders</h2>
            <table class="table" id="recent-orders">
                <thead>
                <tr>
                    <th>Order #</th>
                    <th>Status</th>
                    <th>Total</th>
                    <th>Date</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($data['recent_orders'] as $order): ?>
                    <tr>
                        <td><?= Helpers\sanitize($order['order_number']) ?></td>
                        <td><?= Helpers\sanitize(ucfirst($order['status'])) ?></td>
                        <td>₨<?= number_format($order['total_amount'] ?? 0, 2) ?></td>
                        <td><?= Helpers\sanitize($order['created_at'] ?? '') ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </section>

        <section class="card">
            <h2>Low Stock Alerts</h2>
            <?php if (empty($data['low_stock'])): ?>
                <p>No low stock items.</p>
            <?php else: ?>
                <ul>
                    <?php foreach ($data['low_stock'] as $product): ?>
                        <li><?= Helpers\sanitize($product['title']) ?> — Qty: <?= (int)$product['quantity'] ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>
    </section>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const sellerId = document.querySelector('.dashboard').dataset.sellerId;
        let lastCheck = Math.floor(Date.now() / 1000);

        setInterval(() => {
            $.getJSON('<?= Helpers\app_url('api/seller/orders_poll.php') ?>', {since: lastCheck}, function (res) {
                if (res.now) {
                    lastCheck = res.now;
                }
                if (res.newOrders && res.newOrders.length) {
                    res.newOrders.forEach(order => {
                        ClickCart.toast('New order #' + order.order_number, 'success');
                        $('#recent-orders tbody').prepend(`
                            <tr>
                                <td>${order.order_number}</td>
                                <td>${order.status}</td>
                                <td>₨${parseFloat(order.total_amount).toFixed(2)}</td>
                                <td>${order.created_at}</td>
                            </tr>
                        `);
                    });
                }
            });
        }, 10000);
    });
</script>

<?php require_once __DIR__ . '/../../app/views/layouts/footer.php'; ?>
