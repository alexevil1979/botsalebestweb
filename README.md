# Telegram-бот для продаж веб-студии

Production-ready Telegram-бот, который работает как профессиональный менеджер по продажам для веб-студии.

**📍 Путь на VPS:** `/ssd/www/bots/botsalebestwebstudio`

📖 **Быстрый старт:** см. [QUICK_START.md](QUICK_START.md)  
🚀 **Полный гайд по деплою:** см. [DEPLOY_GUIDE.md](DEPLOY_GUIDE.md)  
📋 **Детальный деплой:** см. [DEPLOY.md](DEPLOY.md)

## 🎯 Возможности

- **Продажная воронка** с state machine (приветствие → задача → уточнение → услуга → цена → CTA → контакт)
- **Мультиязычность** - поддержка русского, английского, тайского и китайского языков
- **Полная история диалогов** в MySQL
- **Веб-админка** для управления лидами, услугами и просмотра чатов
- **Поддержка LLM** (YandexGPT, GigaChat) для улучшения формулировок (опционально)
- **Автоматический деплой** через Git
- **Redis** для хранения состояний диалогов
- **Анти-флуд защита**

## 📋 Требования

- PHP 8.1+
- MySQL 5.7+ или MariaDB 10.3+
- Redis
- Composer
- Git
- Веб-сервер (Nginx/Apache) с PHP-FPM

## 🚀 Установка на VPS

> **📘 Для подробной пошаговой инструкции см. [DEPLOY_GUIDE.md](DEPLOY_GUIDE.md)**

### 1. Клонирование репозитория

```bash
cd /ssd/www/bots
git clone <your-repo-url> botsalebestwebstudio
cd botsalebestwebstudio
```

### 2. Настройка окружения

```bash
cp env.example.txt .env
nano .env
```

Заполните все необходимые параметры в `.env`:

```env
# Telegram Bot
TELEGRAM_BOT_TOKEN=your_bot_token_here
TELEGRAM_WEBHOOK_SECRET=your_webhook_secret_here
TELEGRAM_WEBHOOK_URL=https://yourdomain.com/bot/webhook.php

# Database
DB_HOST=localhost
DB_NAME=telegram_bot
DB_USER=root
DB_PASS=
DB_CHARSET=utf8mb4

# Redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_PASSWORD=
REDIS_DB=0

# Admin Panel
ADMIN_USERNAME=admin
ADMIN_PASSWORD=change_me_secure_password
ADMIN_SESSION_LIFETIME=3600

# LLM (Optional)
LLM_ENABLED=false
LLM_PROVIDER=yandex
YANDEX_API_KEY=
YANDEX_FOLDER_ID=
GIGACHAT_CLIENT_ID=
GIGACHAT_CLIENT_SECRET=
GIGACHAT_SCOPE=https://gigachat.dev/v1

# App
APP_ENV=production
APP_DEBUG=false
TIMEZONE=Europe/Moscow
```

### 3. Установка зависимостей

```bash
composer install --no-dev --optimize-autoloader
```

### 4. Настройка базы данных

Создайте базу данных:

```bash
mysql -u root -p
```

```sql
CREATE DATABASE telegram_bot CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'telegram_bot'@'localhost' IDENTIFIED BY 'your_password';
GRANT ALL PRIVILEGES ON telegram_bot.* TO 'telegram_bot'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

Запустите миграции:

```bash
php migrations/migrate.php
php migrations/add_preferred_language.php
```

Или импортируйте схему напрямую:

```bash
mysql -u telegram_bot -p telegram_bot < schema.sql
```

### 5. Настройка Redis

Убедитесь, что Redis запущен:

```bash
sudo systemctl start redis
sudo systemctl enable redis
```

Проверьте подключение:

```bash
redis-cli ping
```

### 6. Настройка веб-сервера (Apache)

#### Включение необходимых модулей
```bash
sudo a2enmod rewrite
sudo a2enmod headers
sudo a2enmod ssl
```

#### Создание конфигурации

Создайте виртуальный хост в `/etc/apache2/sites-available/botsalebestwebstudio.conf`:

```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    ServerAlias www.yourdomain.com
    DocumentRoot /ssd/www/bots/botsalebestwebstudio

    # Логи
    ErrorLog ${APACHE_LOG_DIR}/botsalebestwebstudio_error.log
    CustomLog ${APACHE_LOG_DIR}/botsalebestwebstudio_access.log combined

    # Основные настройки
    <Directory /ssd/www/bots/botsalebestwebstudio>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    # PHP обработка
    <FilesMatch \.php$>
        SetHandler application/x-httpd-php
    </FilesMatch>

    # Админ-панель
    <Directory /ssd/www/bots/botsalebestwebstudio/admin>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    # Webhook для Telegram
    <Directory /ssd/www/bots/botsalebestwebstudio/bot>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    # Защита .env файла
    <FilesMatch "^\.env">
        Require all denied
    </FilesMatch>

    # Защита других скрытых файлов
    <FilesMatch "^\.">
        Require all denied
    </FilesMatch>

    # Передача заголовков для webhook secret
    <IfModule mod_headers.c>
        RequestHeader set X-Telegram-Bot-Api-Secret-Token "expr=%{HTTP_X_TELEGRAM_BOT_API_SECRET_TOKEN}"
    </IfModule>
</VirtualHost>
```

Активируйте:

```bash
sudo a2ensite botsalebestwebstudio.conf
sudo apache2ctl configtest
sudo systemctl reload apache2
```

### 7. Настройка SSL (Let's Encrypt)

```bash
sudo apt install -y certbot python3-certbot-apache
sudo certbot --apache -d yourdomain.com -d www.yourdomain.com
```

### 8. Настройка Telegram Webhook

```bash
php bot/setup-webhook.php
```

Или вручную через API:

```bash
curl -X POST "https://api.telegram.org/bot<YOUR_BOT_TOKEN>/setWebhook" \
  -d "url=https://yourdomain.com/bot/webhook.php" \
  -d "secret_token=your_webhook_secret"
```

### 9. Настройка прав доступа

```bash
sudo chown -R www-data:www-data /ssd/www/bots/botsalebestwebstudio
sudo chmod -R 755 /ssd/www/bots/botsalebestwebstudio
sudo chmod -R 777 /ssd/www/bots/botsalebestwebstudio/logs 2>/dev/null || true
```

## 🔄 Автоматический деплой

### Вариант 1: GitHub Actions

1. Добавьте secrets в GitHub:
   - `VPS_HOST` - IP или домен вашего VPS
   - `VPS_USER` - пользователь для SSH
   - `VPS_SSH_KEY` - приватный SSH ключ
   - `VPS_PATH` - путь к проекту на VPS: `/ssd/www/bots/botsalebestwebstudio`

2. При каждом push в `main` или `master` будет автоматически выполняться деплой.

### Вариант 2: Git Webhook

1. Добавьте в `.env`:
   ```env
   WEBHOOK_SECRET=your_secure_random_string
   ```

2. Настройте webhook в вашем Git-репозитории:
   - URL: `https://yourdomain.com/deploy/webhook.php`
   - Secret: значение из `WEBHOOK_SECRET`
   - Events: Push

3. При push в `main`/`master` будет автоматически выполняться деплой.

### Вариант 3: Ручной деплой

```bash
cd /ssd/www/bots/botsalebestwebstudio
bash deploy/deploy.sh
```

## 📊 Веб-админка

Доступ: `https://yourdomain.com/admin`

Логин и пароль настраиваются в `.env`:
- `ADMIN_USERNAME`
- `ADMIN_PASSWORD`

### Возможности админки:

- **Дашборд** - статистика, последние лиды и диалоги
- **Диалоги** - просмотр всех диалогов с пользователями
- **Чат** - полный просмотр истории сообщений (как в Telegram)
- **Лиды** - управление лидами, изменение статусов, заметки
- **Услуги** - CRUD услуг и цен
- **Пользователи** - список всех пользователей бота
- **Поиск** - поиск по сообщениям

## ⚙️ Управление услугами

Услуги можно добавлять и редактировать через веб-админку (`/admin/services.php`) или напрямую в БД:

```sql
INSERT INTO services (name, description, price_from, price_to, active, sort_order) 
VALUES ('Лендинг', 'Одностраничный сайт', 15000, 50000, 1, 1);
```

## 🤖 Подключение LLM (опционально)

### YandexGPT

1. Получите API ключ и Folder ID в [Yandex Cloud](https://cloud.yandex.ru/)
2. Добавьте в `.env`:
   ```env
   LLM_ENABLED=true
   LLM_PROVIDER=yandex
   YANDEX_API_KEY=your_api_key
   YANDEX_FOLDER_ID=your_folder_id
   ```

### GigaChat

1. Зарегистрируйтесь в [GigaChat](https://developers.sber.ru/gigachat)
2. Получите Client ID и Client Secret
3. Добавьте в `.env`:
   ```env
   LLM_ENABLED=true
   LLM_PROVIDER=gigachat
   GIGACHAT_CLIENT_ID=your_client_id
   GIGACHAT_CLIENT_SECRET=your_client_secret
   ```

**Важно:** Бот полностью работоспособен без LLM. LLM только улучшает формулировки, но не управляет логикой воронки.

## 🔐 Безопасность

- Webhook secret для защиты от несанкционированных запросов
- Авторизация в админке по паролю
- CSRF защита для форм
- Prepared statements для всех SQL запросов
- Валидация всех входных данных

## 📝 Структура проекта

```
/project
 ├── bot/                    # Telegram бот
 │   ├── WebhookHandler.php  # Обработчик webhook
 │   ├── webhook.php         # Точка входа webhook
 │   └── setup-webhook.php   # Скрипт настройки webhook
 ├── admin/                  # Веб-админка
 │   ├── index.php          # Дашборд
 │   ├── dialogs.php        # Список диалогов
 │   ├── chat.php           # Просмотр чата
 │   ├── leads.php          # Список лидов
 │   ├── lead.php           # Детали лида
 │   ├── services.php       # Управление услугами
 │   ├── users.php          # Пользователи
 │   ├── search.php         # Поиск
 │   └── assets/            # CSS стили
 ├── core/                  # Ядро системы
 │   ├── Config.php         # Конфигурация
 │   ├── Database.php       # Работа с БД
 │   ├── Redis.php          # Работа с Redis
 │   ├── TelegramAPI.php    # Telegram API
 │   ├── StateMachine.php   # State machine
 │   ├── LLM.php            # LLM интеграция
 │   ├── Translator.php     # Система переводов
 │   ├── User.php           # Модель пользователя
 │   ├── Dialog.php         # Модель диалога
 │   ├── Lead.php           # Модель лида
 │   └── Service.php        # Модель услуги
 ├── translations/          # Переводы
 │   ├── ru.php            # Русский
 │   ├── en.php            # Английский
 │   ├── th.php            # Тайский
 │   └── zh.php            # Китайский
 ├── migrations/            # Миграции БД
 │   ├── migrate.php
 │   └── add_preferred_language.php
 ├── deploy/                # Скрипты деплоя
 │   ├── deploy.sh          # Основной скрипт деплоя
 │   └── webhook.php        # Git webhook handler
 ├── .github/workflows/     # GitHub Actions
 │   └── deploy.yml
 ├── schema.sql            # SQL схема БД
 ├── composer.json         # Зависимости
 ├── env.example.txt       # Пример конфигурации
 ├── .gitignore           # Git ignore правила
 ├── DEPLOY_GUIDE.md      # Полный гайд по деплою
 └── README.md             # Документация
```

## 🔄 Обновление без простоя

1. Автоматически через Git (при настройке деплоя)
2. Вручную:
   ```bash
   cd /ssd/www/bots/botsalebestwebstudio
   git pull
   composer install --no-dev
   php migrations/migrate.php
   php migrations/add_preferred_language.php
   sudo systemctl reload apache2
   ```

## 🐛 Логи и отладка

Логи PHP ошибок обычно находятся в:
- `/var/log/php8.1-fpm.log`
- Или настройте в `php.ini`: `error_log = /ssd/www/bots/botsalebestwebstudio/logs/php_errors.log`

Проверка webhook:
```bash
php bot/setup-webhook.php
```

Проверка подключения к БД:
```bash
cd /ssd/www/bots/botsalebestwebstudio
php -r "require 'vendor/autoload.php'; Core\Config::load('.env'); var_dump(Core\Database::fetch('SELECT 1'));"
```

Проверка Redis:
```bash
redis-cli ping
```

## 📞 Поддержка

При возникновении проблем:

1. Проверьте логи веб-сервера и PHP
2. Убедитесь, что все сервисы запущены (MySQL, Redis, PHP-FPM)
3. Проверьте права доступа к файлам
4. Убедитесь, что `.env` настроен корректно
5. Проверьте webhook через `php bot/setup-webhook.php`

## 📄 Лицензия

MIT

## 🌍 Мультиязычность

Бот автоматически определяет язык пользователя из Telegram и использует соответствующие переводы:
- **Русский** (ru) - по умолчанию
- **Английский** (en)
- **Тайский** (th)
- **Китайский** (zh)

Язык определяется из `language_code` пользователя Telegram и сохраняется в поле `preferred_language`. Все сообщения бота автоматически переводятся на язык пользователя.

Переводы находятся в папке `translations/` и могут быть легко расширены.

## 🎉 Готово к продакшену!

Бот полностью готов к использованию в production. Все компоненты протестированы и оптимизированы. Поддерживается мультиязычность для 4 языков.
