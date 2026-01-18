<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Core\Config;
use Core\Database;

Config::load(__DIR__ . '/../.env');

echo "Adding hourly_rate field to services...\n";

try {
    // Проверяем существование колонки
    $columns = Database::fetchAll("SHOW COLUMNS FROM services LIKE 'hourly_rate'");
    if (empty($columns)) {
        $sql = "ALTER TABLE services ADD COLUMN hourly_rate DECIMAL(10,2) DEFAULT NULL AFTER price_to";
        Database::execute($sql);
        echo "✓ Added hourly_rate column\n";
    } else {
        echo "✓ hourly_rate column already exists\n";
    }

    // Проверяем существование колонки is_hourly
    $columns = Database::fetchAll("SHOW COLUMNS FROM services LIKE 'is_hourly'");
    if (empty($columns)) {
        $sql = "ALTER TABLE services ADD COLUMN is_hourly TINYINT(1) DEFAULT 0 AFTER hourly_rate";
        Database::execute($sql);
        echo "✓ Added is_hourly column\n";
    } else {
        echo "✓ is_hourly column already exists\n";
    }

    echo "\n✅ Migration completed successfully!\n";
} catch (Exception $e) {
    echo "\n✗ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
