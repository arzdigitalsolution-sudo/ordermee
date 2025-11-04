<?php

declare(strict_types=1);

require_once __DIR__ . '/../../app/bootstrap.php';

use ClickCart\Controllers\AuthController;
use ClickCart\Helpers;

if (Helpers\auth_user()) {
    Helpers\redirect(Helpers\app_url('index.php'));
}

$type = $_GET['type'] ?? 'buyer';
$type = in_array($type, ['buyer', 'seller'], true) ? $type : 'buyer';

$controller = new AuthController();
$errors = [];
$success = false;

if (Helpers\request_method() === 'POST') {
    if (!Helpers\verify_csrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request token.';
    } else {
        $selectedType = $_POST['type'] ?? 'buyer';
        $selectedType = in_array($selectedType, ['buyer', 'seller'], true) ? $selectedType : 'buyer';

        if ($selectedType === 'seller') {
            $name = trim($_POST['name'] ?? '');
            $brandName = trim($_POST['brand_name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $phone = trim($_POST['phone'] ?? '');
            $bio = trim($_POST['bio'] ?? '');

            $brandLogo = null;
            $profileImage = null;

            if (!empty($_FILES['brand_logo']['name'])) {
                $brandLogo = Helpers\store_upload($_FILES['brand_logo'], 'brand-logos');
            }

            if (!empty($_FILES['profile_image']['name'])) {
                $profileImage = Helpers\store_upload($_FILES['profile_image'], 'profile-images');
            }

            $result = $controller->registerSeller([
                'name' => $name,
                'brand_name' => $brandName,
                'email' => $email,
                'password' => $password,
                'phone' => $phone,
                'bio' => $bio,
                'brand_logo' => $brandLogo,
                'profile_image' => $profileImage,
            ]);

            if ($result['success']) {
                $success = true;
            } else {
                $errors[] = $result['message'] ?? 'Unable to register seller.';
            }
        } else {
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            $result = $controller->registerBuyer([
                'name' => $name,
                'email' => $email,
                'password' => $password,
            ]);

            if ($result['success']) {
                $success = true;
            } else {
                $errors[] = $result['message'] ?? 'Unable to register buyer.';
            }
        }
    }
}

$title = 'Register';

require_once __DIR__ . '/../../app/views/layouts/header.php';
?>

<section class="auth-card card">
    <h1>Create your ClickCart.pk account</h1>

    <?php if ($success): ?>
        <div class="alert alert-info">Account created successfully. You can now <a href="<?= Helpers\app_url('auth/login.php') ?>">log in</a>.</div>
    <?php endif; ?>

    <?php if ($errors): ?>
        <div class="alert alert-warning">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= Helpers\sanitize($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="" method="post" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= Helpers\csrf_token() ?>">
        <label for="type">Account Type</label>
        <select name="type" id="type" onchange="window.location='?type=' + this.value">
            <option value="buyer" <?= $type === 'buyer' ? 'selected' : '' ?>>Buyer</option>
            <option value="seller" <?= $type === 'seller' ? 'selected' : '' ?>>Seller</option>
        </select>

        <label>Name
            <input type="text" name="name" value="<?= Helpers\sanitize($_POST['name'] ?? '') ?>" required>
        </label>
        <label>Email
            <input type="email" name="email" value="<?= Helpers\sanitize($_POST['email'] ?? '') ?>" required>
        </label>
        <label>Password
            <input type="password" name="password" required>
        </label>

        <?php if ($type === 'seller'): ?>
            <label>Brand Name
                <input type="text" name="brand_name" value="<?= Helpers\sanitize($_POST['brand_name'] ?? '') ?>" required>
            </label>
            <label>Brand Logo
                <input type="file" name="brand_logo" accept="image/*">
            </label>
            <label>Profile Image
                <input type="file" name="profile_image" accept="image/*">
            </label>
            <label>Short Bio
                <textarea name="bio" rows="3"><?= Helpers\sanitize($_POST['bio'] ?? '') ?></textarea>
            </label>
            <label>Contact Phone
                <input type="text" name="phone" value="<?= Helpers\sanitize($_POST['phone'] ?? '') ?>">
            </label>
            <p class="note">After 10 completed sales, a one-time ₨100 platform fee will apply automatically on the next sale.</p>
        <?php endif; ?>

        <button type="submit" class="btn btn-primary">Create Account</button>
    </form>
    <p>Already registered? <a href="<?= Helpers\app_url('auth/login.php') ?>">Sign in</a></p>
</section>

<?php require_once __DIR__ . '/../../app/views/layouts/footer.php'; ?>
