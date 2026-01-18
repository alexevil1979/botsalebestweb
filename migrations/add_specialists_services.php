<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Core\Config;
use Core\Database;
use Core\Service;

Config::load(__DIR__ . '/../.env');

echo "Adding specialists services with hourly rates...\n";

// Услуги узких специалистов с почасовой оплатой
$specialists = [
    [
        'name' => 'Узкие специалисты',
        'category' => 'specialists',
        'parent_id' => null,
        'description' => 'Услуги узких специалистов с почасовой оплатой',
        'price_from' => null,
        'price_to' => null,
        'hourly_rate' => null,
        'is_hourly' => 0,
        'active' => 1,
        'sort_order' => 9
    ],
    
    // Frontend разработчики
    [
        'name' => 'Frontend разработчик (React/Vue)',
        'category' => 'specialists',
        'parent_id' => null,
        'description' => 'Разработка пользовательских интерфейсов на React, Vue.js, Angular. Компонентная разработка, состояние приложения, интеграция с API.',
        'price_from' => null,
        'price_to' => null,
        'hourly_rate' => 25,
        'is_hourly' => 1,
        'active' => 1,
        'sort_order' => 200
    ],
    [
        'name' => 'Frontend разработчик (Senior)',
        'category' => 'specialists',
        'parent_id' => null,
        'description' => 'Опытный frontend разработчик с глубокими знаниями архитектуры, оптимизации производительности, TypeScript, тестирования.',
        'price_from' => null,
        'price_to' => null,
        'hourly_rate' => 50,
        'is_hourly' => 1,
        'active' => 1,
        'sort_order' => 201
    ],
    
    // Backend разработчики
    [
        'name' => 'Backend разработчик (PHP/Node.js)',
        'category' => 'specialists',
        'parent_id' => null,
        'description' => 'Разработка серверной части приложений на PHP, Node.js, Python. API, базы данных, интеграции, архитектура.',
        'price_from' => null,
        'price_to' => null,
        'hourly_rate' => 30,
        'is_hourly' => 1,
        'active' => 1,
        'sort_order' => 210
    ],
    [
        'name' => 'Backend разработчик (Senior)',
        'category' => 'specialists',
        'parent_id' => null,
        'description' => 'Опытный backend разработчик: микросервисы, масштабирование, безопасность, оптимизация производительности, DevOps.',
        'price_from' => null,
        'price_to' => null,
        'hourly_rate' => 60,
        'is_hourly' => 1,
        'active' => 1,
        'sort_order' => 211
    ],
    
    // Fullstack разработчики
    [
        'name' => 'Fullstack разработчик',
        'category' => 'specialists',
        'parent_id' => null,
        'description' => 'Разработка полного цикла: frontend и backend, базы данных, деплой, интеграции. Универсальный специалист.',
        'price_from' => null,
        'price_to' => null,
        'hourly_rate' => 35,
        'is_hourly' => 1,
        'active' => 1,
        'sort_order' => 220
    ],
    [
        'name' => 'Fullstack разработчик (Senior)',
        'category' => 'specialists',
        'parent_id' => null,
        'description' => 'Опытный fullstack разработчик с экспертизой в архитектуре, масштабировании, безопасности, CI/CD.',
        'price_from' => null,
        'price_to' => null,
        'hourly_rate' => 70,
        'is_hourly' => 1,
        'active' => 1,
        'sort_order' => 221
    ],
    
    // UI/UX дизайнеры
    [
        'name' => 'UI/UX дизайнер',
        'category' => 'specialists',
        'parent_id' => null,
        'description' => 'Дизайн интерфейсов, прототипирование, пользовательский опыт, исследования, тестирование удобства использования.',
        'price_from' => null,
        'price_to' => null,
        'hourly_rate' => 20,
        'is_hourly' => 1,
        'active' => 1,
        'sort_order' => 230
    ],
    [
        'name' => 'UI/UX дизайнер (Senior)',
        'category' => 'specialists',
        'parent_id' => null,
        'description' => 'Опытный дизайнер: стратегия дизайна, дизайн-системы, сложные интерфейсы, руководство командой.',
        'price_from' => null,
        'price_to' => null,
        'hourly_rate' => 45,
        'is_hourly' => 1,
        'active' => 1,
        'sort_order' => 231
    ],
    
    // SEO специалисты
    [
        'name' => 'SEO специалист',
        'category' => 'specialists',
        'parent_id' => null,
        'description' => 'Поисковая оптимизация: аудит, техническое SEO, контент-стратегия, аналитика, локальное SEO.',
        'price_from' => null,
        'price_to' => null,
        'hourly_rate' => 15,
        'is_hourly' => 1,
        'active' => 1,
        'sort_order' => 240
    ],
    [
        'name' => 'SEO специалист (Senior)',
        'category' => 'specialists',
        'parent_id' => null,
        'description' => 'Опытный SEO: стратегия продвижения, международное SEO, сложные проекты, консультации, команда.',
        'price_from' => null,
        'price_to' => null,
        'hourly_rate' => 40,
        'is_hourly' => 1,
        'active' => 1,
        'sort_order' => 241
    ],
    
    // DevOps инженеры
    [
        'name' => 'DevOps инженер',
        'category' => 'specialists',
        'parent_id' => null,
        'description' => 'Настройка инфраструктуры, CI/CD, контейнеризация, мониторинг, автоматизация деплоя, облачные сервисы.',
        'price_from' => null,
        'price_to' => null,
        'hourly_rate' => 35,
        'is_hourly' => 1,
        'active' => 1,
        'sort_order' => 250
    ],
    [
        'name' => 'DevOps инженер (Senior)',
        'category' => 'specialists',
        'parent_id' => null,
        'description' => 'Опытный DevOps: архитектура инфраструктуры, масштабирование, безопасность, Kubernetes, облачная архитектура.',
        'price_from' => null,
        'price_to' => null,
        'hourly_rate' => 65,
        'is_hourly' => 1,
        'active' => 1,
        'sort_order' => 251
    ],
    
    // QA инженеры
    [
        'name' => 'QA инженер',
        'category' => 'specialists',
        'parent_id' => null,
        'description' => 'Тестирование веб-приложений: функциональное, регрессионное, интеграционное тестирование, баг-репорты.',
        'price_from' => null,
        'price_to' => null,
        'hourly_rate' => 18,
        'is_hourly' => 1,
        'active' => 1,
        'sort_order' => 260
    ],
    [
        'name' => 'QA инженер (Автоматизация)',
        'category' => 'specialists',
        'parent_id' => null,
        'description' => 'Автоматизация тестирования: написание тестов, настройка CI/CD для тестов, фреймворки автоматизации.',
        'price_from' => null,
        'price_to' => null,
        'hourly_rate' => 30,
        'is_hourly' => 1,
        'active' => 1,
        'sort_order' => 261
    ],
    
    // Копирайтеры
    [
        'name' => 'Копирайтер / Контент-райтер',
        'category' => 'specialists',
        'parent_id' => null,
        'description' => 'Написание текстов для сайтов, блогов, рекламы. SEO-копирайтинг, контент-стратегия, редактирование.',
        'price_from' => null,
        'price_to' => null,
        'hourly_rate' => 12,
        'is_hourly' => 1,
        'active' => 1,
        'sort_order' => 270
    ],
    [
        'name' => 'Копирайтер (Senior)',
        'category' => 'specialists',
        'parent_id' => null,
        'description' => 'Опытный копирайтер: стратегия контента, брендинг, сложные тексты, руководство контент-командой.',
        'price_from' => null,
        'price_to' => null,
        'hourly_rate' => 25,
        'is_hourly' => 1,
        'active' => 1,
        'sort_order' => 271
    ],
    
    // Специалисты по автоматизации
    [
        'name' => 'Специалист по автоматизации',
        'category' => 'specialists',
        'parent_id' => null,
        'description' => 'Автоматизация бизнес-процессов: скрипты, интеграции, webhooks, API, автоматизация задач.',
        'price_from' => null,
        'price_to' => null,
        'hourly_rate' => 28,
        'is_hourly' => 1,
        'active' => 1,
        'sort_order' => 280
    ],
    
    // Специалисты по безопасности
    [
        'name' => 'Специалист по безопасности',
        'category' => 'specialists',
        'parent_id' => null,
        'description' => 'Аудит безопасности, защита от атак, настройка SSL, соответствие стандартам, консультации по безопасности.',
        'price_from' => null,
        'price_to' => null,
        'hourly_rate' => 40,
        'is_hourly' => 1,
        'active' => 1,
        'sort_order' => 290
    ],
    
    // Графические дизайнеры
    [
        'name' => 'Графический дизайнер',
        'category' => 'specialists',
        'parent_id' => null,
        'description' => 'Создание графики, баннеров, иллюстраций, обработка изображений, брендинг, визуальный контент.',
        'price_from' => null,
        'price_to' => null,
        'hourly_rate' => 15,
        'is_hourly' => 1,
        'active' => 1,
        'sort_order' => 300
    ],
];

try {
    $categoryIds = [];
    
    // Сначала создаем категорию
    foreach ($specialists as $specialist) {
        if ($specialist['price_from'] === null && $specialist['price_to'] === null && $specialist['hourly_rate'] === null) {
            // Это категория
            $id = Service::create($specialist);
            $categoryIds[$specialist['category']] = $id;
            echo "✓ Created category: {$specialist['name']}\n";
        }
    }
    
    // Затем создаем услуги специалистов
    foreach ($specialists as $specialist) {
        if ($specialist['hourly_rate'] !== null) {
            // Это услуга специалиста, привязываем к категории
            $specialist['parent_id'] = $categoryIds[$specialist['category']] ?? null;
            Service::create($specialist);
            echo "✓ Created specialist service: {$specialist['name']} ({$specialist['hourly_rate']} $/час)\n";
        }
    }
    
    echo "\n✅ Migration completed successfully!\n";
    echo "Total categories: " . count($categoryIds) . "\n";
    echo "Total specialist services: " . (count($specialists) - count($categoryIds)) . "\n";
} catch (Exception $e) {
    echo "\n✗ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
