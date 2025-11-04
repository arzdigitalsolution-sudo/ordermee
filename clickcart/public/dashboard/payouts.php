<?php

declare(strict_types=1);

require_once __DIR__ . '/../../app/bootstrap.php';

use ClickCart\Helpers;

Helpers\require_auth('seller');
$user = Helpers\auth_user();
$db = Helpers\db();

try {
    $stmt = $db->prepare('SELECT * FROM payouts WHERE seller_id = :seller_id ORDER BY id DESC');
    $stmt->execute(['seller_id' => $user['id']]);
    $payouts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable) {
    $payouts = [];
}

$title = 'Payouts';

require_once __DIR__ . '/../../app/views/layouts/header.php';
?>

<div class="dashboard">
    <?php $active = 'payouts'; include __DIR__ . '/../../app/views/seller/sidebar.php'; ?>
    <section class="dashboard-content">
        <h1>Payouts & Earnings</h1>
        <p class="note">Payouts will appear after the admin schedules disbursements.</p>
        <table class="table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Amount</th>
                <th>Status</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($payouts as $payout): ?>
                <tr>
                    <td><?= (int)$payout['id'] ?></td>
                    <td>₨<?= number_format($payout['amount'], 2) ?></td>
                    <td><?= Helpers\sanitize(ucfirst($payout['status'])) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$payouts): ?>
                <tr><td colspan="3">No payout records yet.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </section>
</div>

<?php require_once __DIR__ . '/../../app/views/layouts/footer.php'; ?>
