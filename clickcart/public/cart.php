<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/bootstrap.php';

use ClickCart\Helpers;
use ClickCart\Helpers\Cart;
use ClickCart\Models\Product;
use ClickCart\Models\ProductImage;

$productModel = new Product();
$imageModel = new ProductImage();

if (Helpers\request_method() === 'POST') {
    if (!Helpers\verify_csrf($_POST['csrf_token'] ?? '')) {
        Helpers\flash('error', 'Invalid request.');
        Helpers\redirect(Helpers\app_url('cart.php'));
    }

    $action = $_POST['action'] ?? '';
    $productId = (int)($_POST['product_id'] ?? 0);

    if ($action === 'add' && $productId) {
        $product = $productModel->find($productId);
        if ($product) {
            $images = $imageModel->getByProduct($productId);
            Cart\add_to_cart(
                $productId,
                (int)$product['seller_id'],
                $product['title'],
                (float)$product['sale_price'] ?: (float)$product['price'],
                max(1, (int)($_POST['qty'] ?? 1)),
                $images[0]['image_url'] ?? null
            );
            Helpers\flash('success', 'Product added to cart.');
        }
    }

    if ($action === 'update') {
        foreach ($_POST['qty'] ?? [] as $id => $qty) {
            Cart\update_qty((int)$id, max(1, (int)$qty));
        }
        if (!empty($_POST['remove'])) {
            Cart\remove_from_cart((int)$_POST['remove']);
            Helpers\flash('success', 'Item removed from cart.');
        } else {
            Helpers\flash('success', 'Cart updated.');
        }
    }

    Helpers\redirect(Helpers\app_url('cart.php'));
}

$cart = Cart\get_cart();
$totals = Cart\cart_totals();
$title = 'Your Cart';

require_once __DIR__ . '/../app/views/layouts/header.php';
?>

<section class="card">
    <h1>Your Cart</h1>

    <?php if ($message = Helpers\flash('success')): ?>
        <div class="alert alert-info"><?= Helpers\sanitize($message) ?></div>
    <?php endif; ?>
    <?php if ($message = Helpers\flash('error')): ?>
        <div class="alert alert-warning"><?= Helpers\sanitize($message) ?></div>
    <?php endif; ?>

    <?php if (!$cart): ?>
        <p>Your cart is empty. <a href="<?= Helpers\app_url('index.php') ?>">Continue shopping</a>.</p>
    <?php else: ?>
        <form action="<?= Helpers\app_url('cart.php') ?>" method="post">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="csrf_token" value="<?= Helpers\csrf_token() ?>">
            <table class="table">
                <thead>
                <tr>
                    <th>Item</th>
                    <th>Price</th>
                    <th>Qty</th>
                    <th>Total</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($cart as $item): ?>
                    <tr>
                        <td>
                            <div class="cart-item">
                                <img src="<?= $item['image'] ? Helpers\app_url(ltrim($item['image'], '/')) : Helpers\app_url('assets/img/placeholder.svg') ?>" alt="<?= Helpers\sanitize($item['title']) ?>">
                                <div>
                                    <strong><?= Helpers\sanitize($item['title']) ?></strong><br>
                                    <small>Seller ID: <?= (int)$item['seller_id'] ?></small>
                                </div>
                            </div>
                        </td>
                        <td>₨<?= number_format($item['price'], 2) ?></td>
                        <td>
                            <input type="number" name="qty[<?= $item['product_id'] ?>]" value="<?= (int)$item['qty'] ?>" min="1">
                        </td>
                        <td>₨<?= number_format($item['price'] * $item['qty'], 2) ?></td>
                        <td>
                            <button type="submit" name="remove" value="<?= $item['product_id'] ?>" class="btn btn-outline">Remove</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <div class="cart-actions">
                <button type="submit" class="btn btn-outline">Update Cart</button>
                <a href="<?= Helpers\app_url('checkout.php') ?>" class="btn btn-primary">Proceed to Checkout</a>
            </div>
        </form>

        <div class="cart-summary card">
            <h3>Order Summary</h3>
            <p>Subtotal: ₨<?= number_format($totals['subtotal'], 2) ?></p>
            <p class="note">Platform fees (if any) will be calculated per seller at checkout.</p>
        </div>
    <?php endif; ?>
</section>

<?php require_once __DIR__ . '/../app/views/layouts/footer.php'; ?>
