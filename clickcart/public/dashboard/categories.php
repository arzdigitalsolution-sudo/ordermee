<?php

declare(strict_types=1);

require_once __DIR__ . '/../../app/bootstrap.php';

use ClickCart\Helpers;
use ClickCart\Models\Category;

Helpers\require_auth('seller');
$categoryModel = new Category();

if (Helpers\request_method() === 'POST') {
    if (Helpers\verify_csrf($_POST['csrf_token'] ?? '')) {
        if (!empty($_POST['name'])) {
            $categoryModel->create(trim($_POST['name']));
            Helpers\flash('success', 'Category added.');
        }
    }
    Helpers\redirect(Helpers\app_url('dashboard/categories.php'));
}

$categories = $categoryModel->all();
$title = 'Categories';

require_once __DIR__ . '/../../app/views/layouts/header.php';
?>

<div class="dashboard">
    <?php $active = 'categories'; include __DIR__ . '/../../app/views/seller/sidebar.php'; ?>
    <section class="dashboard-content">
        <h1>Categories</h1>
        <?php if ($message = Helpers\flash('success')): ?>
            <div class="alert alert-info"><?= Helpers\sanitize($message) ?></div>
        <?php endif; ?>

        <form action="" method="post" class="card" style="max-width:400px;">
            <input type="hidden" name="csrf_token" value="<?= Helpers\csrf_token() ?>">
            <label>Category name
                <input type="text" name="name" required>
            </label>
            <button type="submit" class="btn btn-primary">Add Category</button>
        </form>

        <table class="table">
            <thead>
            <tr>
                <th>Name</th>
                <th>Slug</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($categories as $category): ?>
                <tr>
                    <td><?= Helpers\sanitize($category['name']) ?></td>
                    <td><?= Helpers\sanitize($category['slug']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>
</div>

<?php require_once __DIR__ . '/../../app/views/layouts/footer.php'; ?>
