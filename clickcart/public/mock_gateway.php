<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/bootstrap.php';

use ClickCart\Helpers;

$provider = $_GET['provider'] ?? 'mock';
$orderNumber = $_GET['order'] ?? null;

if (!$orderNumber) {
    echo 'Missing order number';
    exit;
}

$title = 'Mock Payment Gateway';

require_once __DIR__ . '/../app/views/layouts/header.php';
?>

<section class="card" style="max-width:600px;margin:0 auto;">
    <h1><?= strtoupper($provider) ?> Sandbox</h1>
    <p>Order #: <?= Helpers\sanitize($orderNumber) ?></p>
    <p>This is a simulated payment screen. Click the button below to trigger a success webhook.</p>
    <a href="<?= Helpers\app_url('webhook/mock_return.php?order=' . urlencode($orderNumber)) ?>" class="btn btn-primary">Simulate Success</a>
</section>

<?php require_once __DIR__ . '/../app/views/layouts/footer.php'; ?>
