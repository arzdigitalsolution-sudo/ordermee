<?php

declare(strict_types=1);

require_once __DIR__ . '/../../app/bootstrap.php';

use ClickCart\Controllers\AuthController;
use ClickCart\Helpers;

if (Helpers\auth_user()) {
    Helpers\redirect(Helpers\app_url('index.php'));
}

$controller = new AuthController();
$errors = [];

if (Helpers\request_method() === 'POST') {
    if (!Helpers\verify_csrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request token.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($email === '' || $password === '') {
            $errors[] = 'Email and password are required.';
        } else {
            $result = $controller->attemptLogin($email, $password);
            if ($result['success']) {
                $user = $result['user'];
                if ($user['role'] === 'seller') {
                    Helpers\redirect(Helpers\app_url('dashboard/index.php'));
                }
                if ($user['role'] === 'admin') {
                    Helpers\redirect(Helpers\app_url('admin/index.php'));
                }
                Helpers\redirect(Helpers\app_url('index.php'));
            } else {
                $errors[] = $result['message'] ?? 'Login failed.';
            }
        }
    }
}

$title = 'Login';

require_once __DIR__ . '/../../app/views/layouts/header.php';
?>

<section class="auth-card card">
    <h1>Sign in to ClickCart.pk</h1>
    <?php if ($errors): ?>
        <div class="alert alert-warning">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= Helpers\sanitize($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    <form action="" method="post">
        <input type="hidden" name="csrf_token" value="<?= Helpers\csrf_token() ?>">
        <label>Email
            <input type="email" name="email" value="<?= Helpers\sanitize($_POST['email'] ?? '') ?>" required>
        </label>
        <label>Password
            <input type="password" name="password" required>
        </label>
        <button type="submit" class="btn btn-primary">Login</button>
    </form>
    <p><a href="<?= Helpers\app_url('auth/register.php') ?>">Need an account? Register</a></p>
    <p><a href="<?= Helpers\app_url('auth/forgot.php') ?>">Forgot password?</a></p>
</section>

<?php require_once __DIR__ . '/../../app/views/layouts/footer.php'; ?>
