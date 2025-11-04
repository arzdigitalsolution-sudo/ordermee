<?php

declare(strict_types=1);

require_once __DIR__ . '/../../app/bootstrap.php';

$title = 'Seller Agreement';

require_once __DIR__ . '/../../app/views/layouts/header.php';
?>

<article class="card">
    <h1>Seller Agreement</h1>
    <p>By listing products on ClickCart.pk you certify that you own or have rights to sell the products, will honor orders promptly, and will comply with consumer protection laws of Pakistan. You agree to: </p>
    <ul>
        <li>Maintain accurate product descriptions, pricing, and stock levels.</li>
        <li>Ship orders within the promised timelines.</li>
        <li>Respond to buyer inquiries within two business days.</li>
        <li>Acknowledge the one-time ₨100 platform fee once 10 completed sales are reached.</li>
    </ul>
</article>

<?php require_once __DIR__ . '/../../app/views/layouts/footer.php'; ?>
