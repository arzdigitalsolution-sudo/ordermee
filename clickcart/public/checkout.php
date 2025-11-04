<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/bootstrap.php';

use ClickCart\Controllers\OrderController;
use ClickCart\Helpers;
use ClickCart\Helpers\Cart;
use ClickCart\Models\User;

Helpers\require_auth('buyer');

$user = Helpers\auth_user();
$cart = Cart\get_cart();

if (!$cart) {
    Helpers\flash('error', 'Your cart is empty.');
    Helpers\redirect(Helpers\app_url('cart.php'));
}

$userModel = new User();
$grouped = Cart\group_by_seller();
$platformFeePreview = [];

foreach (array_keys($grouped) as $sellerId) {
    $seller = $userModel->findById((int)$sellerId);
    if ($seller) {
        $platformFeePreview[] = [
            'seller' => $seller,
            'will_charge' => ($seller['total_sales'] ?? 0) >= 10 && (int)$seller['platform_fee_paid'] === 0,
        ];
    }
}

$title = 'Checkout';
$errors = [];

if (Helpers\request_method() === 'POST') {
    if (!Helpers\verify_csrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request.';
    }

    $address = trim($_POST['shipping_address'] ?? '');
    $phone = trim($_POST['shipping_phone'] ?? '');
    $paymentMethod = $_POST['payment_method'] ?? 'mock';

    if ($address === '' || $phone === '') {
        $errors[] = 'Shipping address and phone are required.';
    }

    if (!$errors) {
        $orderController = new OrderController();
        $result = $orderController->createOrder((int)$user['id'], [
            'shipping_address' => $address,
            'shipping_phone' => $phone,
            'payment_method' => $paymentMethod,
        ]);

        if ($result['success']) {
            Helpers\flash('success', 'Order created. Redirecting to payment...');
            Helpers\redirect($result['redirect_url']);
        } else {
            $errors[] = $result['message'] ?? 'Unable to create order.';
        }
    }
}

require_once __DIR__ . '/../app/views/layouts/header.php';
?>

<section class="checkout-grid">
    <div>
        <h1>Checkout</h1>

        <?php if ($errors): ?>
            <div class="alert alert-warning">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= Helpers\sanitize($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="" method="post" class="card">
            <input type="hidden" name="csrf_token" value="<?= Helpers\csrf_token() ?>">
            <label>Shipping Address
                <textarea name="shipping_address" required><?= Helpers\sanitize($_POST['shipping_address'] ?? ($user['default_address'] ?? '')) ?></textarea>
            </label>
            <label>Contact Phone
                <input type="text" name="shipping_phone" value="<?= Helpers\sanitize($_POST['shipping_phone'] ?? ($user['phone'] ?? '')) ?>" required>
            </label>

            <label>Payment Method</label>
            <div class="payment-options">
                <label><input type="radio" name="payment_method" value="jazzcash" <?= (($_POST['payment_method'] ?? '') === 'jazzcash') ? 'checked' : '' ?>> JazzCash</label>
                <label><input type="radio" name="payment_method" value="easypaisa" <?= (($_POST['payment_method'] ?? '') === 'easypaisa') ? 'checked' : '' ?>> EasyPaisa</label>
                <label><input type="radio" name="payment_method" value="nayapay" <?= (($_POST['payment_method'] ?? '') === 'nayapay') ? 'checked' : '' ?>> NayaPay</label>
                <label><input type="radio" name="payment_method" value="mock" <?= (($_POST['payment_method'] ?? 'mock') === 'mock') ? 'checked' : '' ?>> Cash on Delivery / Manual</label>
            </div>

            <p class="note">All payment options run in sandbox mode for testing. After confirming, you’ll be redirected to the selected gateway or mock confirmation page.</p>

            <button type="submit" class="btn btn-primary">Confirm & Pay</button>
        </form>
    </div>

    <aside class="card">
        <h2>Order Summary</h2>
        <ul class="summary-list">
            <?php foreach ($cart as $item): ?>
                <li>
                    <div>
                        <strong><?= Helpers\sanitize($item['title']) ?></strong>
                        <small>Qty: <?= (int)$item['qty'] ?></small>
                    </div>
                    <span>₨<?= number_format($item['price'] * $item['qty'], 2) ?></span>
                </li>
            <?php endforeach; ?>
        </ul>

        <p class="subtotal">Subtotal: ₨<?= number_format(array_reduce($cart, fn ($carry, $item) => $carry + $item['price'] * $item['qty'], 0), 2) ?></p>

        <div class="platform-fee">
            <h3>Platform Fee Preview</h3>
            <?php foreach ($platformFeePreview as $preview): ?>
                <p>
                    <?= Helpers\sanitize($preview['seller']['brand_name'] ?? $preview['seller']['name']) ?>:
                    <?php if ($preview['will_charge']): ?>
                        <span class="badge badge-warning">₨100 will be added</span>
                    <?php else: ?>
                        <span class="badge badge-success">Still free</span>
                    <?php endif; ?>
                </p>
            <?php endforeach; ?>
        </div>

        <p class="note">Platform fee is charged per seller only once after 10 completed sales.</p>
    </aside>
</section>

<?php require_once __DIR__ . '/../app/views/layouts/footer.php'; ?>
