<?php

use function ClickCart\Helpers\app_url;

$active = $active ?? 'overview';

?>
<aside class="sidebar">
    <a href="<?= app_url('dashboard/index.php') ?>" class="<?= $active === 'overview' ? 'active' : '' ?>">Overview</a>
    <a href="<?= app_url('dashboard/products.php') ?>" class="<?= $active === 'products' ? 'active' : '' ?>">Products</a>
    <a href="<?= app_url('dashboard/add-product.php') ?>" class="<?= $active === 'add-product' ? 'active' : '' ?>">Add Product</a>
    <a href="<?= app_url('dashboard/orders.php?status=new') ?>" class="<?= $active === 'orders' ? 'active' : '' ?>">Orders</a>
    <a href="<?= app_url('dashboard/inventory.php') ?>" class="<?= $active === 'inventory' ? 'active' : '' ?>">Inventory</a>
    <a href="<?= app_url('dashboard/sales.php') ?>" class="<?= $active === 'sales' ? 'active' : '' ?>">Sales & Reports</a>
    <a href="<?= app_url('dashboard/categories.php') ?>" class="<?= $active === 'categories' ? 'active' : '' ?>">Categories</a>
    <a href="<?= app_url('dashboard/shipping.php') ?>" class="<?= $active === 'shipping' ? 'active' : '' ?>">Shipping Integration</a>
    <a href="<?= app_url('dashboard/payment-setup.php') ?>" class="<?= $active === 'payment' ? 'active' : '' ?>">Payment Setup</a>
    <a href="<?= app_url('dashboard/settings.php') ?>" class="<?= $active === 'settings' ? 'active' : '' ?>">Store Settings</a>
    <a href="<?= app_url('dashboard/payouts.php') ?>" class="<?= $active === 'payouts' ? 'active' : '' ?>">Payouts</a>
    <a href="<?= app_url('dashboard/notifications.php') ?>" class="<?= $active === 'notifications' ? 'active' : '' ?>">Notifications</a>
</aside>
