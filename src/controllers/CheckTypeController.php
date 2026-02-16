<?php

class CheckTypeController
{
    public static function index(): void
    {
        Auth::requireAuth();
        Permission::requirePerm('view', 'check_types');

        $checkTypes = CheckType::all();
        require __DIR__ . '/../views/check-types/index.php';
    }

    public static function create(): void
    {
        Auth::requireAuth();
        Permission::requirePerm('create', 'check_types');

        require __DIR__ . '/../views/check-types/form.php';
    }

    public static function store(): void
    {
        Auth::requireAuth();
        Permission::requirePerm('create', 'check_types');
        Csrf::requireValidToken();

        $name = trim($_POST['name'] ?? '');
        $colour = trim($_POST['colour'] ?? '#2563eb');
        $icon = trim($_POST['icon'] ?? '');

        if (empty($name)) {
            $_SESSION['flash_error'] = 'Check type name is required';
            header('Location: /check-types/create');
            exit;
        }

        CheckType::create([
            'name' => $name,
            'colour' => $colour,
            'icon' => $icon
        ]);

        Csrf::regenerate();
        $_SESSION['flash_success'] = 'Check type created successfully';
        header('Location: /check-types');
        exit;
    }

    public static function edit(int $id): void
    {
        Auth::requireAuth();
        Permission::requirePerm('edit', 'check_types');

        $checkType = CheckType::find($id);
        if (!$checkType) {
            $_SESSION['flash_error'] = 'Check type not found';
            header('Location: /check-types');
            exit;
        }

        require __DIR__ . '/../views/check-types/form.php';
    }

    public static function update(int $id): void
    {
        Auth::requireAuth();
        Permission::requirePerm('edit', 'check_types');
        Csrf::requireValidToken();

        $checkType = CheckType::find($id);
        if (!$checkType) {
            $_SESSION['flash_error'] = 'Check type not found';
            header('Location: /check-types');
            exit;
        }

        $name = trim($_POST['name'] ?? '');
        $colour = trim($_POST['colour'] ?? '#2563eb');
        $icon = trim($_POST['icon'] ?? '');

        if (empty($name)) {
            $_SESSION['flash_error'] = 'Check type name is required';
            header('Location: /check-types/' . $id . '/edit');
            exit;
        }

        CheckType::update($id, [
            'name' => $name,
            'colour' => $colour,
            'icon' => $icon
        ]);

        Csrf::regenerate();
        $_SESSION['flash_success'] = 'Check type updated successfully';
        header('Location: /check-types');
        exit;
    }

    public static function delete(int $id): void
    {
        Auth::requireAuth();
        Permission::requirePerm('delete', 'check_types');
        Csrf::requireValidToken();

        CheckType::delete($id);

        Csrf::regenerate();
        $_SESSION['flash_success'] = 'Check type deleted successfully';
        header('Location: /check-types');
        exit;
    }
}
