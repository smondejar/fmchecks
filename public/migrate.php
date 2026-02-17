<?php
// Web-accessible migration runner
require __DIR__ . '/../config/database.php';

// Simple security: only allow with a secret key
$secret = $_GET['secret'] ?? '';
if ($secret !== 'fmchecks-migrate-2026') {
    die('Access denied');
}

$migrations = [
    '001_add_checkpoint_customization.sql',
    '002_add_area_crop.sql',
];

try {
    $pdo = Database::connect();

    foreach ($migrations as $migFile) {
        $path = __DIR__ . '/../database/migrations/' . $migFile;
        if (!file_exists($path)) {
            echo "<p>⚠️ Migration file not found: {$migFile}</p>";
            continue;
        }

        $sql = file_get_contents($path);
        // Split on semicolons (skip empty statements)
        $statements = array_filter(array_map('trim', explode(';', preg_replace('/--[^\n]*/', '', $sql))));

        $ok = true;
        foreach ($statements as $stmt) {
            if (empty($stmt)) continue;
            try {
                $pdo->exec($stmt);
            } catch (PDOException $e) {
                // Ignore "Duplicate column" errors (migration already applied)
                if (str_contains($e->getMessage(), 'Duplicate column') || str_contains($e->getMessage(), 'already exists')) {
                    echo "<p>⏭️ {$migFile}: already applied (skipped)</p>";
                } else {
                    echo "<p>✗ {$migFile} failed: " . htmlspecialchars($e->getMessage()) . "</p>";
                }
                $ok = false;
                break;
            }
        }

        if ($ok) {
            echo "<p>✓ {$migFile} applied successfully</p>";
        }
    }

    echo "<h2>Migration complete</h2>";
    echo "<p><a href='/'>Go to application</a></p>";
} catch (PDOException $e) {
    echo "<h2>✗ Database connection failed</h2>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    exit(1);
}
