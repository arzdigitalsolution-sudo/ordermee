<?php

declare(strict_types=1);

use ClickCart\Config\Env;

require_once __DIR__ . '/../vendor/autoload.php';

if (class_exists(\Dotenv\Dotenv::class)) {
    $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
    $dotenv->safeLoad();
} else {
    Env::load(dirname(__DIR__));
}

// Ensure helpers are loaded
require_once __DIR__ . '/helpers/helpers.php';
require_once __DIR__ . '/helpers/cart.php';
require_once __DIR__ . '/helpers/email.php';

// Start session for global access
ClickCart\Helpers\session_start_secure();
