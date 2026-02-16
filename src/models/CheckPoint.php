<?php

class CheckPoint
{
    public static function all(): array
    {
        $pdo = Database::connect();
        $stmt = $pdo->query('
            SELECT cp.*, a.area_name, v.name as venue_name, ct.name as type_name, ct.colour as type_colour
            FROM check_points cp
            LEFT JOIN areas a ON cp.area_id = a.id
            LEFT JOIN venues v ON a.venue_id = v.id
            LEFT JOIN check_types ct ON cp.check_type_id = ct.id
            ORDER BY cp.created_at DESC
        ');
        return $stmt->fetchAll();
    }

    public static function findByArea(int $areaId): array
    {
        $pdo = Database::connect();
        $stmt = $pdo->prepare('
            SELECT cp.*, ct.name as type_name, ct.colour as type_colour, ct.icon as type_icon
            FROM check_points cp
            LEFT JOIN check_types ct ON cp.check_type_id = ct.id
            WHERE cp.area_id = ?
            ORDER BY cp.reference ASC
        ');
        $stmt->execute([$areaId]);
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $pdo = Database::connect();
        $stmt = $pdo->prepare('
            SELECT cp.*, a.area_name, a.venue_id, v.name as venue_name, ct.name as type_name, ct.colour as type_colour
            FROM check_points cp
            LEFT JOIN areas a ON cp.area_id = a.id
            LEFT JOIN venues v ON a.venue_id = v.id
            LEFT JOIN check_types ct ON cp.check_type_id = ct.id
            WHERE cp.id = ?
        ');
        $stmt->execute([$id]);
        $point = $stmt->fetch();
        return $point ?: null;
    }

    public static function create(array $data): int
    {
        $pdo = Database::connect();
        $stmt = $pdo->prepare('
            INSERT INTO check_points (area_id, reference, label, check_type_id, x_coord, y_coord, periodicity, notes)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ');

        $stmt->execute([
            $data['area_id'],
            $data['reference'],
            $data['label'],
            $data['check_type_id'],
            $data['x_coord'],
            $data['y_coord'],
            $data['periodicity'] ?? 'monthly',
            $data['notes'] ?? null
        ]);

        return (int) $pdo->lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        $pdo = Database::connect();
        $stmt = $pdo->prepare('
            UPDATE check_points
            SET reference = ?, label = ?, check_type_id = ?, x_coord = ?, y_coord = ?, periodicity = ?, notes = ?
            WHERE id = ?
        ');

        return $stmt->execute([
            $data['reference'],
            $data['label'],
            $data['check_type_id'],
            $data['x_coord'],
            $data['y_coord'],
            $data['periodicity'],
            $data['notes'] ?? null,
            $id
        ]);
    }

    public static function delete(int $id): bool
    {
        $pdo = Database::connect();
        $stmt = $pdo->prepare('DELETE FROM check_points WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public static function getLastCheck(int $checkPointId): ?array
    {
        $pdo = Database::connect();
        $stmt = $pdo->prepare('
            SELECT * FROM check_logs
            WHERE check_point_id = ?
            ORDER BY performed_at DESC
            LIMIT 1
        ');
        $stmt->execute([$checkPointId]);
        $log = $stmt->fetch();
        return $log ?: null;
    }

    public static function getStatus(int $checkPointId, string $periodicity): string
    {
        $lastCheck = self::getLastCheck($checkPointId);

        if (!$lastCheck) {
            return 'never';
        }

        $lastCheckTime = strtotime($lastCheck['performed_at']);
        $now = time();

        // Calculate period end based on periodicity
        $periodSeconds = self::getPeriodicitySeconds($periodicity);
        $dueTime = $lastCheckTime + $periodSeconds;

        // Amber warning 24 hours before due
        $warningTime = $dueTime - (24 * 3600);

        if ($now > $dueTime) {
            return 'overdue';
        } elseif ($now > $warningTime) {
            return 'due_soon';
        } else {
            return 'ok';
        }
    }

    private static function getPeriodicitySeconds(string $periodicity): int
    {
        return match ($periodicity) {
            'daily' => 86400,
            'weekly' => 604800,
            'monthly' => 2592000,
            'quarterly' => 7776000,
            'annually' => 31536000,
            default => 2592000
        };
    }
}
