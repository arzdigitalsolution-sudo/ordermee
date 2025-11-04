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
$settingsFile = $settingsDir . '/' . $user['id'] . '.json';
$settings = [];
if (file_exists($settingsFile)) {
    $settings = json_decode((string)file_get_contents($settingsFile), true) ?: [];
}

if (Helpers\request_method() === 'POST') {
    if (Helpers\verify_csrf($_POST['csrf_token'] ?? '')) {
        $settings['tcs'] = [
            'account_id' => trim($_POST['tcs_account_id'] ?? ''),
            'api_key' => trim($_POST['tcs_api_key'] ?? ''),
        ];
        $settings['leopards'] = [
            'account_id' => trim($_POST['leopards_account_id'] ?? ''),
            'api_key' => trim($_POST['leopards_api_key'] ?? ''),
        ];
        file_put_contents($settingsFile, json_encode($settings, JSON_PRETTY_PRINT));
        Helpers\flash('success', 'Shipping preferences saved.');
    }
    Helpers\redirect(Helpers\app_url('dashboard/shipping.php'));
}

$title = 'Shipping Integration';

require_once __DIR__ . '/../../app/views/layouts/header.php';
?>

<div class="dashboard">
    <?php $active = 'shipping'; include __DIR__ . '/../../app/views/seller/sidebar.php'; ?>
    <section class="dashboard-content">
        <h1>Shipping Integrations</h1>
        <?php if ($message = Helpers\flash('success')): ?>
            <div class="alert alert-info"><?= Helpers\sanitize($message) ?></div>
        <?php endif; ?>

        <form action="" method="post" class="card">
            <input type="hidden" name="csrf_token" value="<?= Helpers\csrf_token() ?>">
            <h2>TCS</h2>
            <label>Account ID
                <input type="text" name="tcs_account_id" value="<?= Helpers\sanitize($settings['tcs']['account_id'] ?? '') ?>">
            </label>
            <label>API Key
                <input type="text" name="tcs_api_key" value="<?= Helpers\sanitize($settings['tcs']['api_key'] ?? '') ?>">
            </label>
            <h2>Leopards</h2>
            <label>Account ID
                <input type="text" name="leopards_account_id" value="<?= Helpers\sanitize($settings['leopards']['account_id'] ?? '') ?>">
            </label>
            <label>API Key
                <input type="text" name="leopards_api_key" value="<?= Helpers\sanitize($settings['leopards']['api_key'] ?? '') ?>">
            </label>
            <button type="submit" class="btn btn-primary">Save Settings</button>
        </form>
        <p class="note">Provide courier credentials to enable automatic tracking. Without credentials, enter tracking numbers manually in the Orders tab.</p>
    </section>
</div>

<?php require_once __DIR__ . '/../../app/views/layouts/footer.php'; ?>
