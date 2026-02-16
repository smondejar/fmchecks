<?php

class Csrf
{
    private static function getToken(): string
    {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function field(): string
    {
        $token = self::getToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
    }

    public static function validate(): bool
    {
        $token = $_POST['csrf_token'] ?? '';
        $sessionToken = $_SESSION['csrf_token'] ?? '';

        return !empty($token) && !empty($sessionToken) && hash_equals($sessionToken, $token);
    }

    public static function regenerate(): void
    {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    public static function requireValidToken(): void
    {
        if (!self::validate()) {
            $_SESSION['flash_error'] = 'Invalid security token. Please try again.';
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/dashboard'));
            exit;
        }
    }
}
