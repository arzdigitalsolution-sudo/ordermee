<?php

declare(strict_types=1);

require_once __DIR__ . '/../../app/bootstrap.php';

use ClickCart\Controllers\ProductController;
use ClickCart\Helpers;
use ClickCart\Models\Category;

Helpers\require_auth('seller');
$user = Helpers\auth_user();
$controller = new ProductController();
$categoryModel = new Category();
$categories = $categoryModel->all();
$errors = [];
$success = false;

if (Helpers\request_method() === 'POST') {
    if (!Helpers\verify_csrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request token.';
    } else {
        $data = [
            'title' => trim($_POST['title'] ?? ''),
            'sku' => trim($_POST['sku'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'price' => (float)($_POST['price'] ?? 0),
            'sale_price' => $_POST['sale_price'] !== '' ? (float)$_POST['sale_price'] : null,
            'quantity' => (int)($_POST['quantity'] ?? 0),
            'category_id' => $_POST['category_id'] ? (int)$_POST['category_id'] : null,
            'weight' => $_POST['weight'] ? (float)$_POST['weight'] : null,
            'dimensions' => trim($_POST['dimensions'] ?? ''),
            'status' => $_POST['status'] ?? 'published',
        ];

        $files = array_filter([
            $_FILES['image_primary'] ?? null,
            $_FILES['image_secondary'] ?? null,
        ], fn($file) => !empty($file['name']));

        $result = $controller->addProduct((int)$user['id'], $data, $files);
        if ($result['success']) {
            $success = true;
        } else {
            $errors[] = $result['message'] ?? 'Unable to add product.';
        }
    }
}

$title = 'Add Product';

require_once __DIR__ . '/../../app/views/layouts/header.php';
?>

<div class="dashboard">
    <?php $active = 'add-product'; include __DIR__ . '/../../app/views/seller/sidebar.php'; ?>
    <section class="dashboard-content">
        <h1>Add Product</h1>
        <?php if ($success): ?>
            <div class="alert alert-info">Product created successfully.</div>
        <?php endif; ?>
        <?php if ($errors): ?>
            <div class="alert alert-warning">
                <ul><?php foreach ($errors as $error): ?><li><?= Helpers\sanitize($error) ?></li><?php endforeach; ?></ul>
            </div>
        <?php endif; ?>

        <form action="" method="post" enctype="multipart/form-data" class="card">
            <input type="hidden" name="csrf_token" value="<?= Helpers\csrf_token() ?>">
            <label>Title
                <input type="text" name="title" value="<?= Helpers\sanitize($_POST['title'] ?? '') ?>" required>
            </label>
            <label>SKU
                <input type="text" name="sku" value="<?= Helpers\sanitize($_POST['sku'] ?? '') ?>">
            </label>
            <label>Description
                <textarea name="description" rows="4"><?= Helpers\sanitize($_POST['description'] ?? '') ?></textarea>
            </label>
            <div class="form-grid">
                <label>Price
                    <input type="number" step="0.01" name="price" value="<?= Helpers\sanitize($_POST['price'] ?? '') ?>" required>
                </label>
                <label>Sale Price
                    <input type="number" step="0.01" name="sale_price" value="<?= Helpers\sanitize($_POST['sale_price'] ?? '') ?>">
                </label>
                <label>Quantity
                    <input type="number" name="quantity" value="<?= Helpers\sanitize($_POST['quantity'] ?? '0') ?>" required>
                </label>
            </div>
            <div class="form-grid">
                <label>Category
                    <select name="category_id">
                        <option value="">-- None --</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= $category['id'] ?>" <?= (($_POST['category_id'] ?? '') == $category['id']) ? 'selected' : '' ?>><?= Helpers\sanitize($category['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>Weight (kg)
                    <input type="number" step="0.01" name="weight" value="<?= Helpers\sanitize($_POST['weight'] ?? '') ?>">
                </label>
                <label>Dimensions
                    <input type="text" name="dimensions" value="<?= Helpers\sanitize($_POST['dimensions'] ?? '') ?>">
                </label>
            </div>
            <label>Status
                <select name="status">
                    <option value="published" <?= (($_POST['status'] ?? '') === 'published') ? 'selected' : '' ?>>Published</option>
                    <option value="draft" <?= (($_POST['status'] ?? '') === 'draft') ? 'selected' : '' ?>>Draft</option>
                </select>
            </label>
            <div class="form-grid">
                <label>Primary Image
                    <input type="file" name="image_primary" accept="image/*">
                </label>
                <label>Secondary Image
                    <input type="file" name="image_secondary" accept="image/*">
                </label>
            </div>
            <button type="submit" class="btn btn-primary">Save Product</button>
        </form>
    </section>
</div>

<?php require_once __DIR__ . '/../../app/views/layouts/footer.php'; ?>
