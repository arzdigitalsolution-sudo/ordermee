<?php

declare(strict_types=1);

require_once __DIR__ . '/../../app/bootstrap.php';

$title = 'Shipping Policy';

require_once __DIR__ . '/../../app/views/layouts/header.php';
?>

<article class="card">
    <h1>Shipping Policy</h1>
    <p>Sellers fulfill orders using approved couriers including TCS and Leopards. Typical delivery windows range from 2–5 business days within Pakistan. Tracking numbers are shared via email and available in your order history.</p>
    <p>Shipping costs are calculated per seller at checkout and may vary by weight, dimensions, and destination.</p>
</article>

<?php require_once __DIR__ . '/../../app/views/layouts/footer.php'; ?>
