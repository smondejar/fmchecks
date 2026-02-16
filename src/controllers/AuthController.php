<?php

class AuthController
{
    public static function showLogin(): void
    {
        Auth::requireGuest();
        require __DIR__ . '/../views/login.php';
    }

    public static function login(): void
    {
        Auth::requireGuest();
        Csrf::requireValidToken();

        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            $_SESSION['flash_error'] = 'Username and password are required';
            header('Location: /login');
            exit;
        }

        $user = User::authenticate($username, $password);

        if (!$user) {
            $_SESSION['flash_error'] = 'Invalid credentials';
            header('Location: /login');
            exit;
        }

        Auth::login($user);
        Csrf::regenerate();

        $_SESSION['flash_success'] = 'Welcome back, ' . htmlspecialchars($user['full_name']);
        header('Location: /dashboard');
        exit;
    }

    public static function showRegister(): void
    {
        Auth::requireGuest();

        $allowRegistration = Setting::get('allow_registration', '0');
        if ($allowRegistration !== '1') {
            $_SESSION['flash_error'] = 'Registration is currently disabled';
            header('Location: /login');
            exit;
        }

        require __DIR__ . '/../views/register.php';
    }

    public static function register(): void
    {
        Auth::requireGuest();
        Csrf::requireValidToken();

        $allowRegistration = Setting::get('allow_registration', '0');
        if ($allowRegistration !== '1') {
            $_SESSION['flash_error'] = 'Registration is currently disabled';
            header('Location: /login');
            exit;
        }

        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';
        $fullName = trim($_POST['full_name'] ?? '');

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

        if ($password !== $passwordConfirm) {
            $errors[] = 'Passwords do not match';
        }

        // Check if username exists
        if (User::findByUsername($username)) {
            $errors[] = 'Username already taken';
        }

        // Check if email exists
        if (User::findByEmail($email)) {
            $errors[] = 'Email already registered';
        }

        if (!empty($errors)) {
            $_SESSION['flash_error'] = implode('<br>', $errors);
            header('Location: /register');
            exit;
        }

        // Create user
        $userId = User::create([
            'username' => $username,
            'email' => $email,
            'password' => $password,
            'full_name' => $fullName,
            'role' => 'staff'
        ]);

        $user = User::find($userId);
        Auth::login($user);

        $_SESSION['flash_success'] = 'Registration successful! Welcome, ' . htmlspecialchars($fullName);
        header('Location: /dashboard');
        exit;
    }

    public static function logout(): void
    {
        Auth::logout();
        $_SESSION['flash_success'] = 'Logged out successfully';
        header('Location: /login');
        exit;
    }
}
