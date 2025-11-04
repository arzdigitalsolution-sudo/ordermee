<?php

declare(strict_types=1);

namespace ClickCart\Helpers\Email;

use ClickCart\Config\Env;

function send_mail(string $to, string $subject, string $message): bool
{
    $headers = 'From: ' . Env::get('MAIL_FROM_NAME', 'ClickCart') . ' <' . Env::get('MAIL_FROM_ADDRESS', 'no-reply@clickcart.pk') . '>' . "\r\n";
    $headers .= 'Content-Type: text/html; charset=UTF-8' . "\r\n";

    if (Env::get('APP_ENV', 'local') === 'local') {
        // Log emails locally instead of sending
        $logDir = dirname(__DIR__, 2) . '/storage/logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0775, true);
        }
        file_put_contents($logDir . '/mail.log', sprintf("[%s] %s | %s\n%s\n\n", date('Y-m-d H:i:s'), $to, $subject, $message), FILE_APPEND);
        return true;
    }

    return mail($to, $subject, $message, $headers);
}
