<?php

class CheckLog
{
    private static function buildBaseQuery(array $filters, bool $forExport = false): array
    {
        $sql = '
            SELECT cl.id, cl.status, cl.notes, cl.performed_at,
                   cp.reference, cp.label, a.area_name, v.name as venue_name,
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
        if (!empty($filters['date_from'])) {
            $sql .= ' AND DATE(cl.performed_at) >= ?';
            $params[] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $sql .= ' AND DATE(cl.performed_at) <= ?';
            $params[] = $filters['date_to'];
        }

        $sql .= ' ORDER BY cl.performed_at DESC';
        if (!$forExport) {
            $sql .= ' LIMIT 1000';
        }

        return [$sql, $params];
    }

    public static function all(array $filters = []): array
    {
        $pdo = Database::connect();
        [$sql, $params] = self::buildBaseQuery($filters, false);
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function allForExport(array $filters = []): array
    {
        $pdo = Database::connect();
        [$sql, $params] = self::buildBaseQuery($filters, true);
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function analyticsData(array $filters = []): array
    {
        $pdo = Database::connect();

        $joins = '
            FROM check_logs cl
            LEFT JOIN check_points cp ON cl.check_point_id = cp.id
            LEFT JOIN areas a ON cp.area_id = a.id
            LEFT JOIN venues v ON a.venue_id = v.id
        ';

        $where = 'WHERE 1=1';
        $params = [];

        if (!empty($filters['date_from'])) {
            $where .= ' AND DATE(cl.performed_at) >= ?';
            $params[] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $where .= ' AND DATE(cl.performed_at) <= ?';
            $params[] = $filters['date_to'];
        }
        if (!empty($filters['venue_id'])) {
            $where .= ' AND v.id = ?';
            $params[] = $filters['venue_id'];
        }

        // Summary counts
        $stmt = $pdo->prepare("
            SELECT
                COUNT(*) as total,
                SUM(CASE WHEN cl.status = 'pass' THEN 1 ELSE 0 END) as total_pass,
                SUM(CASE WHEN cl.status = 'fail' THEN 1 ELSE 0 END) as total_fail,
                COUNT(DISTINCT cl.check_point_id) as active_checkpoints
            $joins $where
        ");
        $stmt->execute($params);
        $summary = $stmt->fetch(PDO::FETCH_ASSOC);

        // Daily pass/fail counts
        $stmt = $pdo->prepare("
            SELECT DATE(cl.performed_at) as day,
                   SUM(CASE WHEN cl.status = 'pass' THEN 1 ELSE 0 END) as pass_count,
                   SUM(CASE WHEN cl.status = 'fail' THEN 1 ELSE 0 END) as fail_count
            $joins $where
            GROUP BY DATE(cl.performed_at)
            ORDER BY day ASC
        ");
        $stmt->execute($params);
        $daily = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Failure rate by check type
        $stmt = $pdo->prepare("
            SELECT ct.name as type_name,
                   COUNT(*) as total,
                   SUM(CASE WHEN cl.status = 'fail' THEN 1 ELSE 0 END) as fail_count
            $joins
            LEFT JOIN check_types ct ON cp.check_type_id = ct.id
            $where
            GROUP BY ct.id, ct.name
            ORDER BY fail_count DESC
            LIMIT 10
        ");
        $stmt->execute($params);
        $byType = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Failure rate by area
        $stmt = $pdo->prepare("
            SELECT a.area_name, v.name as venue_name,
                   COUNT(*) as total,
                   SUM(CASE WHEN cl.status = 'fail' THEN 1 ELSE 0 END) as fail_count
            $joins $where
            GROUP BY a.id, a.area_name, v.name
            ORDER BY fail_count DESC
            LIMIT 10
        ");
        $stmt->execute($params);
        $byArea = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Top failed checkpoints
        $stmt = $pdo->prepare("
            SELECT cp.reference, cp.label, a.area_name, v.name as venue_name,
                   ct.name as type_name, COUNT(*) as fail_count
            $joins
            LEFT JOIN check_types ct ON cp.check_type_id = ct.id
            $where AND cl.status = 'fail'
            GROUP BY cl.check_point_id, cp.reference, cp.label, a.area_name, v.name, ct.name
            ORDER BY fail_count DESC
            LIMIT 10
        ");
        $stmt->execute($params);
        $topFailed = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'summary'  => $summary,
            'daily'    => $daily,
            'by_type'  => $byType,
            'by_area'  => $byArea,
            'top_failed' => $topFailed,
        ];
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
