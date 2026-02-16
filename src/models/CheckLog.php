<?php

class CheckLog
{
    public static function all(array $filters = []): array
    {
        $pdo = Database::connect();

        $sql = '
            SELECT cl.*, cp.reference, cp.label, a.area_name, v.name as venue_name,
                   u.full_name as performer_name, ct.name as type_name
            FROM check_logs cl
            LEFT JOIN check_points cp ON cl.check_point_id = cp.id
            LEFT JOIN areas a ON cp.area_id = a.id
            LEFT JOIN venues v ON a.venue_id = v.id
            LEFT JOIN users u ON cl.performed_by = u.id
            LEFT JOIN check_types ct ON cp.check_type_id = ct.id
            WHERE 1=1
        ';

        $params = [];

        if (!empty($filters['venue_id'])) {
            $sql .= ' AND v.id = ?';
            $params[] = $filters['venue_id'];
        }

        if (!empty($filters['area_id'])) {
            $sql .= ' AND a.id = ?';
            $params[] = $filters['area_id'];
        }

        if (!empty($filters['status'])) {
            $sql .= ' AND cl.status = ?';
            $params[] = $filters['status'];
        }

        if (!empty($filters['performed_by'])) {
            $sql .= ' AND cl.performed_by = ?';
            $params[] = $filters['performed_by'];
        }

        $sql .= ' ORDER BY cl.performed_at DESC LIMIT 1000';

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $pdo = Database::connect();
        $stmt = $pdo->prepare('
            SELECT cl.*, cp.reference, cp.label, cp.area_id, a.area_name, a.venue_id, v.name as venue_name,
                   u.full_name as performer_name, ct.name as type_name
            FROM check_logs cl
            LEFT JOIN check_points cp ON cl.check_point_id = cp.id
            LEFT JOIN areas a ON cp.area_id = a.id
            LEFT JOIN venues v ON a.venue_id = v.id
            LEFT JOIN users u ON cl.performed_by = u.id
            LEFT JOIN check_types ct ON cp.check_type_id = ct.id
            WHERE cl.id = ?
        ');
        $stmt->execute([$id]);
        $log = $stmt->fetch();
        return $log ?: null;
    }

    public static function create(array $data): int
    {
        $pdo = Database::connect();
        $stmt = $pdo->prepare('
            INSERT INTO check_logs (check_point_id, performed_by, status, notes, photo_path)
            VALUES (?, ?, ?, ?, ?)
        ');

        $stmt->execute([
            $data['check_point_id'],
            $data['performed_by'],
            $data['status'],
            $data['notes'] ?? null,
            $data['photo_path'] ?? null
        ]);

        return (int) $pdo->lastInsertId();
    }

    public static function delete(int $id): bool
    {
        $pdo = Database::connect();
        $stmt = $pdo->prepare('DELETE FROM check_logs WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public static function getByCheckPoint(int $checkPointId, int $limit = 10): array
    {
        $pdo = Database::connect();
        $stmt = $pdo->prepare('
            SELECT cl.*, u.full_name as performer_name
            FROM check_logs cl
            LEFT JOIN users u ON cl.performed_by = u.id
            WHERE cl.check_point_id = ?
            ORDER BY cl.performed_at DESC
            LIMIT ?
        ');
        $stmt->execute([$checkPointId, $limit]);
        return $stmt->fetchAll();
    }
}
