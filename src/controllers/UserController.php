<?php

class UserController
{
    public static function index(): void
    {
        Auth::requireAuth();
        Permission::requirePerm('view', 'users');

        $users = User::all();
        require __DIR__ . '/../views/users/index.php';
    }

    public static function create(): void
    {
        Auth::requireAuth();
        Permission::requirePerm('create', 'users');

        $roles = Permission::getAllRoles();
        require __DIR__ . '/../views/users/form.php';
    }

    public static function store(): void
    {
        Auth::requireAuth();
        Permission::requirePerm('create', 'users');
        Csrf::requireValidToken();

        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $fullName = trim($_POST['full_name'] ?? '');
        $role = $_POST['role'] ?? 'staff';
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        // Validation
        $errors = [];

        if (empty($username) || strlen($username) < 3) {
            $errors[] = 'Username must be at least 3 characters';
        }

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Valid email is required';
        }

        if (empty($fullName)) {
            $errors[] = 'Full name is required';
        }

        if (empty($password) || strlen($password) < 6) {
            $errors[] = 'Password must be at least 6 characters';
        }

        if (User::findByUsername($username)) {
            $errors[] = 'Username already taken';
        }

        if (User::findByEmail($email)) {
            $errors[] = 'Email already registered';
        }

        if (!empty($errors)) {
            $_SESSION['flash_error'] = implode('<br>', $errors);
            header('Location: /users/create');
            exit;
        }

        User::create([
            'username' => $username,
            'email' => $email,
            'password' => $password,
            'full_name' => $fullName,
            'role' => $role,
            'is_active' => $isActive
        ]);

        Csrf::regenerate();
        $_SESSION['flash_success'] = 'User created successfully';
        header('Location: /users');
        exit;
    }

    public static function edit(int $id): void
    {
        Auth::requireAuth();
        Permission::requirePerm('edit', 'users');

        $user = User::find($id);
        if (!$user) {
            $_SESSION['flash_error'] = 'User not found';
            header('Location: /users');
            exit;
        }

        $roles = Permission::getAllRoles();
        require __DIR__ . '/../views/users/form.php';
    }

    public static function update(int $id): void
    {
        Auth::requireAuth();
        Permission::requirePerm('edit', 'users');
        Csrf::requireValidToken();

        $user = User::find($id);
        if (!$user) {
            $_SESSION['flash_error'] = 'User not found';
            header('Location: /users');
            exit;
        }

        $data = [
            'username' => trim($_POST['username'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'full_name' => trim($_POST['full_name'] ?? ''),
            'role' => $_POST['role'] ?? 'staff',
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];

        if (!empty($_POST['password'])) {
            $data['password'] = $_POST['password'];
        }

        User::update($id, $data);

        Csrf::regenerate();
        $_SESSION['flash_success'] = 'User updated successfully';
        header('Location: /users');
        exit;
    }

    public static function delete(int $id): void
    {
        Auth::requireAuth();
        Permission::requirePerm('delete', 'users');
        Csrf::requireValidToken();

        if ($id === Auth::id()) {
            $_SESSION['flash_error'] = 'You cannot delete your own account';
            header('Location: /users');
            exit;
        }

        User::delete($id);

        Csrf::regenerate();
        $_SESSION['flash_success'] = 'User deleted successfully';
        header('Location: /users');
        exit;
    }
}
