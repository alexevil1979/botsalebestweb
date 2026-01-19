<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Core\Config;
use Core\Database;

Config::load(__DIR__ . '/../.env');

echo "Optimizing services structure - removing duplicates and consolidating categories...\n\n";

try {
    // Маппинг старых категорий к новым
    $categoryMapping = [
        // Старые категории без category -> новые категории
        3 => 75,  // Интернет-магазин -> Веб-разработка (ID 75)
        4 => 75,  // Веб-приложение -> Веб-разработка (ID 75)
        17 => 75, // Лендинг -> Веб-разработка (ID 75)
        18 => 75, // Корпоративный сайт -> Веб-разработка (ID 75)
        
        // Старые категории с category -> новые категории
        21 => 75, // Веб-разработка (старая) -> Веб-разработка (новая)
        22 => 79, // Безопасность и SSL (старая) -> Безопасность и SSL (новая)
        23 => 77, // Дизайн и UI/UX (старая) -> Дизайн и UI/UX (новая)
        24 => 78, // SEO и маркетинг (старая) -> SEO и маркетинг (новая)
        25 => 76, // Автоматизация и интеграции -> Чат-боты и автоматизация
        26 => 82, // Прочие услуги (старая) -> Прочие услуги (новая)
    ];
    
    // Шаг 1: Переносим услуги от старых категорий к новым
    echo "Step 1: Moving services from old categories to new ones...\n";
    foreach ($categoryMapping as $oldCategoryId => $newCategoryId) {
        $count = Database::fetch(
            "SELECT COUNT(*) as count FROM services WHERE parent_id = ?",
            [$oldCategoryId]
        )['count'] ?? 0;
        
        if ($count > 0) {
            Database::execute(
                "UPDATE services SET parent_id = ? WHERE parent_id = ?",
                [$newCategoryId, $oldCategoryId]
            );
            echo "  ✓ Moved {$count} services from category {$oldCategoryId} to {$newCategoryId}\n";
        }
    }
    
    // Шаг 2: Объединяем дубликаты услуг (по названию и parent_id)
    echo "\nStep 2: Merging duplicate services...\n";
    
    // Находим дубликаты услуг
    $duplicates = Database::fetchAll(
        "SELECT name, parent_id, GROUP_CONCAT(id ORDER BY id) as ids, COUNT(*) as cnt
         FROM services
         WHERE parent_id IS NOT NULL
         GROUP BY name, parent_id
         HAVING cnt > 1"
    );
    
    foreach ($duplicates as $dup) {
        $ids = explode(',', $dup['ids']);
        $keepId = (int)$ids[0]; // Оставляем первый ID
        $deleteIds = array_slice($ids, 1); // Остальные удаляем
        
        // Обновляем ссылки в таблице leads
        foreach ($deleteIds as $deleteId) {
            Database::execute(
                "UPDATE leads SET service_id = ? WHERE service_id = ?",
                [$keepId, $deleteId]
            );
        }
        
        // Удаляем дубликаты
        $idsStr = implode(',', $deleteIds);
        Database::execute("DELETE FROM services WHERE id IN ({$idsStr})");
        
        echo "  ✓ Merged duplicate service '{$dup['name']}' (kept ID {$keepId}, deleted IDs: {$idsStr})\n";
    }
    
    // Шаг 3: Удаляем старые категории
    echo "\nStep 3: Deleting old categories...\n";
    $oldCategoryIds = array_keys($categoryMapping);
    $idsStr = implode(',', $oldCategoryIds);
    
    // Проверяем, что у них нет услуг
    $servicesCount = Database::fetch(
        "SELECT COUNT(*) as count FROM services WHERE parent_id IN ({$idsStr})"
    )['count'] ?? 0;
    
    if ($servicesCount == 0) {
        Database::execute("DELETE FROM services WHERE id IN ({$idsStr})");
        echo "  ✓ Deleted old categories: {$idsStr}\n";
    } else {
        echo "  ⚠ Warning: {$servicesCount} services still reference old categories\n";
    }
    
    // Шаг 4: Объединяем дубликаты категорий (по названию)
    echo "\nStep 4: Merging duplicate categories...\n";
    
    $duplicateCategories = Database::fetchAll(
        "SELECT name, category, GROUP_CONCAT(id ORDER BY id) as ids, COUNT(*) as cnt
         FROM services
         WHERE parent_id IS NULL AND category IS NOT NULL
         GROUP BY name, category
         HAVING cnt > 1"
    );
    
    foreach ($duplicateCategories as $dup) {
        $ids = explode(',', $dup['ids']);
        $keepId = (int)$ids[0];
        $deleteIds = array_slice($ids, 1);
        
        // Переносим услуги от дубликатов к основной категории
        foreach ($deleteIds as $deleteId) {
            Database::execute(
                "UPDATE services SET parent_id = ? WHERE parent_id = ?",
                [$keepId, $deleteId]
            );
        }
        
        // Удаляем дубликаты категорий
        $idsStr = implode(',', $deleteIds);
        Database::execute("DELETE FROM services WHERE id IN ({$idsStr})");
        
        echo "  ✓ Merged duplicate category '{$dup['name']}' (kept ID {$keepId}, deleted IDs: {$idsStr})\n";
    }
    
    // Шаг 5: Удаляем категории без category (которые не являются категориями)
    echo "\nStep 5: Cleaning up invalid categories...\n";
    
    $invalidCategories = Database::fetchAll(
        "SELECT id, name FROM services 
         WHERE parent_id IS NULL 
         AND category IS NULL 
         AND (price_from IS NOT NULL OR price_to IS NOT NULL)"
    );
    
    foreach ($invalidCategories as $invalid) {
        // Это не категория, а услуга - переносим в Веб-разработку
        Database::execute(
            "UPDATE services SET parent_id = 75, category = 'web_development' WHERE id = ?",
            [$invalid['id']]
        );
        echo "  ✓ Moved service '{$invalid['name']}' (ID {$invalid['id']}) to Веб-разработка\n";
    }
    
    // Шаг 6: Оптимизируем sort_order для категорий
    echo "\nStep 6: Optimizing sort_order for categories...\n";
    
    $categories = Database::fetchAll(
        "SELECT id, category FROM services 
         WHERE parent_id IS NULL AND category IS NOT NULL 
         ORDER BY category"
    );
    
    $sortOrder = 1;
    foreach ($categories as $cat) {
        Database::execute(
            "UPDATE services SET sort_order = ? WHERE id = ?",
            [$sortOrder, $cat['id']]
        );
        $sortOrder += 10;
    }
    
    echo "  ✓ Updated sort_order for " . count($categories) . " categories\n";
    
    // Шаг 7: Проверяем целостность данных
    echo "\nStep 7: Verifying data integrity...\n";
    
    // Проверяем услуги с несуществующими parent_id
    $orphanedServices = Database::fetchAll(
        "SELECT s.id, s.name, s.parent_id 
         FROM services s
         LEFT JOIN services p ON s.parent_id = p.id
         WHERE s.parent_id IS NOT NULL AND p.id IS NULL"
    );
    
    if (!empty($orphanedServices)) {
        echo "  ⚠ Warning: Found " . count($orphanedServices) . " orphaned services\n";
        foreach ($orphanedServices as $orphan) {
            echo "    - Service ID {$orphan['id']}: '{$orphan['name']}' (parent_id: {$orphan['parent_id']})\n";
            // Переносим в Веб-разработку
            Database::execute(
                "UPDATE services SET parent_id = 75 WHERE id = ?",
                [$orphan['id']]
            );
            echo "      → Moved to Веб-разработка\n";
        }
    } else {
        echo "  ✓ No orphaned services found\n";
    }
    
    // Финальная статистика
    echo "\n" . str_repeat("=", 60) . "\n";
    echo "Final Statistics:\n";
    echo str_repeat("=", 60) . "\n";
    
    $totalCategories = Database::fetch("SELECT COUNT(*) as count FROM services WHERE parent_id IS NULL AND category IS NOT NULL")['count'] ?? 0;
    $totalServices = Database::fetch("SELECT COUNT(*) as count FROM services WHERE parent_id IS NOT NULL")['count'] ?? 0;
    $totalSpecialists = Database::fetch("SELECT COUNT(*) as count FROM services WHERE parent_id = 55")['count'] ?? 0;
    
    echo "Total categories: {$totalCategories}\n";
    echo "Total services: {$totalServices}\n";
    echo "Specialist services: {$totalSpecialists}\n";
    
    echo "\n✅ Optimization completed successfully!\n";
    
} catch (Exception $e) {
    echo "\n✗ Optimization failed: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
