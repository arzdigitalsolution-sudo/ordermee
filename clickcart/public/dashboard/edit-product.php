<?php

declare(strict_types=1);

require_once __DIR__ . '/../../app/bootstrap.php';

use ClickCart\Controllers\ProductController;
use ClickCart\Helpers;
use ClickCart\Models\Product;
use ClickCart\Models\ProductImage;
use ClickCart\Models\Category;

Helpers\require_auth('seller');
$user = Helpers\auth_user();
$productModel = new Product();
$imageModel = new ProductImage();
$categoryModel = new Category();
$controller = new ProductController();

$productId = (int)($_GET['id'] ?? 0);
$product = $productModel->find($productId);

if (!$product || (int)$product['seller_id'] !== (int)$user['id']) {
    Helpers\redirect(Helpers\app_url('dashboard/products.php'));
}

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

        $result = $controller->updateProduct($productId, (int)$user['id'], $data, $files);
        if ($result['success']) {
            $product = $productModel->find($productId); // refresh
            $success = true;
        } else {
            $errors[] = $result['message'] ?? 'Unable to update product.';
        }
    }
}

$images = $imageModel->getByProduct($productId);
$categories = $categoryModel->all();
$title = 'Edit Product';

require_once __DIR__ . '/../../app/views/layouts/header.php';
?>

<div class="dashboard">
    <?php $active = 'products'; include __DIR__ . '/../../app/views/seller/sidebar.php'; ?>
    <section class="dashboard-content">
        <h1>Edit Product</h1>
        <?php if ($success): ?>
            <div class="alert alert-info">Product updated successfully.</div>
        <?php endif; ?>
        <?php if ($errors): ?>
            <div class="alert alert-warning">
                <ul><?php foreach ($errors as $error): ?><li><?= Helpers\sanitize($error) ?></li><?php endforeach; ?></ul>
            </div>
        <?php endif; ?>

        <form action="" method="post" enctype="multipart/form-data" class="card">
            <input type="hidden" name="csrf_token" value="<?= Helpers\csrf_token() ?>">
            <label>Title
                <input type="text" name="title" value="<?= Helpers\sanitize($_POST['title'] ?? $product['title']) ?>" required>
            </label>
            <label>SKU
                <input type="text" name="sku" value="<?= Helpers\sanitize($_POST['sku'] ?? $product['sku'] ?? '') ?>">
            </label>
            <label>Description
                <textarea name="description" rows="4"><?= Helpers\sanitize($_POST['description'] ?? $product['description'] ?? '') ?></textarea>
            </label>
            <div class="form-grid">
                <label>Price
                    <input type="number" step="0.01" name="price" value="<?= Helpers\sanitize($_POST['price'] ?? $product['price']) ?>" required>
                </label>
                <label>Sale Price
                    <input type="number" step="0.01" name="sale_price" value="<?= Helpers\sanitize($_POST['sale_price'] ?? ($product['sale_price'] ?? '')) ?>">
                </label>
                <label>Quantity
                    <input type="number" name="quantity" value="<?= Helpers\sanitize($_POST['quantity'] ?? $product['quantity']) ?>" required>
                </label>
            </div>
            <div class="form-grid">
                <label>Category
                    <select name="category_id">
                        <option value="">-- None --</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= $category['id'] ?>" <?= (($_POST['category_id'] ?? $product['category_id']) == $category['id']) ? 'selected' : '' ?>><?= Helpers\sanitize($category['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>Weight (kg)
                    <input type="number" step="0.01" name="weight" value="<?= Helpers\sanitize($_POST['weight'] ?? ($product['weight'] ?? '')) ?>">
                </label>
                <label>Dimensions
                    <input type="text" name="dimensions" value="<?= Helpers\sanitize($_POST['dimensions'] ?? ($product['dimensions'] ?? '')) ?>">
                </label>
            </div>
            <label>Status
                <select name="status">
                    <option value="published" <?= (($_POST['status'] ?? $product['status']) === 'published') ? 'selected' : '' ?>>Published</option>
                    <option value="draft" <?= (($_POST['status'] ?? $product['status']) === 'draft') ? 'selected' : '' ?>>Draft</option>
                </select>
            </label>
            <div class="images-preview">
                <?php foreach ($images as $image): ?>
                    <img src="<?= Helpers\app_url(ltrim($image['image_url'], '/')) ?>" alt="Product image">
                <?php endforeach; ?>
            </div>
            <div class="form-grid">
                <label>Replace Primary Image
                    <input type="file" name="image_primary" accept="image/*">
                </label>
                <label>Add Another Image
                    <input type="file" name="image_secondary" accept="image/*">
                </label>
            </div>
            <button type="submit" class="btn btn-primary">Update Product</button>
        </form>
    </section>
</div>

<?php require_once __DIR__ . '/../../app/views/layouts/footer.php'; ?>
