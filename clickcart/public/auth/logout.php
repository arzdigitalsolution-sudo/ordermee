<?php

declare(strict_types=1);

require_once __DIR__ . '/../../app/bootstrap.php';

use ClickCart\Helpers;

if (Helpers\request_method() === 'POST' && Helpers\verify_csrf($_POST['csrf_token'] ?? '')) {
    Helpers\logout();
}

Helpers\redirect(Helpers\app_url('index.php'));
