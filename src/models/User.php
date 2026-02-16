<?php

class User
{
    public static function all(): array
    {
        $pdo = Database::connect();
        $stmt = $pdo->query('SELECT id, username, email, full_name, role, is_active, created_at FROM users ORDER BY created_at DESC');
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $pdo = Database::connect();
        $stmt = $pdo->prepare('SELECT id, username, email, full_name, role, is_active, created_at FROM users WHERE id = ?');
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public static function findByUsername(string $username): ?array
    {
        $pdo = Database::connect();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ?');
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public static function findByEmail(string $email): ?array
    {
        $pdo = Database::connect();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public static function create(array $data): int
    {
        $pdo = Database::connect();
        $stmt = $pdo->prepare('
            INSERT INTO users (username, email, password_hash, full_name, role, is_active)
            VALUES (?, ?, ?, ?, ?, ?)
        ');

        $stmt->execute([
            $data['username'],
            $data['email'],
            password_hash($data['password'], PASSWORD_DEFAULT),
            $data['full_name'],
            $data['role'] ?? 'staff',
            $data['is_active'] ?? true
        ]);

        return (int) $pdo->lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        $pdo = Database::connect();

        $fields = [];
        $values = [];

        if (isset($data['username'])) {
            $fields[] = 'username = ?';
            $values[] = $data['username'];
        }
        if (isset($data['email'])) {
            $fields[] = 'email = ?';
            $values[] = $data['email'];
        }
        if (isset($data['full_name'])) {
            $fields[] = 'full_name = ?';
            $values[] = $data['full_name'];
        }
        if (isset($data['role'])) {
            $fields[] = 'role = ?';
            $values[] = $data['role'];
        }
        if (isset($data['is_active'])) {
            $fields[] = 'is_active = ?';
            $values[] = $data['is_active'];
        }
        if (isset($data['password'])) {
            $fields[] = 'password_hash = ?';
            $values[] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        if (empty($fields)) {
            return false;
        }

        $values[] = $id;
        $sql = 'UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = ?';
        $stmt = $pdo->prepare($sql);
        return $stmt->execute($values);
    }

    public static function delete(int $id): bool
    {
        $pdo = Database::connect();
        $stmt = $pdo->prepare('DELETE FROM users WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    public static function authenticate(string $username, string $password): ?array
    {
        $user = self::findByUsername($username);

        if (!$user) {
            $user = self::findByEmail($username);
        }

        if (!$user || !$user['is_active']) {
            return null;
        }

        if (!self::verifyPassword($password, $user['password_hash'])) {
            return null;
        }

        unset($user['password_hash']);
        return $user;
    }
}
