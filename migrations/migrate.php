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
    
    $executed = 0;
    $errors = 0;
    
    foreach ($statements as $statement) {
        if (empty(trim($statement))) {
            continue;
        }
        try {
            $pdo->exec($statement);
            $executed++;
            echo "✓ Executed statement\n";
        } catch (\PDOException $e) {
            // Ignore "table already exists" errors
            if (strpos($e->getMessage(), 'already exists') !== false) {
                echo "ℹ Table already exists, skipping\n";
            } else {
                $errors++;
                echo "⚠ Warning: " . $e->getMessage() . "\n";
            }
        }
    }

    // Verify that tables were created
    echo "\nVerifying tables...\n";
    $tables = ['users', 'dialogs', 'messages', 'leads', 'services', 'events'];
    $created = 0;
    
    foreach ($tables as $table) {
        try {
            $result = Database::fetch("SHOW TABLES LIKE '{$table}'");
            if ($result) {
                $created++;
                echo "✓ Table '{$table}' exists\n";
            } else {
                echo "✗ Table '{$table}' NOT found\n";
            }
        } catch (\Exception $e) {
            echo "✗ Error checking table '{$table}': " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n";
    echo "✓ Executed {$executed} statements\n";
    if ($errors > 0) {
        echo "⚠ {$errors} errors occurred\n";
    }
    echo "✓ {$created}/" . count($tables) . " tables verified\n";
    
    if ($created < count($tables)) {
        echo "\n⚠ WARNING: Not all tables were created. Please check the errors above.\n";
        echo "Database: " . Config::get('DB_NAME') . "\n";
        echo "Host: " . Config::get('DB_HOST', '127.0.0.1') . "\n";
    } else {
        echo "✓ Migrations completed successfully!\n";
    }
} catch (\Exception $e) {
    echo "✗ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
