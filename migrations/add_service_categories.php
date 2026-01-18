<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Core\Config;
use Core\Database;

Config::load(__DIR__ . '/../.env');

echo "Adding service categories support...\n";

try {
    // Проверяем существование колонок перед добавлением
    $columns = Database::fetchAll("SHOW COLUMNS FROM services LIKE 'parent_id'");
    if (empty($columns)) {
        $sql = "ALTER TABLE services ADD COLUMN parent_id INT(11) DEFAULT NULL AFTER id";
        Database::execute($sql);
        echo "✓ Added parent_id column\n";
    } else {
        echo "✓ parent_id column already exists\n";
    }

    // Проверяем существование индекса
    $indexes = Database::fetchAll("SHOW INDEX FROM services WHERE Key_name = 'idx_parent_id'");
    if (empty($indexes)) {
        $sql = "ALTER TABLE services ADD INDEX idx_parent_id (parent_id)";
        Database::execute($sql);
        echo "✓ Added index for parent_id\n";
    } else {
        echo "✓ Index idx_parent_id already exists\n";
    }

    // Проверяем существование колонки category
    $columns = Database::fetchAll("SHOW COLUMNS FROM services LIKE 'category'");
    if (empty($columns)) {
        $sql = "ALTER TABLE services ADD COLUMN category VARCHAR(100) DEFAULT NULL AFTER parent_id";
        Database::execute($sql);
        echo "✓ Added category column\n";
    } else {
        echo "✓ category column already exists\n";
    }

    // Проверяем существование индекса category
    $indexes = Database::fetchAll("SHOW INDEX FROM services WHERE Key_name = 'idx_category'");
    if (empty($indexes)) {
        $sql = "ALTER TABLE services ADD INDEX idx_category (category)";
        Database::execute($sql);
        echo "✓ Added index for category\n";
    } else {
        echo "✓ Index idx_category already exists\n";
    }

    echo "\n✅ Migration completed successfully!\n";
} catch (Exception $e) {
    echo "\n✗ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
