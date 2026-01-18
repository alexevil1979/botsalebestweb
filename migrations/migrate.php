<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Core\Config;
use Core\Database;

Config::load(__DIR__ . '/../.env');

echo "Running database migrations...\n";

try {
    $sql = file_get_contents(__DIR__ . '/../schema.sql');
    
    // Split by semicolon and execute each statement
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function($stmt) {
            return !empty($stmt) && 
                   !preg_match('/^--/', $stmt) && 
                   !preg_match('/^SET/', $stmt) &&
                   !preg_match('/^\/\*/', $stmt);
        }
    );

    $pdo = Database::getInstance();
    
    foreach ($statements as $statement) {
        if (empty(trim($statement))) {
            continue;
        }
        try {
            $pdo->exec($statement);
            echo "✓ Executed statement\n";
        } catch (\PDOException $e) {
            // Ignore "table already exists" errors
            if (strpos($e->getMessage(), 'already exists') === false) {
                echo "⚠ Warning: " . $e->getMessage() . "\n";
            }
        }
    }

    echo "✓ Migrations completed!\n";
} catch (\Exception $e) {
    echo "✗ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
