<?php

class Auth
{
    public static function check(): bool
    {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    public static function user(): ?array
    {
        if (!self::check()) {
            return null;
        }

        // Return cached user from session if available
        if (isset($_SESSION['user_data'])) {
            return $_SESSION['user_data'];
        }

        return null;
    }

    public static function id(): ?int
    {
        return $_SESSION['user_id'] ?? null;
    }

    public static function role(): ?string
    {
        $user = self::user();
        return $user['role'] ?? null;
    }

    public static function login(array $user): void
    {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_data'] = $user;
        session_regenerate_id(true);
    }

    public static function logout(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
    }

    public static function guest(): bool
    {
        return !self::check();
    }

    public static function requireAuth(): void
    {
        if (!self::check()) {
            $_SESSION['flash_error'] = 'Please log in to continue';
            header('Location: /login');
            exit;
        }
    }

    public static function requireGuest(): void
    {
        if (self::check()) {
            header('Location: /dashboard');
            exit;
        }
    }

    public static function requireRole(string $role): void
    {
        self::requireAuth();

        if (self::role() !== $role && self::role() !== 'admin') {
            $_SESSION['flash_error'] = 'Access denied';
            header('Location: /dashboard');
            exit;
        }
    }
}
