<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Core\Config;
use Core\Database;
use Core\Service;

Config::load(__DIR__ . '/../.env');

echo "Adding detailed web studio services catalog with regional pricing...\n";

// Детализированный каталог услуг с региональными ценами
$services = [
    // ========== 1. ВЕБ-РАЗРАБОТКА ==========
    [
        'name' => 'Веб-разработка',
        'category' => 'web_development',
        'parent_id' => null,
        'description' => 'Разработка и создание веб-сайтов различных типов и сложности',
        'price_from' => null,
        'price_to' => null,
        'active' => 1,
        'sort_order' => 1
    ],
    
    // Лендинги
    [
        'name' => 'Лендинг / Одностраничный сайт',
        'category' => 'web_development',
        'parent_id' => null,
        'description' => 'Одностраничный сайт с 1-5 секциями, шаблонный дизайн, базовая адаптивность, контактная форма. Идеально для промо-акций и быстрого запуска.',
        'price_from' => 500,
        'price_to' => 2000,
        'active' => 1,
        'sort_order' => 10
    ],
    [
        'name' => 'Лендинг премиум',
        'category' => 'web_development',
        'parent_id' => null,
        'description' => 'Одностраничный сайт с уникальным дизайном, анимациями, A/B тестированием, интеграцией аналитики, мультиязычностью.',
        'price_from' => 2000,
        'price_to' => 5000,
        'active' => 1,
        'sort_order' => 11
    ],
    
    // Корпоративные сайты
    [
        'name' => 'Корпоративный сайт (5-10 страниц)',
        'category' => 'web_development',
        'parent_id' => null,
        'description' => 'Корпоративный сайт с уникальным дизайном, адаптивностью, CMS (WordPress), формами обратной связи, базовым SEO.',
        'price_from' => 3000,
        'price_to' => 8000,
        'active' => 1,
        'sort_order' => 20
    ],
    [
        'name' => 'Корпоративный сайт (10-20 страниц)',
        'category' => 'web_development',
        'parent_id' => null,
        'description' => 'Расширенный корпоративный сайт с блогом, галереей, мультиязычностью, интеграцией CRM, продвинутым SEO.',
        'price_from' => 8000,
        'price_to' => 20000,
        'active' => 1,
        'sort_order' => 21
    ],
    [
        'name' => 'Корпоративный сайт Enterprise',
        'category' => 'web_development',
        'parent_id' => null,
        'description' => 'Корпоративный портал с личными кабинетами, интеграциями ERP/CRM, системой бронирования, мультиязычностью, API.',
        'price_from' => 20000,
        'price_to' => 60000,
        'active' => 1,
        'sort_order' => 22
    ],
    
    // Интернет-магазины
    [
        'name' => 'Интернет-магазин (до 50 товаров)',
        'category' => 'web_development',
        'parent_id' => null,
        'description' => 'Интернет-магазин с каталогом товаров, корзиной, платежными системами, управлением заказами, базовой аналитикой.',
        'price_from' => 5000,
        'price_to' => 15000,
        'active' => 1,
        'sort_order' => 30
    ],
    [
        'name' => 'Интернет-магазин (50-200 товаров)',
        'category' => 'web_development',
        'parent_id' => null,
        'description' => 'Расширенный магазин с фильтрами, поиском, мультивалютностью, интеграцией с логистикой, CRM, аналитикой продаж.',
        'price_from' => 15000,
        'price_to' => 40000,
        'active' => 1,
        'sort_order' => 31
    ],
    [
        'name' => 'Маркетплейс / Платформа',
        'category' => 'web_development',
        'parent_id' => null,
        'description' => 'Многоуровневая платформа с множеством продавцов, системой комиссий, рейтингами, платежами, модерацией.',
        'price_from' => 40000,
        'price_to' => 150000,
        'active' => 1,
        'sort_order' => 32
    ],
    
    // Мультиязычные сайты
    [
        'name' => 'Мультиязычный сайт (2-3 языка)',
        'category' => 'web_development',
        'parent_id' => null,
        'description' => 'Сайт с поддержкой 2-3 языков, автоматическим переключением, SEO для каждого языка, локализация контента.',
        'price_from' => 4000,
        'price_to' => 12000,
        'active' => 1,
        'sort_order' => 40
    ],
    [
        'name' => 'Мультиязычный сайт (4+ языков)',
        'category' => 'web_development',
        'parent_id' => null,
        'description' => 'Сайт с поддержкой 4+ языков, RTL поддержкой, региональными настройками, мультивалютностью, локализацией.',
        'price_from' => 12000,
        'price_to' => 35000,
        'active' => 1,
        'sort_order' => 41
    ],
    
    // Миграция и перенос
    [
        'name' => 'Перенос сайта на новый хостинг',
        'category' => 'web_development',
        'parent_id' => null,
        'description' => 'Полный перенос файлов, базы данных, настройка домена, DNS, SSL, проверка работоспособности, SEO-редиректы.',
        'price_from' => 300,
        'price_to' => 2000,
        'active' => 1,
        'sort_order' => 50
    ],
    [
        'name' => 'Миграция на другую CMS',
        'category' => 'web_development',
        'parent_id' => null,
        'description' => 'Перенос сайта с одной CMS на другую, конвертация данных, настройка функционала, обучение работе.',
        'price_from' => 2000,
        'price_to' => 10000,
        'active' => 1,
        'sort_order' => 51
    ],
    
    // ========== 2. ЧАТ-БОТЫ И АВТОМАТИЗАЦИЯ ==========
    [
        'name' => 'Чат-боты и автоматизация',
        'category' => 'bots_automation',
        'parent_id' => null,
        'description' => 'Разработка чат-ботов различных типов и автоматизация бизнес-процессов',
        'price_from' => null,
        'price_to' => null,
        'active' => 1,
        'sort_order' => 2
    ],
    
    // Текстовые боты
    [
        'name' => 'FAQ-бот (текстовый, без админки)',
        'category' => 'bots_automation',
        'parent_id' => null,
        'description' => 'Простой текстовый бот с меню и кнопками, ответы на часто задаваемые вопросы, правила и сценарии. Без панели администратора.',
        'price_from' => 3000,
        'price_to' => 8000,
        'active' => 1,
        'sort_order' => 60
    ],
    [
        'name' => 'FAQ-бот (текстовый, с админкой)',
        'category' => 'bots_automation',
        'parent_id' => null,
        'description' => 'Текстовый бот с панелью администратора для управления ответами, статистикой, пользователями. Обновление контента без программиста.',
        'price_from' => 5000,
        'price_to' => 15000,
        'active' => 1,
        'sort_order' => 61
    ],
    [
        'name' => 'Текстовый бот с NLP (без админки)',
        'category' => 'bots_automation',
        'parent_id' => null,
        'description' => 'Умный текстовый бот с пониманием естественного языка, контекстные ответы, обучение на данных. Без панели управления.',
        'price_from' => 15000,
        'price_to' => 40000,
        'active' => 1,
        'sort_order' => 62
    ],
    [
        'name' => 'Текстовый бот с NLP (с админкой)',
        'category' => 'bots_automation',
        'parent_id' => null,
        'description' => 'AI-бот с NLP и панелью администратора для управления диалогами, обучения, аналитики, интеграций.',
        'price_from' => 25000,
        'price_to' => 80000,
        'active' => 1,
        'sort_order' => 63
    ],
    
    // Голосовые боты
    [
        'name' => 'Голосовой бот (без админки)',
        'category' => 'bots_automation',
        'parent_id' => null,
        'description' => 'Голосовой ассистент с распознаванием и синтезом речи, IVR-сценарии, обработка звонков. Без панели управления.',
        'price_from' => 20000,
        'price_to' => 60000,
        'active' => 1,
        'sort_order' => 70
    ],
    [
        'name' => 'Голосовой бот (с админкой)',
        'category' => 'bots_automation',
        'parent_id' => null,
        'description' => 'Голосовой бот с панелью администратора для управления сценариями, статистикой звонков, интеграциями CRM.',
        'price_from' => 40000,
        'price_to' => 120000,
        'active' => 1,
        'sort_order' => 71
    ],
    [
        'name' => 'Мультимодальный бот (текст + голос)',
        'category' => 'bots_automation',
        'parent_id' => null,
        'description' => 'Бот с поддержкой текстового и голосового общения, переключение между режимами, единая логика диалогов.',
        'price_from' => 50000,
        'price_to' => 150000,
        'active' => 1,
        'sort_order' => 72
    ],
    
    // Платежные боты
    [
        'name' => 'Платежный бот (без админки)',
        'category' => 'bots_automation',
        'parent_id' => null,
        'description' => 'Бот для приема платежей через Telegram/мессенджеры, интеграция платежных систем, обработка заказов. Без панели управления.',
        'price_from' => 8000,
        'price_to' => 25000,
        'active' => 1,
        'sort_order' => 80
    ],
    [
        'name' => 'Платежный бот (с админкой)',
        'category' => 'bots_automation',
        'parent_id' => null,
        'description' => 'Платежный бот с админ-панелью для управления товарами, ценами, заказами, статистикой продаж, возвратами.',
        'price_from' => 15000,
        'price_to' => 50000,
        'active' => 1,
        'sort_order' => 81
    ],
    [
        'name' => 'E-commerce бот (магазин в мессенджере)',
        'category' => 'bots_automation',
        'parent_id' => null,
        'description' => 'Полноценный магазин в Telegram/WhatsApp с каталогом, корзиной, оплатой, доставкой, личным кабинетом, админкой.',
        'price_from' => 25000,
        'price_to' => 80000,
        'active' => 1,
        'sort_order' => 82
    ],
    
    // Генеративные боты
    [
        'name' => 'Генеративный бот (GPT/LLM, без админки)',
        'category' => 'bots_automation',
        'parent_id' => null,
        'description' => 'Бот на базе GPT/Claude/других LLM, генерация ответов, обучение на данных клиента. Без панели управления.',
        'price_from' => 30000,
        'price_to' => 100000,
        'active' => 1,
        'sort_order' => 90
    ],
    [
        'name' => 'Генеративный бот (GPT/LLM, с админкой)',
        'category' => 'bots_automation',
        'parent_id' => null,
        'description' => 'AI-бот с LLM и админ-панелью для управления промптами, обучения, аналитики, интеграций, мультиязычности.',
        'price_from' => 80000,
        'price_to' => 250000,
        'active' => 1,
        'sort_order' => 91
    ],
    
    // Интеграции ботов
    [
        'name' => 'Интеграция бота с CRM',
        'category' => 'bots_automation',
        'parent_id' => null,
        'description' => 'Интеграция чат-бота с CRM-системой (Salesforce, HubSpot, AmoCRM и др.): синхронизация лидов, сделок, контактов.',
        'price_from' => 2000,
        'price_to' => 10000,
        'active' => 1,
        'sort_order' => 100
    ],
    [
        'name' => 'Интеграция бота с платежными системами',
        'category' => 'bots_automation',
        'parent_id' => null,
        'description' => 'Подключение платежных систем (Stripe, PayPal, ЮKassa, Qiwi и др.) к боту для приема оплат.',
        'price_from' => 1500,
        'price_to' => 8000,
        'active' => 1,
        'sort_order' => 101
    ],
    [
        'name' => 'Мультиканальный бот (Telegram, WhatsApp, Viber)',
        'category' => 'bots_automation',
        'parent_id' => null,
        'description' => 'Бот работающий в нескольких мессенджерах одновременно, единая логика, синхронизация диалогов.',
        'price_from' => 10000,
        'price_to' => 40000,
        'active' => 1,
        'sort_order' => 102
    ],
    
    // Автоматизация процессов
    [
        'name' => 'Автоматизация воронки продаж',
        'category' => 'bots_automation',
        'parent_id' => null,
        'description' => 'Автоматизация сбора лидов: формы → CRM → рассылки → уведомления менеджерам, триггеры, сегментация.',
        'price_from' => 1500,
        'price_to' => 5000,
        'active' => 1,
        'sort_order' => 110
    ],
    [
        'name' => 'Автоматизация email-рассылок',
        'category' => 'bots_automation',
        'parent_id' => null,
        'description' => 'Настройка автоматических email-рассылок, drip-кампаний, триггерных писем, A/B тестирование, аналитика.',
        'price_from' => 1000,
        'price_to' => 4000,
        'active' => 1,
        'sort_order' => 111
    ],
    [
        'name' => 'Автоматизация обработки заявок',
        'category' => 'bots_automation',
        'parent_id' => null,
        'description' => 'Автоматическая обработка заявок: распределение по менеджерам, уведомления, статусы, интеграция с CRM.',
        'price_from' => 2000,
        'price_to' => 8000,
        'active' => 1,
        'sort_order' => 112
    ],
    [
        'name' => 'Парсинг и сбор данных',
        'category' => 'bots_automation',
        'parent_id' => null,
        'description' => 'Автоматический сбор данных с сайтов, парсинг контента/продуктов, мониторинг конкурентов, обработка данных.',
        'price_from' => 500,
        'price_to' => 1500,
        'active' => 1,
        'sort_order' => 113
    ],
    [
        'name' => 'Автоматическая генерация отчётов',
        'category' => 'bots_automation',
        'parent_id' => null,
        'description' => 'Настройка автоматической генерации и отправки отчётов по расписанию, дашборды, интеграция с аналитикой.',
        'price_from' => 1000,
        'price_to' => 5000,
        'active' => 1,
        'sort_order' => 114
    ],
    
    // ========== 3. ДИЗАЙН И UI/UX ==========
    [
        'name' => 'Дизайн и UI/UX',
        'category' => 'design',
        'parent_id' => null,
        'description' => 'Дизайн интерфейсов, прототипирование, брендинг, пользовательский опыт',
        'price_from' => null,
        'price_to' => null,
        'active' => 1,
        'sort_order' => 3
    ],
    
    [
        'name' => 'Прототипирование / Wireframes',
        'category' => 'design',
        'parent_id' => null,
        'description' => 'Создание прототипов интерфейса, wireframes, пользовательских путей, структуры сайта/приложения.',
        'price_from' => 500,
        'price_to' => 2000,
        'active' => 1,
        'sort_order' => 120
    ],
    [
        'name' => 'UI/UX дизайн (базовый)',
        'category' => 'design',
        'parent_id' => null,
        'description' => 'Базовый дизайн интерфейса: шапка, цвета, логотип, типографика, базовые элементы, адаптивность.',
        'price_from' => 800,
        'price_to' => 3000,
        'active' => 1,
        'sort_order' => 121
    ],
    [
        'name' => 'UI/UX дизайн (продвинутый)',
        'category' => 'design',
        'parent_id' => null,
        'description' => 'Полный дизайн-пакет: взаимодействие, анимации, микроинтеракции, тестирование удобства, адаптивность всех устройств.',
        'price_from' => 3000,
        'price_to' => 10000,
        'active' => 1,
        'sort_order' => 122
    ],
    [
        'name' => 'Редизайн существующего сайта',
        'category' => 'design',
        'parent_id' => null,
        'description' => 'Полный редизайн сайта с сохранением функциональности, улучшение UX/UI, модернизация интерфейса.',
        'price_from' => 2000,
        'price_to' => 8000,
        'active' => 1,
        'sort_order' => 123
    ],
    [
        'name' => 'Адаптивный / Мобильный дизайн',
        'category' => 'design',
        'parent_id' => null,
        'description' => 'Разработка адаптивного дизайна для всех устройств, мобильная версия сайта, тестирование на устройствах.',
        'price_from' => 1000,
        'price_to' => 4000,
        'active' => 1,
        'sort_order' => 124
    ],
    [
        'name' => 'Брендинг (логотип, фирменный стиль)',
        'category' => 'design',
        'parent_id' => null,
        'description' => 'Разработка логотипа, фирменного стиля, гайдлайнов, брендбука, применение на носителях.',
        'price_from' => 1000,
        'price_to' => 5000,
        'active' => 1,
        'sort_order' => 125
    ],
    [
        'name' => 'UX тестирование / Исследование',
        'category' => 'design',
        'parent_id' => null,
        'description' => 'Исследование пользовательского опыта, тестирование интерфейса, интервью с пользователями, рекомендации.',
        'price_from' => 1500,
        'price_to' => 6000,
        'active' => 1,
        'sort_order' => 126
    ],
    
    // ========== 4. SEO И МАРКЕТИНГ ==========
    [
        'name' => 'SEO и маркетинг',
        'category' => 'seo',
        'parent_id' => null,
        'description' => 'Поисковая оптимизация, контент-маркетинг, настройка рекламы, аналитика',
        'price_from' => null,
        'price_to' => null,
        'active' => 1,
        'sort_order' => 4
    ],
    
    [
        'name' => 'SEO-аудит (технический)',
        'category' => 'seo',
        'parent_id' => null,
        'description' => 'Технический SEO-аудит: анализ скорости, метатегов, архитектуры сайта, ошибок индексации, рекомендации.',
        'price_from' => 200,
        'price_to' => 1000,
        'active' => 1,
        'sort_order' => 130
    ],
    [
        'name' => 'SEO базовая оптимизация',
        'category' => 'seo',
        'parent_id' => null,
        'description' => 'Базовая SEO-оптимизация: метатеги, структура, sitemap, регистрация в поисковиках, внутренняя перелинковка.',
        'price_from' => 500,
        'price_to' => 2000,
        'active' => 1,
        'sort_order' => 131
    ],
    [
        'name' => 'SEO продвинутая / Локальная',
        'category' => 'seo',
        'parent_id' => null,
        'description' => 'Продвинутая SEO: исследование ключевых слов, контент-план, внешняя оптимизация, локальное SEO, отчёты.',
        'price_from' => 1000,
        'price_to' => 5000,
        'active' => 1,
        'sort_order' => 132
    ],
    [
        'name' => 'SEO пакет (ежемесячно)',
        'category' => 'seo',
        'parent_id' => null,
        'description' => 'Ежемесячный пакет SEO: контент, ссылки, мониторинг позиций, отчёты, корректировки стратегии.',
        'price_from' => 300,
        'price_to' => 2000,
        'active' => 1,
        'sort_order' => 133
    ],
    [
        'name' => 'Написание / Оптимизация текстов',
        'category' => 'seo',
        'parent_id' => null,
        'description' => 'SEO-копирайтинг, оптимизация контента, написание текстов для сайта с учётом ключевых слов.',
        'price_from' => 50,
        'price_to' => 500,
        'active' => 1,
        'sort_order' => 134
    ],
    [
        'name' => 'Ведение блога / Контент-план',
        'category' => 'seo',
        'parent_id' => null,
        'description' => 'Планирование и написание контента для блога, регулярные публикации, SEO-оптимизация статей.',
        'price_from' => 200,
        'price_to' => 1500,
        'active' => 1,
        'sort_order' => 135
    ],
    [
        'name' => 'Настройка рекламных кампаний',
        'category' => 'seo',
        'parent_id' => null,
        'description' => 'Настройка Google Ads, Яндекс.Директ, Facebook Ads, настройка пикселей, конверсий, ретаргетинга.',
        'price_from' => 500,
        'price_to' => 3000,
        'active' => 1,
        'sort_order' => 136
    ],
    [
        'name' => 'Настройка аналитики (GA4, Яндекс.Метрика)',
        'category' => 'seo',
        'parent_id' => null,
        'description' => 'Интеграция систем аналитики, настройка целей, событий, отчётов, дашбордов, e-commerce трекинг.',
        'price_from' => 200,
        'price_to' => 1000,
        'active' => 1,
        'sort_order' => 137
    ],
    [
        'name' => 'SMM / Социальные сети',
        'category' => 'seo',
        'parent_id' => null,
        'description' => 'Ведение социальных сетей, создание контента, публикации, взаимодействие с аудиторией, реклама.',
        'price_from' => 300,
        'price_to' => 2000,
        'active' => 1,
        'sort_order' => 138
    ],
    
    // ========== 5. БЕЗОПАСНОСТЬ И SSL ==========
    [
        'name' => 'Безопасность и SSL',
        'category' => 'security',
        'parent_id' => null,
        'description' => 'Услуги по обеспечению безопасности сайта, SSL-сертификаты, защита данных',
        'price_from' => null,
        'price_to' => null,
        'active' => 1,
        'sort_order' => 5
    ],
    
    [
        'name' => 'Установка SSL-сертификата (DV)',
        'category' => 'security',
        'parent_id' => null,
        'description' => 'Установка базового SSL-сертификата (Domain Validation), настройка HTTPS, редиректы.',
        'price_from' => 50,
        'price_to' => 200,
        'active' => 1,
        'sort_order' => 140
    ],
    [
        'name' => 'Установка SSL-сертификата (OV/EV)',
        'category' => 'security',
        'parent_id' => null,
        'description' => 'Установка расширенного SSL (Organization/Extended Validation), настройка безопасности, заголовки.',
        'price_from' => 200,
        'price_to' => 800,
        'active' => 1,
        'sort_order' => 141
    ],
    [
        'name' => 'Wildcard SSL / Мультидоменный',
        'category' => 'security',
        'parent_id' => null,
        'description' => 'SSL-сертификат для поддоменов (wildcard) или нескольких доменов, настройка для всех поддоменов.',
        'price_from' => 300,
        'price_to' => 1200,
        'active' => 1,
        'sort_order' => 142
    ],
    [
        'name' => 'Управляемый SSL (с поддержкой)',
        'category' => 'security',
        'parent_id' => null,
        'description' => 'Полная поддержка SSL: мониторинг, автоматические обновления, управление сертификатами, уведомления.',
        'price_from' => 100,
        'price_to' => 500,
        'active' => 1,
        'sort_order' => 143
    ],
    [
        'name' => 'Аудит безопасности',
        'category' => 'security',
        'parent_id' => null,
        'description' => 'Проверка уязвимостей, слабых мест, настройка заголовков безопасности, конфигурация сервера.',
        'price_from' => 500,
        'price_to' => 3000,
        'active' => 1,
        'sort_order' => 144
    ],
    [
        'name' => 'Защита от DDoS и атак',
        'category' => 'security',
        'parent_id' => null,
        'description' => 'Настройка защиты от DDoS-атак, WAF, мониторинг, блокировка подозрительного трафика.',
        'price_from' => 1000,
        'price_to' => 5000,
        'active' => 1,
        'sort_order' => 145
    ],
    [
        'name' => 'Резервное копирование и восстановление',
        'category' => 'security',
        'parent_id' => null,
        'description' => 'Настройка регулярных бэкапов, автоматическое восстановление, план действий в аварийных ситуациях.',
        'price_from' => 200,
        'price_to' => 1500,
        'active' => 1,
        'sort_order' => 146
    ],
    [
        'name' => 'GDPR / Комплаенс',
        'category' => 'security',
        'parent_id' => null,
        'description' => 'Настройка соответствия GDPR, защита персональных данных, политика конфиденциальности, согласия.',
        'price_from' => 1000,
        'price_to' => 5000,
        'active' => 1,
        'sort_order' => 147
    ],
    
    // ========== 6. ИНТЕГРАЦИИ И API ==========
    [
        'name' => 'Интеграции и API',
        'category' => 'integrations',
        'parent_id' => null,
        'description' => 'Интеграция с внешними сервисами, API, автоматизация процессов',
        'price_from' => null,
        'price_to' => null,
        'active' => 1,
        'sort_order' => 6
    ],
    
    [
        'name' => 'API-интеграции (CRM, ERP)',
        'category' => 'integrations',
        'parent_id' => null,
        'description' => 'Интеграция с CRM/ERP системами (Salesforce, 1С, SAP и др.), синхронизация данных, автоматизация.',
        'price_from' => 2000,
        'price_to' => 15000,
        'active' => 1,
        'sort_order' => 150
    ],
    [
        'name' => 'Интеграция платежных систем',
        'category' => 'integrations',
        'parent_id' => null,
        'description' => 'Подключение платежных шлюзов (Stripe, PayPal, ЮKassa, Qiwi и др.), обработка платежей, возвраты.',
        'price_from' => 1000,
        'price_to' => 8000,
        'active' => 1,
        'sort_order' => 151
    ],
    [
        'name' => 'Интеграция с логистикой',
        'category' => 'integrations',
        'parent_id' => null,
        'description' => 'Интеграция с службами доставки, трекинг заказов, расчёт стоимости доставки, уведомления.',
        'price_from' => 1500,
        'price_to' => 10000,
        'active' => 1,
        'sort_order' => 152
    ],
    [
        'name' => 'Webhooks и автоматизация',
        'category' => 'integrations',
        'parent_id' => null,
        'description' => 'Настройка webhooks для автоматизации процессов между системами, триггеры, уведомления.',
        'price_from' => 500,
        'price_to' => 3000,
        'active' => 1,
        'sort_order' => 153
    ],
    [
        'name' => 'Интеграция с мессенджерами',
        'category' => 'integrations',
        'parent_id' => null,
        'description' => 'Интеграция с Telegram, WhatsApp, Viber, Facebook Messenger для уведомлений и коммуникации.',
        'price_from' => 1000,
        'price_to' => 5000,
        'active' => 1,
        'sort_order' => 154
    ],
    
    // ========== 7. ПОДДЕРЖКА И ОБСЛУЖИВАНИЕ ==========
    [
        'name' => 'Поддержка и обслуживание',
        'category' => 'support',
        'parent_id' => null,
        'description' => 'Техническая поддержка, обновления, хостинг, мониторинг',
        'price_from' => null,
        'price_to' => null,
        'active' => 1,
        'sort_order' => 7
    ],
    
    [
        'name' => 'Техническая поддержка (базовая)',
        'category' => 'support',
        'parent_id' => null,
        'description' => 'Базовая техническая поддержка: исправление ошибок, обновления контента, консультации (до 5 часов/мес).',
        'price_from' => 200,
        'price_to' => 800,
        'active' => 1,
        'sort_order' => 160
    ],
    [
        'name' => 'Техническая поддержка (расширенная)',
        'category' => 'support',
        'parent_id' => null,
        'description' => 'Расширенная поддержка: доработки, новые функции, интеграции, мониторинг (до 20 часов/мес).',
        'price_from' => 800,
        'price_to' => 3000,
        'active' => 1,
        'sort_order' => 161
    ],
    [
        'name' => 'Хостинг и домен',
        'category' => 'support',
        'parent_id' => null,
        'description' => 'Настройка хостинга, регистрация/продление домена, настройка DNS, CDN, оптимизация производительности.',
        'price_from' => 100,
        'price_to' => 500,
        'active' => 1,
        'sort_order' => 162
    ],
    [
        'name' => 'Мониторинг и аналитика',
        'category' => 'support',
        'parent_id' => null,
        'description' => 'Настройка мониторинга сайта, уведомления о сбоях, аналитика производительности, отчёты.',
        'price_from' => 300,
        'price_to' => 1500,
        'active' => 1,
        'sort_order' => 163
    ],
    [
        'name' => 'Обучение работе с сайтом',
        'category' => 'support',
        'parent_id' => null,
        'description' => 'Обучение работе с CMS, редактированию контента, управлению сайтом, консультации.',
        'price_from' => 200,
        'price_to' => 1000,
        'active' => 1,
        'sort_order' => 164
    ],
    
    // ========== 8. ПРОЧИЕ УСЛУГИ ==========
    [
        'name' => 'Прочие услуги',
        'category' => 'other',
        'parent_id' => null,
        'description' => 'Дополнительные услуги: контент, графика, консультации',
        'price_from' => null,
        'price_to' => null,
        'active' => 1,
        'sort_order' => 8
    ],
    
    [
        'name' => 'Контент и графика',
        'category' => 'other',
        'parent_id' => null,
        'description' => 'Написание текстов, дизайн баннеров, иллюстрации, обработка изображений, инфографика.',
        'price_from' => 100,
        'price_to' => 1000,
        'active' => 1,
        'sort_order' => 170
    ],
    [
        'name' => 'Видео и анимация',
        'category' => 'other',
        'parent_id' => null,
        'description' => 'Создание видео-контента, анимаций, рекламных роликов, монтаж, обработка.',
        'price_from' => 500,
        'price_to' => 5000,
        'active' => 1,
        'sort_order' => 171
    ],
    [
        'name' => 'Консультации и аудит',
        'category' => 'other',
        'parent_id' => null,
        'description' => 'Консультации по веб-разработке, технический аудит проекта, рекомендации по улучшению.',
        'price_from' => 300,
        'price_to' => 2000,
        'active' => 1,
        'sort_order' => 172
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
