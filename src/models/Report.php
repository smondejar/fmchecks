<?php

class Report
{
    public static function all(array $filters = []): array
    {
        $pdo = Database::connect();

        $sql = '
            SELECT r.*, v.name as venue_name, a.area_name, cp.reference as checkpoint_ref,
                   u1.full_name as creator_name, u2.full_name as assigned_name, u3.full_name as resolver_name
            FROM reports r
            LEFT JOIN venues v ON r.venue_id = v.id
            LEFT JOIN areas a ON r.area_id = a.id
            LEFT JOIN check_points cp ON r.check_point_id = cp.id
            LEFT JOIN users u1 ON r.created_by = u1.id
            LEFT JOIN users u2 ON r.assigned_to = u2.id
            LEFT JOIN users u3 ON r.resolved_by = u3.id
            WHERE 1=1
        ';

        $params = [];

        if (!empty($filters['venue_id'])) {
            $sql .= ' AND r.venue_id = ?';
            $params[] = $filters['venue_id'];
        }

        if (!empty($filters['area_id'])) {
            $sql .= ' AND r.area_id = ?';
            $params[] = $filters['area_id'];
        }

        if (!empty($filters['severity'])) {
            $sql .= ' AND r.severity = ?';
            $params[] = $filters['severity'];
        }

        if (!empty($filters['status'])) {
            $sql .= ' AND r.status = ?';
            $params[] = $filters['status'];
        }

        if (!empty($filters['assigned_to'])) {
            $sql .= ' AND r.assigned_to = ?';
            $params[] = $filters['assigned_to'];
        }

        $sql .= ' ORDER BY r.created_at DESC';

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $pdo = Database::connect();
        $stmt = $pdo->prepare('
            SELECT r.*, v.name as venue_name, a.area_name, cp.reference as checkpoint_ref, cp.label as checkpoint_label,
                   u1.full_name as creator_name, u2.full_name as assigned_name, u3.full_name as resolver_name,
                   cl.status as check_status, cl.notes as check_notes
            FROM reports r
            LEFT JOIN venues v ON r.venue_id = v.id
            LEFT JOIN areas a ON r.area_id = a.id
            LEFT JOIN check_points cp ON r.check_point_id = cp.id
            LEFT JOIN users u1 ON r.created_by = u1.id
            LEFT JOIN users u2 ON r.assigned_to = u2.id
            LEFT JOIN users u3 ON r.resolved_by = u3.id
            LEFT JOIN check_logs cl ON r.check_log_id = cl.id
            WHERE r.id = ?
        ');
        $stmt->execute([$id]);
        $report = $stmt->fetch();
        return $report ?: null;
    }

    public static function create(array $data): int
    {
        $pdo = Database::connect();
        $stmt = $pdo->prepare('
            INSERT INTO reports (check_log_id, check_point_id, venue_id, area_id, title, description, severity, status, assigned_to, created_by)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ');

        $stmt->execute([
            $data['check_log_id'] ?? null,
            $data['check_point_id'] ?? null,
            $data['venue_id'],
            $data['area_id'] ?? null,
            $data['title'],
            $data['description'],
            $data['severity'] ?? 'medium',
            $data['status'] ?? 'open',
            $data['assigned_to'] ?? null,
            $data['created_by']
        ]);

        return (int) $pdo->lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        $pdo = Database::connect();

        $fields = [];
        $values = [];

        if (isset($data['title'])) {
            $fields[] = 'title = ?';
            $values[] = $data['title'];
        }
        if (isset($data['description'])) {
            $fields[] = 'description = ?';
            $values[] = $data['description'];
        }
        if (isset($data['severity'])) {
            $fields[] = 'severity = ?';
            $values[] = $data['severity'];
        }
        if (isset($data['status'])) {
            $fields[] = 'status = ?';
            $values[] = $data['status'];
        }
        if (isset($data['assigned_to'])) {
            $fields[] = 'assigned_to = ?';
            $values[] = $data['assigned_to'];
        }

        if (empty($fields)) {
            return false;
        }

        $values[] = $id;
        $sql = 'UPDATE reports SET ' . implode(', ', $fields) . ' WHERE id = ?';
        $stmt = $pdo->prepare($sql);
        return $stmt->execute($values);
    }

    public static function resolve(int $id, int $userId, string $notes): bool
    {
        $pdo = Database::connect();
        $stmt = $pdo->prepare('
            UPDATE reports
            SET status = ?, resolved_at = NOW(), resolved_by = ?, resolution_notes = ?
            WHERE id = ?
        ');

        return $stmt->execute(['resolved', $userId, $notes, $id]);
    }

    public static function delete(int $id): bool
    {
        $pdo = Database::connect();
        $stmt = $pdo->prepare('DELETE FROM reports WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public static function getOpenCount(): int
    {
        $pdo = Database::connect();
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM reports WHERE status IN ('open', 'in_progress')");
        $result = $stmt->fetch();
        return (int) $result['count'];
    }

    public static function getCriticalCount(): int
    {
        $pdo = Database::connect();
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM reports WHERE severity = 'critical' AND status IN ('open', 'in_progress')");
        $result = $stmt->fetch();
        return (int) $result['count'];
    }
}
