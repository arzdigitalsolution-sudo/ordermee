<?php

declare(strict_types=1);

require_once __DIR__ . '/../../app/bootstrap.php';

use ClickCart\Helpers;

Helpers\require_auth('seller');
$title = 'Notifications';

require_once __DIR__ . '/../../app/views/layouts/header.php';
?>

<div class="dashboard">
    <?php $active = 'notifications'; include __DIR__ . '/../../app/views/seller/sidebar.php'; ?>
    <section class="dashboard-content">
        <h1>Notifications</h1>
        <p class="note">Real-time alerts for orders appear here. Use the dashboard overview to see the latest events.</p>
        <div class="card">
            <p>No saved notifications yet.</p>
        </div>
    </section>
</div>

<?php require_once __DIR__ . '/../../app/views/layouts/footer.php'; ?>
