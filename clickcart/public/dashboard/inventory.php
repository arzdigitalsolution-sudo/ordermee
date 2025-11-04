<?php

declare(strict_types=1);

require_once __DIR__ . '/../../app/bootstrap.php';

use ClickCart\Helpers;
use ClickCart\Models\Product;

Helpers\require_auth('seller');
$user = Helpers\auth_user();
$productModel = new Product();

if (Helpers\request_method() === 'POST') {
    if (Helpers\verify_csrf($_POST['csrf_token'] ?? '')) {
        foreach ($_POST['qty'] ?? [] as $productId => $qty) {
            $productModel->update((int)$productId, (int)$user['id'], ['quantity' => (int)$qty]);
        }
        Helpers\flash('success', 'Inventory updated.');
    }
    Helpers\redirect(Helpers\app_url('dashboard/inventory.php'));
}

$products = $productModel->listBySeller((int)$user['id']);
$title = 'Inventory';

require_once __DIR__ . '/../../app/views/layouts/header.php';
?>

<div class="dashboard">
    <?php $active = 'inventory'; include __DIR__ . '/../../app/views/seller/sidebar.php'; ?>
    <section class="dashboard-content">
        <h1>Inventory Manager</h1>
        <?php if ($message = Helpers\flash('success')): ?>
            <div class="alert alert-info"><?= Helpers\sanitize($message) ?></div>
        <?php endif; ?>
        <form action="" method="post">
            <input type="hidden" name="csrf_token" value="<?= Helpers\csrf_token() ?>">
            <table class="table">
                <thead>
                <tr>
                    <th>Product</th>
                    <th>Quantity</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($products as $product): ?>
                    <tr>
                        <td><?= Helpers\sanitize($product['title']) ?></td>
                        <td><input type="number" name="qty[<?= $product['id'] ?>]" value="<?= (int)$product['quantity'] ?>"></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <button type="submit" class="btn btn-primary">Update Inventory</button>
        </form>
    </section>
</div>

<?php require_once __DIR__ . '/../../app/views/layouts/footer.php'; ?>
