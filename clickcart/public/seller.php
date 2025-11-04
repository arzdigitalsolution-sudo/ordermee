<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/bootstrap.php';

use ClickCart\Models\User;
use ClickCart\Models\Product;
use ClickCart\Models\ProductImage;
use ClickCart\Helpers;

$userModel = new User();
$productModel = new Product();
$imageModel = new ProductImage();

$sellerId = (int)($_GET['id'] ?? 0);
$seller = $userModel->findById($sellerId);

if (!$seller || $seller['role'] !== 'seller') {
    http_response_code(404);
    echo 'Seller not found';
    exit;
}

$title = Helpers\sanitize($seller['brand_name'] ?? $seller['name']);
$products = $productModel->listPublished(24, 0, $sellerId);

require_once __DIR__ . '/../app/views/layouts/header.php';
?>

<section class="seller-hero card">
    <div class="seller-hero-grid">
        <img src="<?= $seller['brand_logo'] ? Helpers\app_url(ltrim($seller['brand_logo'], '/')) : Helpers\app_url('assets/img/avatar.svg') ?>" alt="<?= Helpers\sanitize($title) ?>">
        <div>
            <h1><?= Helpers\sanitize($title) ?></h1>
            <p><?= nl2br(Helpers\sanitize($seller['bio'] ?? 'Local seller on ClickCart.pk')) ?></p>
            <div class="seller-meta">
                <span>Total sales: <?= (int)$seller['total_sales'] ?></span>
                <span>Contact: <?= Helpers\sanitize($seller['phone'] ?? 'N/A') ?></span>
            </div>
            <?php if (!(int)$seller['platform_fee_paid'] && ($seller['total_sales'] ?? 0) >= 10): ?>
                <div class="alert alert-info">This seller’s next sale will include the one-time ₨100 platform fee.</div>
            <?php endif; ?>
        </div>
    </div>
</section>

<section>
    <h2>Products</h2>
    <div class="product-grid">
        <?php foreach ($products as $product): ?>
            <?php $images = $imageModel->getByProduct((int)$product['id']); ?>
            <div class="product-card">
                <a href="<?= Helpers\app_url('product.php?id=' . $product['id']) ?>">
                    <img src="<?= $images[0]['image_url'] ?? Helpers\app_url('assets/img/placeholder.svg') ?>" alt="<?= Helpers\sanitize($product['title']) ?>">
                </a>
                <h3><?= Helpers\sanitize($product['title']) ?></h3>
                <div class="price">₨<?= number_format((float)$product['sale_price'] ?: (float)$product['price'], 2) ?></div>
                <form action="<?= Helpers\app_url('cart.php') ?>" method="post">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                    <input type="hidden" name="csrf_token" value="<?= Helpers\csrf_token() ?>">
                    <button type="submit" class="btn btn-primary">Add to Cart</button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<?php require_once __DIR__ . '/../app/views/layouts/footer.php'; ?>
