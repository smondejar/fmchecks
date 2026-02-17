<?php
// Simple migration runner
require __DIR__ . '/config/database.php';

$migrationFile = $argv[1] ?? null;

if (!$migrationFile) {
    die("Usage: php run-migration.php <migration-file>\n");
}

$sql = file_get_contents($migrationFile);

try {
    $pdo = Database::connect();
    $pdo->exec($sql);
    echo "✓ Migration applied successfully: " . basename($migrationFile) . "\n";
} catch (PDOException $e) {
    echo "✗ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
