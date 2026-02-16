<?php

class Database
{
    private static ?PDO $pdo = null;

    public static function connect(): PDO
    {
        if (self::$pdo !== null) {
            return self::$pdo;
        }

        // Try to load local config first, fallback to example
        $configPath = __DIR__ . '/database.local.php';
        if (!file_exists($configPath)) {
            $configPath = __DIR__ . '/database.example.php';
        }

        if (!file_exists($configPath)) {
            throw new Exception('Database configuration file not found');
        }

        $config = require $configPath;

        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
            $config['host'],
            $config['port'],
            $config['database']
        );

        try {
            self::$pdo = new PDO(
                $dsn,
                $config['username'],
                $config['password'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        } catch (PDOException $e) {
            error_log('Database connection failed: ' . $e->getMessage());
            throw new Exception('Database connection failed');
        }

        return self::$pdo;
    }

    public static function disconnect(): void
    {
        self::$pdo = null;
    }
}
