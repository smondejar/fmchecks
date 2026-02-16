<?php

class Area
{
    public static function all(): array
    {
        $pdo = Database::connect();
        $stmt = $pdo->query('
            SELECT a.*, v.name as venue_name, u.full_name as uploader_name
            FROM areas a
            LEFT JOIN venues v ON a.venue_id = v.id
            LEFT JOIN users u ON a.uploaded_by = u.id
            ORDER BY v.name ASC, a.sort_order ASC, a.created_at DESC
        ');
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $pdo = Database::connect();
        $stmt = $pdo->prepare('
            SELECT a.*, v.name as venue_name, u.full_name as uploader_name
            FROM areas a
            LEFT JOIN venues v ON a.venue_id = v.id
            LEFT JOIN users u ON a.uploaded_by = u.id
            WHERE a.id = ?
        ');
        $stmt->execute([$id]);
        $area = $stmt->fetch();
        return $area ?: null;
    }

    public static function create(array $data): int
    {
        $pdo = Database::connect();
        $stmt = $pdo->prepare('
            INSERT INTO areas (venue_id, area_name, pdf_path, uploaded_by, sort_order)
            VALUES (?, ?, ?, ?, ?)
        ');

        $stmt->execute([
            $data['venue_id'],
            $data['area_name'],
            $data['pdf_path'],
            $data['uploaded_by'],
            $data['sort_order'] ?? 0
        ]);

        return (int) $pdo->lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        $pdo = Database::connect();

        $fields = [];
        $values = [];

        if (isset($data['venue_id'])) {
            $fields[] = 'venue_id = ?';
            $values[] = $data['venue_id'];
        }
        if (isset($data['area_name'])) {
            $fields[] = 'area_name = ?';
            $values[] = $data['area_name'];
        }
        if (isset($data['pdf_path'])) {
            $fields[] = 'pdf_path = ?';
            $values[] = $data['pdf_path'];
        }
        if (isset($data['sort_order'])) {
            $fields[] = 'sort_order = ?';
            $values[] = $data['sort_order'];
        }

        if (empty($fields)) {
            return false;
        }

        $values[] = $id;
        $sql = 'UPDATE areas SET ' . implode(', ', $fields) . ' WHERE id = ?';
        $stmt = $pdo->prepare($sql);
        return $stmt->execute($values);
    }

    public static function updateCalibration(int $id, array $calibration): bool
    {
        $pdo = Database::connect();
        $stmt = $pdo->prepare('
            UPDATE areas
            SET cal_x1 = ?, cal_y1 = ?, cal_x2 = ?, cal_y2 = ?, cal_distance_m = ?
            WHERE id = ?
        ');

        return $stmt->execute([
            $calibration['x1'],
            $calibration['y1'],
            $calibration['x2'],
            $calibration['y2'],
            $calibration['distance_m'],
            $id
        ]);
    }

    public static function delete(int $id): bool
    {
        // Get area to delete PDF file
        $area = self::find($id);
        if ($area && file_exists($area['pdf_path'])) {
            unlink($area['pdf_path']);
        }

        $pdo = Database::connect();
        $stmt = $pdo->prepare('DELETE FROM areas WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public static function isCalibrated(int $id): bool
    {
        $area = self::find($id);
        return $area && !empty($area['cal_distance_m']);
    }
}
