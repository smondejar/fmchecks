<?php

class VenuePlan
{
    public static function findByVenue(int $venueId): array
    {
        $pdo = Database::connect();
        $stmt = $pdo->prepare('
            SELECT vp.*, u.full_name as uploader_name
            FROM venue_plans vp
            LEFT JOIN users u ON vp.uploaded_by = u.id
            WHERE vp.venue_id = ?
            ORDER BY vp.name ASC
        ');
        $stmt->execute([$venueId]);
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $pdo = Database::connect();
        $stmt = $pdo->prepare('SELECT * FROM venue_plans WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function create(array $data): int
    {
        $pdo = Database::connect();
        $stmt = $pdo->prepare('
            INSERT INTO venue_plans (venue_id, name, pdf_path, uploaded_by)
            VALUES (?, ?, ?, ?)
        ');
        $stmt->execute([
            $data['venue_id'],
            $data['name'],
            $data['pdf_path'],
            $data['uploaded_by'],
        ]);
        return (int) $pdo->lastInsertId();
    }

    public static function delete(int $id): ?string
    {
        $pdo = Database::connect();
        $stmt = $pdo->prepare('SELECT pdf_path FROM venue_plans WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }
        $pdo->prepare('DELETE FROM venue_plans WHERE id = ?')->execute([$id]);
        return $row['pdf_path']; // return path so caller can delete the file
    }

    /** Return all venue plans indexed by venue_id for use in JS. */
    public static function allGroupedByVenue(): array
    {
        $pdo = Database::connect();
        $stmt = $pdo->query('SELECT id, venue_id, name, pdf_path FROM venue_plans ORDER BY name ASC');
        $rows = $stmt->fetchAll();
        $grouped = [];
        foreach ($rows as $r) {
            $grouped[$r['venue_id']][] = $r;
        }
        return $grouped;
    }
}
