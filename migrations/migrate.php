<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Core\Config;
use Core\Database;

Config::load(__DIR__ . '/../.env');

echo "Running database migrations...\n";

try {
    $sqlFile = __DIR__ . '/../schema.sql';
    
    if (!file_exists($sqlFile)) {
        throw new \RuntimeException("Schema file not found: {$sqlFile}");
    }
    
    $sql = file_get_contents($sqlFile);
    
    if (empty($sql)) {
        throw new \RuntimeException("Schema file is empty: {$sqlFile}");
    }
    
    echo "Schema file loaded: " . strlen($sql) . " bytes\n";
    
    // Remove comments and clean up
    $sql = preg_replace('/--.*$/m', '', $sql); // Remove single-line comments
    $sql = preg_replace('/\/\*.*?\*\//s', '', $sql); // Remove multi-line comments
    
    // Split by semicolon and execute each statement
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function($stmt) {
            $stmt = trim($stmt);
            return !empty($stmt) && 
                   strlen($stmt) > 10 && // Minimum statement length
                   !preg_match('/^SET\s+/i', $stmt) &&
                   !preg_match('/^\/\*/', $stmt);
        }
    );
    
    echo "Found " . count($statements) . " SQL statements to execute\n\n";

    $pdo = Database::getInstance();
    
    $executed = 0;
    $errors = 0;
    $skipped = 0;
    
    foreach ($statements as $index => $statement) {
        $statement = trim($statement);
        if (empty($statement)) {
            continue;
        }
        
        // Show first 50 chars of statement for debugging
        $preview = substr($statement, 0, 50) . (strlen($statement) > 50 ? '...' : '');
        
        try {
            $pdo->exec($statement);
            $executed++;
            echo "✓ [" . ($index + 1) . "] Executed: {$preview}\n";
        } catch (\PDOException $e) {
            // Ignore "table already exists" errors
            if (strpos($e->getMessage(), 'already exists') !== false) {
                $skipped++;
                echo "ℹ [" . ($index + 1) . "] Already exists: {$preview}\n";
            } else {
                $errors++;
                echo "✗ [" . ($index + 1) . "] Error: {$preview}\n";
                echo "   Message: " . $e->getMessage() . "\n";
            }
        }
    }
    
    echo "\n";

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
