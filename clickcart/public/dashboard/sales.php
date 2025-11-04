<?php

declare(strict_types=1);

require_once __DIR__ . '/../../app/bootstrap.php';

use ClickCart\Helpers;

Helpers\require_auth('seller');
$user = Helpers\auth_user();
$db = Helpers\db();

if (isset($_GET['export']) && Helpers\verify_csrf($_GET['token'] ?? '')) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="sales-report.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Order #', 'Total', 'Status', 'Created At']);
    $stmt = $db->prepare('SELECT DISTINCT o.order_number, o.total_amount, o.status, o.created_at
                          FROM orders o
                          INNER JOIN order_items oi ON oi.order_id = o.id
                          WHERE oi.seller_id = :seller_id ORDER BY o.created_at DESC');
    $stmt->execute(['seller_id' => $user['id']]);
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        fputcsv($out, [$row['order_number'], $row['total_amount'], $row['status'], $row['created_at']]);
    }
    exit;
}

function periodTotal(PDO $db, int $sellerId, string $interval): float
{
    $sql = 'SELECT SUM(o.total_amount) FROM orders o
            INNER JOIN order_items oi ON oi.order_id = o.id
            WHERE oi.seller_id = :seller_id AND o.created_at >= DATE_SUB(NOW(), INTERVAL ' . $interval . ') AND o.payment_status = "paid"';
    $stmt = $db->prepare($sql);
    $stmt->execute(['seller_id' => $sellerId]);
    return (float)$stmt->fetchColumn();
}

$totals = [
    'today' => periodTotal($db, (int)$user['id'], '1 DAY'),
    'week' => periodTotal($db, (int)$user['id'], '7 DAY'),
    'month' => periodTotal($db, (int)$user['id'], '30 DAY'),
];

$title = 'Sales & Reports';

require_once __DIR__ . '/../../app/views/layouts/header.php';
?>

<div class="dashboard">
    <?php $active = 'sales'; include __DIR__ . '/../../app/views/seller/sidebar.php'; ?>
    <section class="dashboard-content">
        <h1>Sales Overview</h1>
        <div class="stats-grid">
            <div class="stat-card"><span class="label">Today</span><span class="value">₨<?= number_format($totals['today'], 2) ?></span></div>
            <div class="stat-card"><span class="label">Last 7 days</span><span class="value">₨<?= number_format($totals['week'], 2) ?></span></div>
            <div class="stat-card"><span class="label">Last 30 days</span><span class="value">₨<?= number_format($totals['month'], 2) ?></span></div>
        </div>

        <a href="?export=1&token=<?= Helpers\csrf_token() ?>" class="btn btn-outline" style="max-width:200px;">Export CSV</a>
    </section>
</div>

<?php require_once __DIR__ . '/../../app/views/layouts/footer.php'; ?>
