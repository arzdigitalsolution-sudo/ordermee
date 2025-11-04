<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/bootstrap.php';

use ClickCart\Models\Product;
use ClickCart\Models\ProductImage;
use ClickCart\Helpers;

$productModel = new Product();
$imageModel = new ProductImage();

$productId = (int)($_GET['id'] ?? 0);
$product = $productModel->find($productId);

if (!$product) {
    http_response_code(404);
    echo 'Product not found';
    exit;
}

$title = Helpers\sanitize($product['title']);
$images = $imageModel->getByProduct($productId);

require_once __DIR__ . '/../app/views/layouts/header.php';
?>

<article class="product-detail card">
    <div class="product-detail-grid">
        <div class="product-images">
            <?php if ($images): ?>
                <img src="<?= Helpers\app_url(ltrim($images[0]['image_url'], '/')) ?>" alt="<?= Helpers\sanitize($product['title']) ?>" class="primary-image">
                <div class="thumbs">
                    <?php foreach ($images as $image): ?>
                        <img src="<?= Helpers\app_url(ltrim($image['image_url'], '/')) ?>" alt="Thumbnail">
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <img src="<?= Helpers\app_url('assets/img/placeholder.svg') ?>" alt="Placeholder" class="primary-image">
            <?php endif; ?>
        </div>
        <div class="product-info">
            <h1><?= Helpers\sanitize($product['title']) ?></h1>
            <p class="price">
                <?php if (!empty($product['sale_price'])): ?>
                    <span class="sale">₨<?= number_format((float)$product['sale_price'], 2) ?></span>
                    <span class="strike">₨<?= number_format((float)$product['price'], 2) ?></span>
                <?php else: ?>
                    ₨<?= number_format((float)$product['price'], 2) ?>
                <?php endif; ?>
            </p>
            <p><?= nl2br(Helpers\sanitize($product['description'] ?? '')) ?></p>

            <div class="seller-block">
                <img src="<?= $product['brand_logo'] ? Helpers\app_url(ltrim($product['brand_logo'], '/')) : Helpers\app_url('assets/img/avatar.svg') ?>" alt="Seller logo">
                <div>
                    <h4><?= Helpers\sanitize($product['brand_name'] ?? 'Seller') ?></h4>
                    <p><?= Helpers\sanitize($product['bio'] ?? 'Verified marketplace seller') ?></p>
                    <a href="<?= Helpers\app_url('seller.php?id=' . $product['seller_id']) ?>" class="btn btn-outline">Visit Store</a>
                </div>
            </div>

            <form action="<?= Helpers\app_url('cart.php') ?>" method="post" class="add-to-cart-form">
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                <input type="hidden" name="csrf_token" value="<?= Helpers\csrf_token() ?>">
                <label for="qty">Quantity</label>
                <input type="number" name="qty" id="qty" value="1" min="1">
                <button type="submit" class="btn btn-primary">Add to cart</button>
            </form>

            <div class="platform-note alert alert-info">
                First 10 sales for each seller are free. If this seller has already completed 10 sales, a one-time ₨100 platform fee will appear at checkout.
            </div>
        </div>
    </div>
</article>

<?php require_once __DIR__ . '/../app/views/layouts/footer.php'; ?>
