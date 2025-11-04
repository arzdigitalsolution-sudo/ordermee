<?php

declare(strict_types=1);

require_once __DIR__ . '/../../app/bootstrap.php';

use ClickCart\Controllers\AdminController;
use ClickCart\Helpers;
use ClickCart\Models\Order;

$user = Helpers\auth_user();
if (!$user || $user['role'] !== 'admin') {
    Helpers\redirect(Helpers\app_url('auth/login.php'));
}

$adminController = new AdminController();
$ordersController = new Order();
$sellers = $adminController->listUsers();
$metrics = $adminController->ordersOverview();

if (Helpers\request_method() === 'POST') {
    if (Helpers\verify_csrf($_POST['csrf_token'] ?? '')) {
        if (!empty($_POST['reset_seller_id'])) {
            $adminController->resetPlatformFee((int)$_POST['reset_seller_id']);
            Helpers\flash('success', 'Platform fee reset for seller #' . (int)$_POST['reset_seller_id']);
        }
    }
    Helpers\redirect(Helpers\app_url('admin/index.php'));
}

$title = 'Admin Panel';

require_once __DIR__ . '/../../app/views/layouts/header.php';
?>

<section class="card">
    <h1>Admin Dashboard</h1>
    <?php if ($message = Helpers\flash('success')): ?>
        <div class="alert alert-info"><?= Helpers\sanitize($message) ?></div>
    <?php endif; ?>
    <div class="stats-grid">
        <div class="stat-card"><span class="label">Total Orders</span><span class="value"><?= $metrics['total_orders'] ?></span></div>
        <div class="stat-card"><span class="label">Pending</span><span class="value"><?= $metrics['pending_orders'] ?></span></div>
        <div class="stat-card"><span class="label">Processing</span><span class="value"><?= $metrics['processing_orders'] ?></span></div>
    </div>
</section>

<section class="card">
    <h2>Sellers</h2>
    <table class="table">
        <thead>
        <tr>
            <th>ID</th>
            <th>Brand</th>
            <th>Total Sales</th>
            <th>Platform Fee Paid</th>
            <th>Action</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($sellers as $seller): ?>
            <tr>
                <td><?= (int)$seller['id'] ?></td>
                <td><?= Helpers\sanitize($seller['brand_name'] ?? 'N/A') ?></td>
                <td><?= (int)$seller['total_sales'] ?></td>
                <td><?= ((int)$seller['platform_fee_paid']) ? 'Yes' : 'No' ?></td>
                <td>
                    <form action="" method="post">
                        <input type="hidden" name="csrf_token" value="<?= Helpers\csrf_token() ?>">
                        <input type="hidden" name="reset_seller_id" value="<?= $seller['id'] ?>">
                        <button class="btn btn-outline" type="submit">Reset Fee</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>

<?php require_once __DIR__ . '/../../app/views/layouts/footer.php'; ?>
