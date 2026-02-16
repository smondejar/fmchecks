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
        // Check POST data first
        $token = $_POST['csrf_token'] ?? '';

        // If not in POST, check JSON body
        if (empty($token)) {
            $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
            if (strpos($contentType, 'application/json') !== false) {
                $jsonData = json_decode(file_get_contents('php://input'), true);
                $token = $jsonData['csrf_token'] ?? '';
            }
        }

        // Check custom header as fallback
        if (empty($token)) {
            $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        }

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
