<?php

declare(strict_types=1);

require_once __DIR__ . '/../../app/bootstrap.php';

use ClickCart\Controllers\AuthController;
use ClickCart\Helpers;

if (Helpers\auth_user()) {
    Helpers\redirect(Helpers\app_url('index.php'));
}

$controller = new AuthController();
$sent = false;
$errors = [];

if (Helpers\request_method() === 'POST') {
    if (!Helpers\verify_csrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request token.';
    } else {
        $email = trim($_POST['email'] ?? '');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Enter a valid email address.';
        } else {
            $controller->sendPasswordReset($email);
            $sent = true;
        }
    }
}

$title = 'Forgot Password';

require_once __DIR__ . '/../../app/views/layouts/header.php';
?>

<section class="auth-card card">
    <h1>Reset your password</h1>
    <?php if ($sent): ?>
        <div class="alert alert-info">If the email exists in our records, a reset link has been sent.</div>
    <?php endif; ?>
    <?php if ($errors): ?>
        <div class="alert alert-warning">
            <ul><?php foreach ($errors as $error): ?><li><?= Helpers\sanitize($error) ?></li><?php endforeach; ?></ul>
        </div>
    <?php endif; ?>
    <form action="" method="post">
        <input type="hidden" name="csrf_token" value="<?= Helpers\csrf_token() ?>">
        <label>Email
            <input type="email" name="email" required>
        </label>
        <button type="submit" class="btn btn-primary">Send reset link</button>
    </form>
</section>

<?php require_once __DIR__ . '/../../app/views/layouts/footer.php'; ?>
