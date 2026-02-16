<?php
// Web-accessible migration runner
require __DIR__ . '/../config/database.php';

// Simple security: only allow in development or with a secret key
$secret = $_GET['secret'] ?? '';
if ($secret !== 'fmchecks-migrate-2026') {
    die('Access denied');
}

$migrationFile = __DIR__ . '/../database/migrations/001_add_checkpoint_customization.sql';

if (!file_exists($migrationFile)) {
    die("Migration file not found");
}

$sql = file_get_contents($migrationFile);

try {
    $pdo = Database::connect();
    $pdo->exec($sql);
    echo "<h2>✓ Migration applied successfully!</h2>";
    echo "<pre>" . htmlspecialchars($sql) . "</pre>";
    echo "<p><a href='/'>Go to application</a></p>";
} catch (PDOException $e) {
    echo "<h2>✗ Migration failed</h2>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    exit(1);
}
