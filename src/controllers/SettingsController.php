<?php

class SettingsController
{
    public static function index(): void
    {
        Auth::requireAuth();
        Permission::requirePerm('view', 'settings');

        $settings = Setting::all();
        require __DIR__ . '/../views/settings/index.php';
    }

    public static function update(): void
    {
        Auth::requireAuth();
        Permission::requirePerm('edit', 'settings');
        Csrf::requireValidToken();

        foreach ($_POST as $key => $value) {
            if ($key === 'csrf_token') {
                continue;
            }

            Setting::set($key, $value);
        }

        Csrf::regenerate();
        $_SESSION['flash_success'] = 'Settings updated successfully';
        header('Location: /settings');
        exit;
    }
}
