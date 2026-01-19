<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Core\Config;
use Core\Database;
use Core\Service;

Config::load(__DIR__ . '/../.env');

echo "Filling empty categories with services...\n\n";

// Находим все категории (записи без parent_id)
$categories = Database::fetchAll(
    "SELECT id, name, category FROM services WHERE parent_id IS NULL ORDER BY id"
);

$servicesToAdd = [];

foreach ($categories as $category) {
    // Считаем количество услуг в категории
    $servicesCount = Database::fetch(
        "SELECT COUNT(*) as count FROM services WHERE parent_id = ?",
        [$category['id']]
    )['count'] ?? 0;
    
    echo "Category: {$category['name']} (ID: {$category['id']}) - {$servicesCount} services\n";
    
    // Если услуг нет, добавляем их
    if ($servicesCount == 0) {
        $categoryName = $category['name'];
        $categoryId = $category['id'];
        
        // Определяем услуги в зависимости от названия категории
        if (stripos($categoryName, 'Лендинг') !== false) {
            // Услуги для категории "Лендинг"
            $servicesToAdd[] = [
                'name' => 'Создание продающего лендинга',
                'parent_id' => $categoryId,
                'description' => 'Разработка одностраничного сайта с уникальным дизайном, адаптивной версткой, формами захвата лидов, интеграцией аналитики. Включает копирайт, дизайн, верстку, базовую SEO-оптимизацию.',
                'price_from' => 5000,
                'price_to' => 15000,
                'active' => 1,
                'sort_order' => 1
            ];
            $servicesToAdd[] = [
                'name' => 'Лендинг-презентация проекта',
                'parent_id' => $categoryId,
                'description' => 'Одностраничный сайт для презентации проекта, продукта или услуги. Включает hero-секцию, описание, преимущества, портфолио/кейсы, контактную форму.',
                'price_from' => 3000,
                'price_to' => 8000,
                'active' => 1,
                'sort_order' => 2
            ];
            $servicesToAdd[] = [
                'name' => 'Лендинг для акции/промо',
                'parent_id' => $categoryId,
                'description' => 'Промо-страница для запуска акции, скидки или специального предложения. Быстрая разработка, интеграция с рекламными кампаниями, отслеживание конверсий.',
                'price_from' => 2000,
                'price_to' => 6000,
                'active' => 1,
                'sort_order' => 3
            ];
            $servicesToAdd[] = [
                'name' => 'Лендинг с A/B тестированием',
                'parent_id' => $categoryId,
                'description' => 'Продающий лендинг с настройкой A/B тестов, аналитикой конверсий, оптимизацией элементов (заголовки, CTA, формы). Включает несколько вариантов дизайна.',
                'price_from' => 8000,
                'price_to' => 20000,
                'active' => 1,
                'sort_order' => 4
            ];
            $servicesToAdd[] = [
                'name' => 'Редизайн существующего лендинга',
                'parent_id' => $categoryId,
                'description' => 'Обновление дизайна и структуры существующего лендинга для повышения конверсии. Анализ текущего лендинга, предложения по улучшению, реализация.',
                'price_from' => 3000,
                'price_to' => 10000,
                'active' => 1,
                'sort_order' => 5
            ];
            $servicesToAdd[] = [
                'name' => 'Лендинг для мероприятий/вебинаров',
                'parent_id' => $categoryId,
                'description' => 'Специализированный лендинг для регистрации на мероприятие, вебинар или онлайн-курс. Интеграция с календарем, формами регистрации, напоминаниями.',
                'price_from' => 4000,
                'price_to' => 12000,
                'active' => 1,
                'sort_order' => 6
            ];
        } elseif (stripos($categoryName, 'Корпоративный') !== false) {
            // Услуги для категории "Корпоративный сайт"
            $servicesToAdd[] = [
                'name' => 'Корпоративный сайт-визитка (5-7 страниц)',
                'parent_id' => $categoryId,
                'description' => 'Небольшой корпоративный сайт с основными разделами: о компании, услуги, контакты. Адаптивный дизайн, контактные формы, базовое SEO.',
                'price_from' => 15000,
                'price_to' => 35000,
                'active' => 1,
                'sort_order' => 1
            ];
            $servicesToAdd[] = [
                'name' => 'Корпоративный сайт с блогом (10-15 страниц)',
                'parent_id' => $categoryId,
                'description' => 'Корпоративный сайт с разделом блога, новостями, галереей, формой обратной связи. CMS для управления контентом, мультиязычность, интеграция с соцсетями.',
                'price_from' => 35000,
                'price_to' => 80000,
                'active' => 1,
                'sort_order' => 2
            ];
            $servicesToAdd[] = [
                'name' => 'Корпоративный портал (20+ страниц)',
                'parent_id' => $categoryId,
                'description' => 'Многостраничный корпоративный портал с личными кабинетами, интеграцией CRM/ERP, системой бронирования, мультиязычностью, API для интеграций.',
                'price_from' => 80000,
                'price_to' => 200000,
                'active' => 1,
                'sort_order' => 3
            ];
            $servicesToAdd[] = [
                'name' => 'Редизайн корпоративного сайта',
                'parent_id' => $categoryId,
                'description' => 'Обновление дизайна и структуры существующего корпоративного сайта. Анализ текущего сайта, UX/UI улучшения, адаптация под современные стандарты.',
                'price_from' => 25000,
                'price_to' => 70000,
                'active' => 1,
                'sort_order' => 4
            ];
            $servicesToAdd[] = [
                'name' => 'Корпоративный сайт с каталогом продукции',
                'parent_id' => $categoryId,
                'description' => 'Корпоративный сайт с каталогом продукции/услуг, фильтрами, поиском, детальными страницами товаров. Интеграция с системами учета, экспорт/импорт данных.',
                'price_from' => 50000,
                'price_to' => 150000,
                'active' => 1,
                'sort_order' => 5
            ];
        } elseif (stripos($categoryName, 'Интернет-магазин') !== false || stripos($categoryName, 'E-commerce') !== false) {
            // Услуги для категории "Интернет-магазин"
            $servicesToAdd[] = [
                'name' => 'Интернет-магазин базовый (до 100 товаров)',
                'parent_id' => $categoryId,
                'description' => 'Интернет-магазин с каталогом товаров, корзиной, оформлением заказа, интеграцией платежных систем (ЮKassa, Stripe), базовой админ-панелью.',
                'price_from' => 50000,
                'price_to' => 120000,
                'active' => 1,
                'sort_order' => 1
            ];
            $servicesToAdd[] = [
                'name' => 'Интернет-магазин средний (100-500 товаров)',
                'parent_id' => $categoryId,
                'description' => 'Расширенный магазин с фильтрами, поиском, сравнением товаров, отзывами, системами скидок и промокодов, интеграцией с логистикой, CRM.',
                'price_from' => 120000,
                'price_to' => 300000,
                'active' => 1,
                'sort_order' => 2
            ];
            $servicesToAdd[] = [
                'name' => 'Интернет-магазин премиум (500+ товаров)',
                'parent_id' => $categoryId,
                'description' => 'Масштабируемый интернет-магазин с мультивалютностью, мультиязычностью, интеграцией с ERP, системами складского учета, аналитикой продаж, мобильным приложением.',
                'price_from' => 300000,
                'price_to' => 800000,
                'active' => 1,
                'sort_order' => 3
            ];
            $servicesToAdd[] = [
                'name' => 'Миграция интернет-магазина',
                'parent_id' => $categoryId,
                'description' => 'Перенос интернет-магазина на новую платформу с сохранением всех данных (товары, заказы, клиенты), SEO-структуры, настроек.',
                'price_from' => 40000,
                'price_to' => 100000,
                'active' => 1,
                'sort_order' => 4
            ];
            $servicesToAdd[] = [
                'name' => 'Доработка интернет-магазина',
                'parent_id' => $categoryId,
                'description' => 'Добавление новых функций в существующий интернет-магазин: новые способы оплаты, интеграции, улучшение UX, оптимизация производительности.',
                'price_from' => 20000,
                'price_to' => 80000,
                'active' => 1,
                'sort_order' => 5
            ];
        } elseif (stripos($categoryName, 'Веб-приложение') !== false || stripos($categoryName, 'Web Application') !== false) {
            // Услуги для категории "Веб-приложение"
            $servicesToAdd[] = [
                'name' => 'Веб-приложение MVP (минимальный продукт)',
                'parent_id' => $categoryId,
                'description' => 'Разработка минимально жизнеспособного веб-приложения с основным функционалом для тестирования гипотезы. Быстрая разработка, базовый дизайн, основные функции.',
                'price_from' => 100000,
                'price_to' => 250000,
                'active' => 1,
                'sort_order' => 1
            ];
            $servicesToAdd[] = [
                'name' => 'Веб-приложение полный функционал',
                'parent_id' => $categoryId,
                'description' => 'Разработка полнофункционального веб-приложения с личными кабинетами, админ-панелью, API, интеграциями, системой уведомлений, аналитикой.',
                'price_from' => 250000,
                'price_to' => 600000,
                'active' => 1,
                'sort_order' => 2
            ];
            $servicesToAdd[] = [
                'name' => 'SPA (Single Page Application)',
                'parent_id' => $categoryId,
                'description' => 'Одностраничное веб-приложение на React/Vue/Angular с динамической загрузкой контента, API интеграцией, современным UX, адаптивным дизайном.',
                'price_from' => 150000,
                'price_to' => 400000,
                'active' => 1,
                'sort_order' => 3
            ];
            $servicesToAdd[] = [
                'name' => 'PWA (Progressive Web App)',
                'parent_id' => $categoryId,
                'description' => 'Прогрессивное веб-приложение с возможностью работы офлайн, push-уведомлениями, установкой на устройство. Работает как нативное приложение.',
                'price_from' => 180000,
                'price_to' => 450000,
                'active' => 1,
                'sort_order' => 4
            ];
            $servicesToAdd[] = [
                'name' => 'Веб-приложение с реальным временем',
                'parent_id' => $categoryId,
                'description' => 'Веб-приложение с WebSocket интеграцией для работы в реальном времени: чаты, уведомления, совместная работа, live-обновления данных.',
                'price_from' => 200000,
                'price_to' => 500000,
                'active' => 1,
                'sort_order' => 5
            ];
            $servicesToAdd[] = [
                'name' => 'Микросервисная архитектура',
                'parent_id' => $categoryId,
                'description' => 'Разработка веб-приложения на основе микросервисной архитектуры для масштабируемости и надежности. Docker, Kubernetes, API Gateway.',
                'price_from' => 300000,
                'price_to' => 800000,
                'active' => 1,
                'sort_order' => 6
            ];
        }
    }
}

echo "\n";

// Добавляем услуги
if (!empty($servicesToAdd)) {
    echo "Adding " . count($servicesToAdd) . " services to empty categories...\n\n";
    
    foreach ($servicesToAdd as $service) {
        try {
            Service::create($service);
            echo "✓ Added: {$service['name']} (parent_id: {$service['parent_id']})\n";
        } catch (Exception $e) {
            echo "✗ Failed to add {$service['name']}: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n✅ Migration completed successfully!\n";
    echo "Total services added: " . count($servicesToAdd) . "\n";
} else {
    echo "No empty categories found or all categories already have services.\n";
}
