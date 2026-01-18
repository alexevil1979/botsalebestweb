<?php

/**
 * Migration: Add preferred_language column to users table
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Core\Config;
use Core\Database;

Config::load(__DIR__ . '/../.env');

echo "Adding preferred_language column to users table...\n";

try {
    // Check if table exists
    $tableExists = Database::fetch(
        "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES 
         WHERE TABLE_SCHEMA = DATABASE() 
         AND TABLE_NAME = 'users'"
    );

    if (!$tableExists) {
        echo "⚠ Table 'users' does not exist. Please run 'php migrations/migrate.php' first.\n";
        exit(1);
    }

    // Check if column exists
    $result = Database::fetch(
        "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
         WHERE TABLE_SCHEMA = DATABASE() 
         AND TABLE_NAME = 'users' 
         AND COLUMN_NAME = 'preferred_language'"
    );

    if (!$result) {
        Database::execute(
            "ALTER TABLE users ADD COLUMN preferred_language VARCHAR(10) DEFAULT 'ru' AFTER language_code"
        );
        Database::execute(
            "ALTER TABLE users ADD INDEX idx_preferred_language (preferred_language)"
        );
        echo "✓ Column added successfully\n";
    } else {
        echo "✓ Column already exists\n";
    }

    // Update existing users
    Database::execute(
        "UPDATE users SET preferred_language = CASE 
            WHEN language_code IN ('ru', 'ru-RU') THEN 'ru'
            WHEN language_code IN ('en', 'en-US', 'en-GB') THEN 'en'
            WHEN language_code IN ('th', 'th-TH') THEN 'th'
            WHEN language_code LIKE 'zh%' THEN 'zh'
            ELSE 'ru'
        END
        WHERE preferred_language IS NULL OR preferred_language = ''"
    );
    echo "✓ Existing users updated\n";

    echo "✓ Migration completed!\n";
} catch (\Exception $e) {
    echo "✗ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
