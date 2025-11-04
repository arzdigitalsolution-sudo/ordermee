<?php

declare(strict_types=1);

require_once __DIR__ . '/../../app/bootstrap.php';

use ClickCart\Helpers;
use ClickCart\Models\User;

Helpers\require_auth('seller');
$user = Helpers\auth_user();
$userModel = new User();
$errors = [];
$success = false;

if (Helpers\request_method() === 'POST') {
    if (!Helpers\verify_csrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request token.';
    } else {
        $updateData = [
            'brand_name' => trim($_POST['brand_name'] ?? ''),
            'bio' => trim($_POST['bio'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
        ];

        if (!empty($_FILES['brand_logo']['name'])) {
            $path = Helpers\store_upload($_FILES['brand_logo'], 'brand-logos');
            if ($path) {
                $updateData['brand_logo'] = $path;
            }
        }

        if (!empty($_FILES['profile_image']['name'])) {
            $path = Helpers\store_upload($_FILES['profile_image'], 'profile-images');
            if ($path) {
                $updateData['profile_image'] = $path;
            }
        }

        $userModel->updateProfile((int)$user['id'], $updateData);
        $user = $userModel->findById((int)$user['id']);
        Helpers\set_auth_user($user);
        $success = true;
    }
}

$title = 'Store Settings';

require_once __DIR__ . '/../../app/views/layouts/header.php';
?>

<div class="dashboard">
    <?php $active = 'settings'; include __DIR__ . '/../../app/views/seller/sidebar.php'; ?>
    <section class="dashboard-content">
        <h1>Store Settings</h1>
        <?php if ($success): ?>
            <div class="alert alert-info">Settings updated.</div>
        <?php endif; ?>
        <?php if ($errors): ?>
            <div class="alert alert-warning">
                <ul><?php foreach ($errors as $error): ?><li><?= Helpers\sanitize($error) ?></li><?php endforeach; ?></ul>
            </div>
        <?php endif; ?>

        <form action="" method="post" enctype="multipart/form-data" class="card">
            <input type="hidden" name="csrf_token" value="<?= Helpers\csrf_token() ?>">
            <label>Brand Name
                <input type="text" name="brand_name" value="<?= Helpers\sanitize($user['brand_name'] ?? '') ?>">
            </label>
            <label>Bio
                <textarea name="bio" rows="4"><?= Helpers\sanitize($user['bio'] ?? '') ?></textarea>
            </label>
            <label>Contact Phone
                <input type="text" name="phone" value="<?= Helpers\sanitize($user['phone'] ?? '') ?>">
            </label>
            <div class="form-grid">
                <label>Brand Logo
                    <input type="file" name="brand_logo" accept="image/*">
                </label>
                <label>Profile Image
                    <input type="file" name="profile_image" accept="image/*">
                </label>
            </div>
            <button type="submit" class="btn btn-primary">Save Changes</button>
        </form>
    </section>
</div>

<?php require_once __DIR__ . '/../../app/views/layouts/footer.php'; ?>
