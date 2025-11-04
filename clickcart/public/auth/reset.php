<?php

declare(strict_types=1);

require_once __DIR__ . '/../../app/bootstrap.php';

use ClickCart\Controllers\AuthController;
use ClickCart\Helpers;

if (Helpers\auth_user()) {
    Helpers\redirect(Helpers\app_url('index.php'));
}

$token = $_GET['token'] ?? '';
$controller = new AuthController();
$errors = [];
$success = false;

if (Helpers\request_method() === 'POST') {
    if (!Helpers\verify_csrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request token.';
    } else {
        $token = $_POST['token'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['password_confirmation'] ?? '';
        if ($password === '' || strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters.';
        } elseif ($password !== $confirm) {
            $errors[] = 'Passwords do not match.';
        } else {
            if ($controller->resetPassword($token, $password)) {
                $success = true;
            } else {
                $errors[] = 'Reset link is invalid or expired.';
            }
        }
    }
}

$title = 'Set New Password';

require_once __DIR__ . '/../../app/views/layouts/header.php';
?>

<section class="auth-card card">
    <h1>Create a new password</h1>
    <?php if ($success): ?>
        <div class="alert alert-info">Password updated. <a href="<?= Helpers\app_url('auth/login.php') ?>">Login</a></div>
    <?php endif; ?>
    <?php if ($errors): ?>
        <div class="alert alert-warning">
            <ul><?php foreach ($errors as $error): ?><li><?= Helpers\sanitize($error) ?></li><?php endforeach; ?></ul>
        </div>
    <?php endif; ?>
    <form action="" method="post">
        <input type="hidden" name="csrf_token" value="<?= Helpers\csrf_token() ?>">
        <input type="hidden" name="token" value="<?= Helpers\sanitize($token) ?>">
        <label>New Password
            <input type="password" name="password" required>
        </label>
        <label>Confirm Password
            <input type="password" name="password_confirmation" required>
        </label>
        <button type="submit" class="btn btn-primary">Update Password</button>
    </form>
</section>

<?php require_once __DIR__ . '/../../app/views/layouts/footer.php'; ?>
