<?php

declare(strict_types=1);

require_once __DIR__ . '/../../app/bootstrap.php';

use ClickCart\Helpers;
use ClickCart\Models\Product;
use ClickCart\Models\ProductImage;

Helpers\require_auth('seller');

$user = Helpers\auth_user();
$productModel = new Product();
$imageModel = new ProductImage();

if (Helpers\request_method() === 'POST') {
    if (!Helpers\verify_csrf($_POST['csrf_token'] ?? '')) {
        Helpers\flash('error', 'Invalid request.');
    } else {
        $action = $_POST['action'] ?? '';
        $productId = (int)($_POST['product_id'] ?? 0);
        if ($action === 'delete' && $productId) {
            $productModel->delete($productId, (int)$user['id']);
            $imageModel->deleteByProduct($productId);
            Helpers\flash('success', 'Product deleted.');
        }
    }
    Helpers\redirect(Helpers\app_url('dashboard/products.php'));
}

$products = $productModel->listBySeller((int)$user['id']);
$title = 'Manage Products';

require_once __DIR__ . '/../../app/views/layouts/header.php';
?>

<div class="dashboard">
    <?php $active = 'products'; include __DIR__ . '/../../app/views/seller/sidebar.php'; ?>
    <section class="dashboard-content">
        <h1>Products</h1>
        <?php if ($message = Helpers\flash('success')): ?>
            <div class="alert alert-info"><?= Helpers\sanitize($message) ?></div>
        <?php endif; ?>
        <table class="table">
            <thead>
            <tr>
                <th>Title</th>
                <th>SKU</th>
                <th>Price</th>
                <th>Qty</th>
                <th>Status</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($products as $product): ?>
                <tr>
                    <td><?= Helpers\sanitize($product['title']) ?></td>
                    <td><?= Helpers\sanitize($product['sku'] ?? '-') ?></td>
                    <td>₨<?= number_format($product['price'], 2) ?></td>
                    <td><?= (int)$product['quantity'] ?></td>
                    <td><?= Helpers\sanitize(ucfirst($product['status'])) ?></td>
                    <td>
                        <a href="<?= Helpers\app_url('dashboard/edit-product.php?id=' . $product['id']) ?>" class="btn btn-outline">Edit</a>
                        <form action="" method="post" style="display:inline">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                            <input type="hidden" name="csrf_token" value="<?= Helpers\csrf_token() ?>">
                            <button type="submit" class="btn btn-outline" onclick="return confirm('Delete this product?')">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>
</div>

<?php require_once __DIR__ . '/../../app/views/layouts/footer.php'; ?>
