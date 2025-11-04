<?php

use function ClickCart\Helpers\app_url;
use function ClickCart\Helpers\auth_user;
use function ClickCart\Helpers\csrf_token;

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'ClickCart.pk' ?></title>
    <link rel="stylesheet" href="<?= app_url('assets/css/style.css') ?>">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-3fp9S4CPrlG/pX9nuSUXGKmzj2YkdE+5EYXj3ZXlqtk=" crossorigin="anonymous"></script>
    <script src="<?= app_url('assets/js/app.js') ?>" defer></script>
</head>
<body>
<header class="site-header">
    <div class="container">
        <div class="logo"><a href="<?= app_url('index.php') ?>">ClickCart.pk</a></div>
        <nav class="nav">
            <a href="<?= app_url('index.php') ?>">Home</a>
            <a href="<?= app_url('cart.php') ?>">Cart</a>
            <a href="<?= app_url('pages/about.php') ?>">About</a>
            <a href="<?= app_url('pages/contact.php') ?>">Contact</a>
            <?php if ($user = auth_user()): ?>
                <?php if ($user['role'] === 'seller'): ?>
                    <a href="<?= app_url('dashboard/index.php') ?>">Dashboard</a>
                <?php elseif ($user['role'] === 'admin'): ?>
                    <a href="<?= app_url('admin/index.php') ?>">Admin</a>
                <?php endif; ?>
                <form action="<?= app_url('auth/logout.php') ?>" method="post" class="logout-form">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <button type="submit">Logout</button>
                </form>
            <?php else: ?>
                <a href="<?= app_url('auth/login.php') ?>">Login</a>
                <a href="<?= app_url('auth/register.php') ?>">Register</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<main class="main-content">
    <div class="container">
