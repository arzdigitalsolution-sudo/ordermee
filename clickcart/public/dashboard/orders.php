<?php

declare(strict_types=1);

require_once __DIR__ . '/../../app/bootstrap.php';

use ClickCart\Helpers;
use ClickCart\Models\Order;
use ClickCart\Models\OrderItem;

Helpers\require_auth('seller');
$user = Helpers\auth_user();
$orderModel = new Order();
$orderItemModel = new OrderItem();

$statusFilter = $_GET['status'] ?? null;
$validStatuses = ['new', 'processing', 'shipped', 'delivered', 'cancelled'];
if ($statusFilter && !in_array($statusFilter, $validStatuses, true)) {
    $statusFilter = null;
}

if (Helpers\request_method() === 'POST') {
    if (!Helpers\verify_csrf($_POST['csrf_token'] ?? '')) {
        Helpers\flash('error', 'Invalid request.');
    } else {
        $orderId = (int)($_POST['order_id'] ?? 0);
        $status = $_POST['status'] ?? 'processing';
        $courier = trim($_POST['shipping_courier'] ?? '');
        $tracking = trim($_POST['tracking_number'] ?? '');

        if (in_array($status, $validStatuses, true)) {
            if ($orderModel->updateStatus($orderId, (int)$user['id'], $status, $courier ?: null, $tracking ?: null)) {
                Helpers\flash('success', 'Order updated.');
            } else {
                Helpers\flash('error', 'Order update failed.');
            }
        }
    }
    Helpers\redirect(Helpers\app_url('dashboard/orders.php?status=' . ($statusFilter ?? '')));
}

$db = Helpers\db();
$sql = 'SELECT o.*, oi.qty, oi.price, oi.platform_fee_applied, p.title 
        FROM orders o
        INNER JOIN order_items oi ON oi.order_id = o.id
        INNER JOIN products p ON p.id = oi.product_id
        WHERE oi.seller_id = :seller_id';
$params = ['seller_id' => $user['id']];
if ($statusFilter) {
    $sql .= ' AND o.status = :status';
    $params['status'] = $statusFilter;
}
$sql .= ' ORDER BY o.created_at DESC LIMIT 50';

$stmt = $db->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

$title = 'Orders';

require_once __DIR__ . '/../../app/views/layouts/header.php';
?>

<div class="dashboard">
    <?php $active = 'orders'; include __DIR__ . '/../../app/views/seller/sidebar.php'; ?>
    <section class="dashboard-content">
        <h1>Orders</h1>
        <form method="get" class="form-grid" style="max-width:400px;">
            <label>Status Filter
                <select name="status" onchange="this.form.submit()">
                    <option value="">All</option>
                    <?php foreach ($validStatuses as $status): ?>
                        <option value="<?= $status ?>" <?= $statusFilter === $status ? 'selected' : '' ?>><?= ucfirst($status) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
        </form>

        <?php if ($message = Helpers\flash('success')): ?>
            <div class="alert alert-info"><?= Helpers\sanitize($message) ?></div>
        <?php endif; ?>
        <?php if ($message = Helpers\flash('error')): ?>
            <div class="alert alert-warning"><?= Helpers\sanitize($message) ?></div>
        <?php endif; ?>

        <table class="table">
            <thead>
            <tr>
                <th>Order #</th>
                <th>Product</th>
                <th>Status</th>
                <th>Qty</th>
                <th>Total</th>
                <th>Tracking</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($orders as $order): ?>
                <tr>
                    <td><?= Helpers\sanitize($order['order_number']) ?></td>
                    <td><?= Helpers\sanitize($order['title']) ?></td>
                    <td><span class="badge"><?= Helpers\sanitize(ucfirst($order['status'])) ?></span></td>
                    <td><?= (int)$order['qty'] ?></td>
                    <td>₨<?= number_format($order['qty'] * $order['price'], 2) ?></td>
                    <td>
                        <?php if (!empty($order['tracking_number'])): ?>
                            <?= Helpers\sanitize($order['shipping_courier'] ?? '') ?> #<?= Helpers\sanitize($order['tracking_number']) ?>
                        <?php else: ?>
                            —
                        <?php endif; ?>
                    </td>
                    <td>
                        <form action="" method="post" class="form-grid">
                            <input type="hidden" name="csrf_token" value="<?= Helpers\csrf_token() ?>">
                            <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                            <label>Status
                                <select name="status">
                                    <?php foreach ($validStatuses as $status): ?>
                                        <option value="<?= $status ?>" <?= $order['status'] === $status ? 'selected' : '' ?>><?= ucfirst($status) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </label>
                            <label>Courier
                                <input type="text" name="shipping_courier" value="<?= Helpers\sanitize($order['shipping_courier'] ?? '') ?>">
                            </label>
                            <label>Tracking #
                                <input type="text" name="tracking_number" value="<?= Helpers\sanitize($order['tracking_number'] ?? '') ?>">
                            </label>
                            <button type="submit" class="btn btn-primary">Update</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>
</div>

<?php require_once __DIR__ . '/../../app/views/layouts/footer.php'; ?>
