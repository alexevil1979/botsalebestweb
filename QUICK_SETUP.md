# ⚡ Быстрая настройка для botsale.1tlt.ru

## 📋 Ваши данные

- **Домен:** `botsale.1tlt.ru`
- **Репозиторий:** `https://github.com/alexevil1979/botsalebestweb.git`
- **Токен бота:** `8496559310:AAFDB-mRyv4pOh_4Sj2LdtNWYZ4XK0v_DE8`
- **Webhook Secret:** `fkew323f32f23f2332f`
- **Пароль БД:** `qweasd333123`
- **Пароль админки:** `qweasd333123`

## 🚀 Команды для быстрой настройки

### 1. Клонирование репозитория
```bash
cd /ssd/www/bots
git clone https://github.com/alexevil1979/botsalebestweb.git botsalebestwebstudio
cd botsalebestwebstudio
```

### 2. Создание .env файла
```bash
cp env.example.txt .env
nano .env
```

Вставьте следующее содержимое:

```env
# Telegram Bot
TELEGRAM_BOT_TOKEN=8496559310:AAFDB-mRyv4pOh_4Sj2LdtNWYZ4XK0v_DE8
TELEGRAM_WEBHOOK_SECRET=fkew323f32f23f2332f
TELEGRAM_WEBHOOK_URL=https://botsale.1tlt.ru/bot/webhook.php

# Database
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=telegram_bot
DB_USER=root
DB_PASS=qweasd333123
DB_CHARSET=utf8mb4

# Redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_PASSWORD=
REDIS_DB=0

# Admin Panel
ADMIN_USERNAME=admin
ADMIN_PASSWORD=qweasd333123
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

**Сохраните:** `Ctrl+O`, `Enter`, `Ctrl+X`

### 3. Установка зависимостей
```bash
composer install --no-dev --optimize-autoloader
```

### 4. Настройка базы данных
```bash
mysql -u root -p
```

Выполните:
```sql
CREATE DATABASE telegram_bot CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
FLUSH PRIVILEGES;
EXIT;
```

Запустите миграции (в правильном порядке):
```bash
# Сначала основная миграция (создает все таблицы)
php migrations/migrate.php

# Затем дополнительные миграции (добавляют колонки)
php migrations/add_preferred_language.php
```

**⚠️ ВАЖНО:** Всегда выполняйте `migrate.php` ПЕРВЫМ, так как он создает все таблицы. `add_preferred_language.php` добавляет колонку в уже существующую таблицу.

### 5. Настройка прав доступа
```bash
sudo chown -R www-data:www-data /ssd/www/bots/botsalebestwebstudio
sudo chmod -R 755 /ssd/www/bots/botsalebestwebstudio
sudo chmod -R 777 /ssd/www/bots/botsalebestwebstudio/logs 2>/dev/null || true
```

### 6. Настройка Apache

Создайте конфигурацию:
```bash
sudo nano /etc/apache2/sites-available/botsalebestwebstudio.conf
```

Вставьте:
```apache
<VirtualHost *:80>
    ServerName botsale.1tlt.ru
    ServerAlias www.botsale.1tlt.ru
    DocumentRoot /ssd/www/bots/botsalebestwebstudio

    ErrorLog ${APACHE_LOG_DIR}/botsalebestwebstudio_error.log
    CustomLog ${APACHE_LOG_DIR}/botsalebestwebstudio_access.log combined

    <Directory /ssd/www/bots/botsalebestwebstudio>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    <FilesMatch \.php$>
        SetHandler application/x-httpd-php
    </FilesMatch>

    <Directory /ssd/www/bots/botsalebestwebstudio/admin>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    <Directory /ssd/www/bots/botsalebestwebstudio/bot>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    <FilesMatch "^\.env">
        Require all denied
    </FilesMatch>

    <FilesMatch "^\.">
        Require all denied
    </FilesMatch>

    <IfModule mod_headers.c>
        RequestHeader set X-Telegram-Bot-Api-Secret-Token "expr=%{HTTP_X_TELEGRAM_BOT_API_SECRET_TOKEN}"
    </IfModule>
</VirtualHost>
```

Активируйте:
```bash
sudo a2enmod rewrite headers ssl
sudo a2ensite botsalebestwebstudio.conf
sudo apache2ctl configtest
sudo systemctl reload apache2
```

### 7. Настройка SSL
```bash
sudo apt install -y certbot python3-certbot-apache
sudo certbot --apache -d botsale.1tlt.ru -d www.botsale.1tlt.ru
```

### 8. Настройка Telegram Webhook
```bash
php bot/setup-webhook.php
```

**Проверка:**
```bash
curl -X POST "https://api.telegram.org/bot8496559310:AAFDB-mRyv4pOh_4Sj2LdtNWYZ4XK0v_DE8/getWebhookInfo"
```

## ✅ Проверка

1. **Админка:** https://botsale.1tlt.ru/admin
   - Логин: `admin`
   - Пароль: `qweasd333123`

2. **Webhook:** https://botsale.1tlt.ru/bot/webhook.php

3. **Проверка бота:** Отправьте сообщение боту в Telegram

## ⚠️ Важно

- **Webhook URL:** Должен быть `/bot/webhook.php` (не `/bots/wrbhook.php`)
- **Безопасность:** Не публикуйте `.env` файл
- **Пароли:** В production используйте более сложные пароли

## 📚 Дополнительная документация

- [DEPLOY_GUIDE.md](DEPLOY_GUIDE.md) - Полный гайд
- [ENV_SETUP_EXAMPLE.md](ENV_SETUP_EXAMPLE.md) - Пример .env
- [GITHUB_ACTIONS_SETUP.md](GITHUB_ACTIONS_SETUP.md) - Автодеплой
- **[APPLY_CHANGES_VPS.md](APPLY_CHANGES_VPS.md)** ⭐ **Используйте для применения изменений на VPS**

---

## 🔄 Применение изменений на VPS

После любых изменений в коде выполните на VPS:

```bash
cd /ssd/www/bots/botsalebestwebstudio
git pull origin main
composer install --no-dev --optimize-autoloader
php migrations/migrate.php
sudo systemctl reload apache2
```

**Или используйте скрипт:**
```bash
bash deploy/deploy.sh
```

**📖 Подробная инструкция:** [APPLY_CHANGES_VPS.md](APPLY_CHANGES_VPS.md)

---

**✅ Готово! Бот должен работать на https://botsale.1tlt.ru**
