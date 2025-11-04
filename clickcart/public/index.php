<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/bootstrap.php';

use ClickCart\Models\Product;
use ClickCart\Models\ProductImage;
use ClickCart\Helpers;

$title = 'ClickCart.pk — Marketplace';
$productModel = new Product();
$imageModel = new ProductImage();

$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 12;
$offset = ($page - 1) * $perPage;

$products = $productModel->listPublished($perPage, $offset);
$totalProducts = $productModel->countPublished();
$pagination = Helpers\paginate($totalProducts, $perPage, $page);

require_once __DIR__ . '/../app/views/layouts/header.php';
?>

<section>
    <div class="hero">
        <h1>Discover Pakistani sellers in one unified marketplace.</h1>
        <p>Shop authentic products from local brands. Sellers get their first 10 sales free — ₨100 platform fee applies on the next sale.</p>
        <a href="<?= Helpers\app_url('auth/register.php?type=seller') ?>" class="btn btn-primary">Become a Seller</a>
    </div>

    <div class="product-grid">
        <?php foreach ($products as $product): ?>
            <?php $images = $imageModel->getByProduct((int)$product['id']); ?>
            <div class="product-card">
                <a href="<?= Helpers\app_url('product.php?id=' . $product['id']) ?>">
                    <img src="<?= $images[0]['image_url'] ?? Helpers\app_url('assets/img/placeholder.svg') ?>" alt="<?= Helpers\sanitize($product['title']) ?>">
                </a>
                <h3><?= Helpers\sanitize($product['title']) ?></h3>
                <div class="price">₨<?= number_format((float)$product['sale_price'] ?: (float)$product['price'], 2) ?></div>
                <div class="seller-mini-profile">
                    <img src="<?= $product['brand_logo'] ? Helpers\app_url(ltrim($product['brand_logo'], '/')) : Helpers\app_url('assets/img/avatar.svg') ?>" alt="<?= Helpers\sanitize($product['brand_name'] ?? 'Seller') ?>">
                    <div>
                        <p class="seller-name">Sold by</p>
                        <a href="<?= Helpers\app_url('seller.php?id=' . $product['seller_id']) ?>"><?= Helpers\sanitize($product['brand_name'] ?? 'Seller') ?></a>
                    </div>
                </div>
                <form action="<?= Helpers\app_url('cart.php') ?>" method="post">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                    <input type="hidden" name="csrf_token" value="<?= Helpers\csrf_token() ?>">
                    <button type="submit" class="btn btn-primary">Add to Cart</button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if ($pagination['pages'] > 1): ?>
        <div class="pagination">
            <?php if ($pagination['has_prev']): ?>
                <a href="?page=<?= $pagination['current_page'] - 1 ?>">Previous</a>
            <?php endif; ?>
            <span>Page <?= $pagination['current_page'] ?> of <?= $pagination['pages'] ?></span>
            <?php if ($pagination['has_next']): ?>
                <a href="?page=<?= $pagination['current_page'] + 1 ?>">Next</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</section>

<?php require_once __DIR__ . '/../app/views/layouts/footer.php'; ?>
