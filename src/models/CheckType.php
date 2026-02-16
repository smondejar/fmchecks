<?php

class CheckType
{
    public static function all(): array
    {
        $pdo = Database::connect();
        $stmt = $pdo->query('SELECT * FROM check_types ORDER BY name ASC');
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $pdo = Database::connect();
        $stmt = $pdo->prepare('SELECT * FROM check_types WHERE id = ?');
        $stmt->execute([$id]);
        $type = $stmt->fetch();
        return $type ?: null;
    }

    public static function create(array $data): int
    {
        $pdo = Database::connect();
        $stmt = $pdo->prepare('
            INSERT INTO check_types (name, colour, icon)
            VALUES (?, ?, ?)
        ');

        $stmt->execute([
            $data['name'],
            $data['colour'] ?? '#2563eb',
            $data['icon'] ?? null
        ]);

        return (int) $pdo->lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        $pdo = Database::connect();
        $stmt = $pdo->prepare('
            UPDATE check_types
            SET name = ?, colour = ?, icon = ?
            WHERE id = ?
        ');

        return $stmt->execute([
            $data['name'],
            $data['colour'] ?? '#2563eb',
            $data['icon'] ?? null,
            $id
        ]);
    }

    public static function delete(int $id): bool
    {
        $pdo = Database::connect();
        $stmt = $pdo->prepare('DELETE FROM check_types WHERE id = ?');
        return $stmt->execute([$id]);
    }
}
