<?php

class Setting
{
    public static function get(string $key, $default = null)
    {
        $pdo = Database::connect();
        $stmt = $pdo->prepare('SELECT setting_value FROM settings WHERE setting_key = ?');
        $stmt->execute([$key]);
        $result = $stmt->fetch();

        return $result ? $result['setting_value'] : $default;
    }

    public static function set(string $key, $value): bool
    {
        $pdo = Database::connect();
        $stmt = $pdo->prepare('
            INSERT INTO settings (setting_key, setting_value)
            VALUES (?, ?)
            ON DUPLICATE KEY UPDATE setting_value = ?
        ');

        return $stmt->execute([$key, $value, $value]);
    }

    public static function all(): array
    {
        $pdo = Database::connect();
        $stmt = $pdo->query('SELECT * FROM settings ORDER BY setting_key');
        return $stmt->fetchAll();
    }

    public static function delete(string $key): bool
    {
        $pdo = Database::connect();
        $stmt = $pdo->prepare('DELETE FROM settings WHERE setting_key = ?');
        return $stmt->execute([$key]);
    }
}
