<?php

class Venue
{
    public static function all(): array
    {
        $pdo = Database::connect();
        $stmt = $pdo->query('
            SELECT v.*, u.full_name as creator_name,
                   COUNT(DISTINCT a.id) as area_count
            FROM venues v
            LEFT JOIN users u ON v.created_by = u.id
            LEFT JOIN areas a ON v.id = a.venue_id
            GROUP BY v.id
            ORDER BY v.name ASC
        ');
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $pdo = Database::connect();
        $stmt = $pdo->prepare('
            SELECT v.*, u.full_name as creator_name
            FROM venues v
            LEFT JOIN users u ON v.created_by = u.id
            WHERE v.id = ?
        ');
        $stmt->execute([$id]);
        $venue = $stmt->fetch();
        return $venue ?: null;
    }

    public static function create(array $data): int
    {
        $pdo = Database::connect();
        $stmt = $pdo->prepare('
            INSERT INTO venues (name, address, notes, created_by)
            VALUES (?, ?, ?, ?)
        ');

        $stmt->execute([
            $data['name'],
            $data['address'] ?? null,
            $data['notes'] ?? null,
            $data['created_by']
        ]);

        return (int) $pdo->lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        $pdo = Database::connect();
        $stmt = $pdo->prepare('
            UPDATE venues
            SET name = ?, address = ?, notes = ?
            WHERE id = ?
        ');

        return $stmt->execute([
            $data['name'],
            $data['address'] ?? null,
            $data['notes'] ?? null,
            $id
        ]);
    }

    public static function delete(int $id): bool
    {
        $pdo = Database::connect();
        $stmt = $pdo->prepare('DELETE FROM venues WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public static function getAreas(int $venueId): array
    {
        $pdo = Database::connect();
        $stmt = $pdo->prepare('
            SELECT a.*, u.full_name as uploader_name,
                   COUNT(DISTINCT cp.id) as checkpoint_count
            FROM areas a
            LEFT JOIN users u ON a.uploaded_by = u.id
            LEFT JOIN check_points cp ON a.id = cp.area_id
            WHERE a.venue_id = ?
            GROUP BY a.id
            ORDER BY a.sort_order ASC, a.created_at DESC
        ');
        $stmt->execute([$venueId]);
        return $stmt->fetchAll();
    }
}
