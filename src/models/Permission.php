<?php

class Permission
{
    private static array $rolePermissions = [
        'admin' => [
            'venues' => ['view', 'create', 'edit', 'delete'],
            'areas' => ['view', 'create', 'edit', 'delete'],
            'checks' => ['view', 'create', 'edit', 'delete'],
            'check_types' => ['view', 'create', 'edit', 'delete'],
            'reports' => ['view', 'create', 'edit', 'delete'],
            'users' => ['view', 'create', 'edit', 'delete'],
            'settings' => ['view', 'edit'],
        ],
        'manager' => [
            'venues' => ['view', 'create', 'edit', 'delete'],
            'areas' => ['view', 'create', 'edit', 'delete'],
            'checks' => ['view', 'create', 'edit', 'delete'],
            'check_types' => ['view', 'create', 'edit', 'delete'],
            'reports' => ['view', 'create', 'edit', 'delete'],
            'users' => ['view'],
        ],
        'staff' => [
            'venues' => ['view'],
            'areas' => ['view'],
            'checks' => ['view', 'create'],
            'check_types' => ['view'],
            'reports' => ['view'],
        ],
        'viewer' => [
            'venues' => ['view'],
            'areas' => ['view'],
            'checks' => ['view'],
            'check_types' => ['view'],
            'reports' => ['view'],
        ],
    ];

    public static function can(string $action, string $resource): bool
    {
        $role = Auth::role();

        if (!$role) {
            return false;
        }

        if (!isset(self::$rolePermissions[$role][$resource])) {
            return false;
        }

        return in_array($action, self::$rolePermissions[$role][$resource]);
    }

    public static function requirePerm(string $action, string $resource): void
    {
        if (!self::can($action, $resource)) {
            $_SESSION['flash_error'] = 'You do not have permission to perform this action';
            header('Location: /dashboard');
            exit;
        }
    }

    public static function getPermissions(string $role): array
    {
        return self::$rolePermissions[$role] ?? [];
    }

    public static function getAllRoles(): array
    {
        return ['admin', 'manager', 'staff', 'viewer'];
    }
}
