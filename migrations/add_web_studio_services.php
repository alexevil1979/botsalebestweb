<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Core\Config;
use Core\Database;
use Core\Service;

Config::load(__DIR__ . '/../.env');

echo "Adding web studio services catalog...\n";

// Каталог услуг с категориями
$services = [
    // 1. Веб-разработка
    [
        'name' => 'Веб-разработка',
        'category' => 'web_development',
        'parent_id' => null,
        'description' => 'Разработка и создание веб-сайтов различных типов',
        'price_from' => null,
        'price_to' => null,
        'active' => 1,
        'sort_order' => 1
    ],
    [
        'name' => 'Лендинг / Сайт-визитка',
        'category' => 'web_development',
        'parent_id' => null, // Будет установлен после создания категории
        'description' => 'Небольшой сайт на 1-5 страниц с простой структурой, контактной формой и адаптивным дизайном',
        'price_from' => 500,
        'price_to' => 2000,
        'active' => 1,
        'sort_order' => 10
    ],
    [
        'name' => 'Корпоративный сайт',
        'category' => 'web_development',
        'parent_id' => null,
        'description' => 'Сайт на 5-20 страниц с брендингом, дизайном, блогом и мобильной адаптацией',
        'price_from' => 3000,
        'price_to' => 10000,
        'active' => 1,
        'sort_order' => 20
    ],
    [
        'name' => 'Интернет-магазин (E-commerce)',
        'category' => 'web_development',
        'parent_id' => null,
        'description' => 'Полнофункциональный магазин с каталогом товаров, корзиной, платежными интеграциями и управлением заказами',
        'price_from' => 5000,
        'price_to' => 75000,
        'active' => 1,
        'sort_order' => 30
    ],
    [
        'name' => 'Перенос / Миграция сайта',
        'category' => 'web_development',
        'parent_id' => null,
        'description' => 'Перенос сайта с одного хостинга на другой: файлы, база данных, настройки, DNS, SSL, SEO-редиректы',
        'price_from' => 100,
        'price_to' => 2500,
        'active' => 1,
        'sort_order' => 40
    ],
    [
        'name' => 'Перенос сайта на новый хостинг',
        'category' => 'web_development',
        'parent_id' => null,
        'description' => 'Полный перенос файлов, базы данных, настройка домена, DNS, проверка работоспособности',
        'price_from' => 300,
        'price_to' => 2000,
        'active' => 1,
        'sort_order' => 41
    ],
    [
        'name' => 'Поддержка и техническое обслуживание',
        'category' => 'web_development',
        'parent_id' => null,
        'description' => 'Регулярные обновления, исправление ошибок, мониторинг, резервное копирование',
        'price_from' => 200,
        'price_to' => 1000,
        'active' => 1,
        'sort_order' => 50
    ],

    // 2. Безопасность и SSL
    [
        'name' => 'Безопасность и SSL',
        'category' => 'security',
        'parent_id' => null,
        'description' => 'Услуги по обеспечению безопасности сайта и настройке SSL-сертификатов',
        'price_from' => null,
        'price_to' => null,
        'active' => 1,
        'sort_order' => 2
    ],
    [
        'name' => 'Установка SSL-сертификата',
        'category' => 'security',
        'parent_id' => null,
        'description' => 'Установка и настройка SSL-сертификата (DV, OV, EV, wildcard), настройка HTTPS, редиректы, устранение проблем смешанного контента',
        'price_from' => 50,
        'price_to' => 500,
        'active' => 1,
        'sort_order' => 60
    ],
    [
        'name' => 'Настройка HTTPS / SSL',
        'category' => 'security',
        'parent_id' => null,
        'description' => 'Полная настройка HTTPS: получение сертификата, установка, настройка перенаправлений и безопасности',
        'price_from' => 50,
        'price_to' => 300,
        'active' => 1,
        'sort_order' => 61
    ],
    [
        'name' => 'Управляемый SSL (OV, EV, wildcard)',
        'category' => 'security',
        'parent_id' => null,
        'description' => 'Полная поддержка SSL: мониторинг, автоматические обновления, управление сертификатами',
        'price_from' => 100,
        'price_to' => 200,
        'active' => 1,
        'sort_order' => 62
    ],
    [
        'name' => 'Аудит безопасности',
        'category' => 'security',
        'parent_id' => null,
        'description' => 'Проверка уязвимостей, слабых мест, настройка заголовков безопасности, правильная конфигурация сервера',
        'price_from' => 200,
        'price_to' => 2000,
        'active' => 1,
        'sort_order' => 70
    ],
    [
        'name' => 'Резервное копирование и восстановление',
        'category' => 'security',
        'parent_id' => null,
        'description' => 'Настройка регулярных бэкапов, восстановление данных, план действий в аварийных ситуациях',
        'price_from' => 100,
        'price_to' => 1000,
        'active' => 1,
        'sort_order' => 80
    ],

    // 3. Дизайн и UI/UX
    [
        'name' => 'Дизайн и UI/UX',
        'category' => 'design',
        'parent_id' => null,
        'description' => 'Дизайн интерфейсов, прототипирование, брендинг',
        'price_from' => null,
        'price_to' => null,
        'active' => 1,
        'sort_order' => 3
    ],
    [
        'name' => 'Прототипирование / Макеты',
        'category' => 'design',
        'parent_id' => null,
        'description' => 'Создание прототипов и макетов интерфейса, пользовательских путей, wireframes',
        'price_from' => 500,
        'price_to' => 1500,
        'active' => 1,
        'sort_order' => 90
    ],
    [
        'name' => 'Адаптивный / Мобильный дизайн',
        'category' => 'design',
        'parent_id' => null,
        'description' => 'Разработка адаптивного дизайна для всех устройств, мобильная версия сайта',
        'price_from' => 300,
        'price_to' => 1000,
        'active' => 1,
        'sort_order' => 100
    ],
    [
        'name' => 'Редизайн существующего сайта',
        'category' => 'design',
        'parent_id' => null,
        'description' => 'Полный редизайн сайта с сохранением функциональности, улучшение UX/UI',
        'price_from' => 500,
        'price_to' => 5000,
        'active' => 1,
        'sort_order' => 110
    ],
    [
        'name' => 'Брендинг (логотип, фирменный стиль)',
        'category' => 'design',
        'parent_id' => null,
        'description' => 'Разработка логотипа, фирменного стиля, гайдлайнов',
        'price_from' => 300,
        'price_to' => 2000,
        'active' => 1,
        'sort_order' => 120
    ],

    // 4. SEO и маркетинг
    [
        'name' => 'SEO и маркетинг',
        'category' => 'seo',
        'parent_id' => null,
        'description' => 'Поисковая оптимизация, контент-маркетинг, настройка рекламы',
        'price_from' => null,
        'price_to' => null,
        'active' => 1,
        'sort_order' => 4
    ],
    [
        'name' => 'SEO-аудит',
        'category' => 'seo',
        'parent_id' => null,
        'description' => 'Технический SEO-аудит: анализ скорости, метатегов, архитектуры сайта, ошибок индексации',
        'price_from' => 200,
        'price_to' => 1000,
        'active' => 1,
        'sort_order' => 130
    ],
    [
        'name' => 'Написание / Оптимизация текстов',
        'category' => 'seo',
        'parent_id' => null,
        'description' => 'SEO-копирайтинг, оптимизация контента, написание текстов для сайта',
        'price_from' => 50,
        'price_to' => 500,
        'active' => 1,
        'sort_order' => 140
    ],
    [
        'name' => 'Ведение блога / Контент-план',
        'category' => 'seo',
        'parent_id' => null,
        'description' => 'Планирование и написание контента для блога, регулярные публикации',
        'price_from' => 100,
        'price_to' => 1000,
        'active' => 1,
        'sort_order' => 150
    ],
    [
        'name' => 'Настройка рекламных кампаний',
        'category' => 'seo',
        'parent_id' => null,
        'description' => 'Настройка Google Ads, Яндекс.Директ, Facebook Ads, настройка пикселей и конверсий',
        'price_from' => 200,
        'price_to' => 2000,
        'active' => 1,
        'sort_order' => 160
    ],
    [
        'name' => 'Настройка аналитики (GA4, Яндекс.Метрика)',
        'category' => 'seo',
        'parent_id' => null,
        'description' => 'Интеграция систем аналитики, настройка целей, отчётов, панелей',
        'price_from' => 50,
        'price_to' => 500,
        'active' => 1,
        'sort_order' => 170
    ],

    // 5. Автоматизация и интеграции
    [
        'name' => 'Автоматизация и интеграции',
        'category' => 'automation',
        'parent_id' => null,
        'description' => 'Автоматизация бизнес-процессов, интеграции с внешними сервисами, веб-скрейпинг',
        'price_from' => null,
        'price_to' => null,
        'active' => 1,
        'sort_order' => 5
    ],
    [
        'name' => 'API-интеграции (CRM, платежи)',
        'category' => 'automation',
        'parent_id' => null,
        'description' => 'Интеграция с CRM-системами, платежными шлюзами, логистикой и другими внешними сервисами',
        'price_from' => 200,
        'price_to' => 2000,
        'active' => 1,
        'sort_order' => 180
    ],
    [
        'name' => 'Автоматизация рабочих процессов',
        'category' => 'automation',
        'parent_id' => null,
        'description' => 'Сквозные автоматические цепочки (формы → CRM → рассылка), скрипты, вебхуки, автоматизация задач',
        'price_from' => 500,
        'price_to' => 2000,
        'active' => 1,
        'sort_order' => 190
    ],
    [
        'name' => 'Парсинг / Веб-скрейпинг данных',
        'category' => 'automation',
        'parent_id' => null,
        'description' => 'Извлечение данных с сайтов, парсинг контента/продуктов, обработка данных',
        'price_from' => 500,
        'price_to' => 1500,
        'active' => 1,
        'sort_order' => 200
    ],
    [
        'name' => 'Автоматическая генерация отчётов',
        'category' => 'automation',
        'parent_id' => null,
        'description' => 'Настройка автоматической генерации и отправки отчётов по расписанию',
        'price_from' => 100,
        'price_to' => 1500,
        'active' => 1,
        'sort_order' => 210
    ],
    [
        'name' => 'Chatbot / Голосовой бот',
        'category' => 'automation',
        'parent_id' => null,
        'description' => 'Разработка чат-бота для Telegram/сайта, автоматические ответы, обработка заказов',
        'price_from' => 300,
        'price_to' => 2000,
        'active' => 1,
        'sort_order' => 220
    ],

    // 6. Прочие услуги
    [
        'name' => 'Прочие услуги',
        'category' => 'other',
        'parent_id' => null,
        'description' => 'Дополнительные услуги: домен, хостинг, обучение, контент',
        'price_from' => null,
        'price_to' => null,
        'active' => 1,
        'sort_order' => 6
    ],
    [
        'name' => 'Регистрация домена / Хостинг',
        'category' => 'other',
        'parent_id' => null,
        'description' => 'Покупка и продление домена, настройка VPS/shared хостинга',
        'price_from' => 10,
        'price_to' => 150,
        'active' => 1,
        'sort_order' => 230
    ],
    [
        'name' => 'Поддержка пользователей / Обучение',
        'category' => 'other',
        'parent_id' => null,
        'description' => 'Обучение работе с сайтом, CMS, редактированию контента, консультации',
        'price_from' => 50,
        'price_to' => 150,
        'active' => 1,
        'sort_order' => 240
    ],
    [
        'name' => 'Контент и графика',
        'category' => 'other',
        'parent_id' => null,
        'description' => 'Написание текстов, дизайн баннеров, иллюстрации, обработка изображений',
        'price_from' => 50,
        'price_to' => 500,
        'active' => 1,
        'sort_order' => 250
    ],
];

try {
    $categoryIds = [];
    
    // Сначала создаем категории (родительские элементы)
    foreach ($services as $service) {
        if ($service['price_from'] === null && $service['price_to'] === null) {
            // Это категория
            $id = Service::create($service);
            $categoryIds[$service['category']] = $id;
            echo "✓ Created category: {$service['name']}\n";
        }
    }
    
    // Затем создаем услуги с привязкой к категориям
    foreach ($services as $service) {
        if ($service['price_from'] !== null || $service['price_to'] !== null) {
            // Это услуга, привязываем к категории
            $service['parent_id'] = $categoryIds[$service['category']] ?? null;
            Service::create($service);
            echo "✓ Created service: {$service['name']}\n";
        }
    }
    
    echo "\n✅ Migration completed successfully!\n";
    echo "Total categories: " . count($categoryIds) . "\n";
    echo "Total services: " . (count($services) - count($categoryIds)) . "\n";
} catch (Exception $e) {
    echo "\n✗ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
