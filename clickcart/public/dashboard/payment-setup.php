<?php

declare(strict_types=1);

require_once __DIR__ . '/../../app/bootstrap.php';

use ClickCart\Helpers;

Helpers\require_auth('seller');
$user = Helpers\auth_user();

$settingsDir = __DIR__ . '/../../storage/seller_settings';
if (!is_dir($settingsDir)) {
    mkdir($settingsDir, 0775, true);
}
$settingsFile = $settingsDir . '/' . $user['id'] . '-payments.json';
$settings = [];
if (file_exists($settingsFile)) {
    $settings = json_decode((string)file_get_contents($settingsFile), true) ?: [];
}

if (Helpers\request_method() === 'POST') {
    if (Helpers\verify_csrf($_POST['csrf_token'] ?? '')) {
        $settings = [
            'jazzcash_merchant_id' => trim($_POST['jazzcash_merchant_id'] ?? ''),
            'jazzcash_password' => trim($_POST['jazzcash_password'] ?? ''),
            'easypaisa_key' => trim($_POST['easypaisa_key'] ?? ''),
            'nayapay_key' => trim($_POST['nayapay_key'] ?? ''),
            'bank_account' => trim($_POST['bank_account'] ?? ''),
        ];
        file_put_contents($settingsFile, json_encode($settings, JSON_PRETTY_PRINT));
        Helpers\flash('success', 'Payment settings saved.');
    }
    Helpers\redirect(Helpers\app_url('dashboard/payment-setup.php'));
}

$title = 'Payment Setup';

require_once __DIR__ . '/../../app/views/layouts/header.php';
?>

<div class="dashboard">
    <?php $active = 'payment'; include __DIR__ . '/../../app/views/seller/sidebar.php'; ?>
    <section class="dashboard-content">
        <h1>Payment Configuration</h1>
        <?php if ($message = Helpers\flash('success')): ?>
            <div class="alert alert-info"><?= Helpers\sanitize($message) ?></div>
        <?php endif; ?>
        <form action="" method="post" class="card">
            <input type="hidden" name="csrf_token" value="<?= Helpers\csrf_token() ?>">
            <label>JazzCash Merchant ID
                <input type="text" name="jazzcash_merchant_id" value="<?= Helpers\sanitize($settings['jazzcash_merchant_id'] ?? '') ?>">
            </label>
            <label>JazzCash Password
                <input type="text" name="jazzcash_password" value="<?= Helpers\sanitize($settings['jazzcash_password'] ?? '') ?>">
            </label>
            <label>EasyPaisa API Key
                <input type="text" name="easypaisa_key" value="<?= Helpers\sanitize($settings['easypaisa_key'] ?? '') ?>">
            </label>
            <label>NayaPay API Key
                <input type="text" name="nayapay_key" value="<?= Helpers\sanitize($settings['nayapay_key'] ?? '') ?>">
            </label>
            <label>Bank Account / COD Instructions
                <textarea name="bank_account" rows="3"><?= Helpers\sanitize($settings['bank_account'] ?? '') ?></textarea>
            </label>
            <button type="submit" class="btn btn-primary">Save Payment Details</button>
        </form>
        <p class="note">Payment credentials are stored securely on the server. Use sandbox credentials for testing in development.</p>
    </section>
</div>

<?php require_once __DIR__ . '/../../app/views/layouts/footer.php'; ?>
