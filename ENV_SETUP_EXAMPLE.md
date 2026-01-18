# 🔐 Пример настройки .env файла

## 📋 Данные для вашего проекта

**Домен:** `botsale.1tlt.ru`  
**Репозиторий:** `https://github.com/alexevil1979/botsalebestweb.git`

## ⚙️ Пример .env файла

Создайте файл `.env` в корне проекта:

```env
# Telegram Bot
TELEGRAM_BOT_TOKEN=8496559310:AAFDB-mRyv4pOh_4Sj2LdtNWYZ4XK0v_DE8
TELEGRAM_WEBHOOK_SECRET=fkew323f32f23f2332f
TELEGRAM_WEBHOOK_URL=https://botsale.1tlt.ru/bot/webhook.php

# Database
DB_HOST=localhost
DB_NAME=telegram_bot
DB_USER=root
DB_PASS=qweasd333123
DB_CHARSET=utf8mb4
DB_SOCKET=/tmp/mysql.sock  # Используйте сокет вместо host для подключения

# Redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_PASSWORD=
REDIS_DB=0

# Admin Panel
ADMIN_USERNAME=admin
ADMIN_PASSWORD=qweasd333123
ADMIN_SESSION_LIFETIME=3600

# LLM (Optional - отключено по умолчанию)
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

## 🔧 Команды для настройки

### 1. Создайте .env файл
```bash
cd /ssd/www/bots/botsalebestwebstudio
cp env.example.txt .env
nano .env
```

### 2. Заполните данные (скопируйте из примера выше)

### 3. Настройте webhook
```bash
php bot/setup-webhook.php
```

**Проверьте, что webhook URL правильный:**
- ✅ `https://botsale.1tlt.ru/bot/webhook.php` (правильно)
- ❌ `https://botsale.1tlt.ru/bots/wrbhook.php` (неправильно - опечатка)

## ⚠️ Важные замечания

1. **Webhook URL:** Должен быть `/bot/webhook.php`, а не `/bots/wrbhook.php`
2. **Безопасность:** Не публикуйте `.env` файл в Git (он уже в `.gitignore`)
3. **Пароли:** Используйте сильные пароли в production
4. **Права доступа:** Убедитесь, что `.env` недоступен через веб-сервер

## 🔍 Проверка настройки

### Проверка webhook
```bash
php bot/setup-webhook.php
```

### Проверка подключения к БД
```bash
php -r "require 'vendor/autoload.php'; Core\Config::load('.env'); var_dump(Core\Database::fetch('SELECT 1'));"
```

### Проверка Redis
```bash
redis-cli ping
```

## 📝 Структура URL

- **Домен:** `botsale.1tlt.ru`
- **Webhook:** `https://botsale.1tlt.ru/bot/webhook.php`
- **Админка:** `https://botsale.1tlt.ru/admin`
- **Главная:** `https://botsale.1tlt.ru/`

---

**✅ После настройки .env файла проект готов к работе!**
