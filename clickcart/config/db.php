<?php

declare(strict_types=1);

namespace ClickCart\Config;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $connection = null;

    public static function getConnection(): PDO
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        $host = Env::get('DB_HOST', '127.0.0.1');
        $port = Env::get('DB_PORT', 3306);
        $database = Env::get('DB_DATABASE', 'clickcart');
        $username = Env::get('DB_USERNAME', 'root');
        $password = Env::get('DB_PASSWORD', '');

        $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $host, $port, $database);

        try {
            self::$connection = new PDO($dsn, (string)$username, (string)$password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $exception) {
            if (Env::get('APP_DEBUG', false)) {
                throw $exception;
            }

            error_log('Database connection failed: ' . $exception->getMessage());
            throw new PDOException('Database connection failed.');
        }

        return self::$connection;
    }
}
