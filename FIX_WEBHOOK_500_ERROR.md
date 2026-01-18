# 🔧 Исправление ошибки 500 на webhook endpoint

## ❌ Проблема

Webhook установлен успешно, но при обращении к `https://botsale.1tlt.ru/bot/webhook.php` получаем ошибку 500 Internal Server Error.

## ✅ РЕШЕНИЕ - ВЫПОЛНИТЕ ВСЕ КОМАНДЫ:

```bash
# 1. Проверка логов Apache на ошибки
sudo tail -n 50 /var/log/apache2/botsalebestwebstudio_error.log

# 2. Проверка логов PHP-FPM
sudo tail -n 50 /var/log/php8.1-fpm.log
# или
sudo journalctl -u php8.1-fpm -n 50

# 3. Проверка прав доступа на файл webhook.php
ls -la /ssd/www/bots/botsalebestwebstudio/bot/webhook.php

# 4. Проверка синтаксиса PHP файла
php -l /ssd/www/bots/botsalebestwebstudio/bot/webhook.php

# 5. Проверка наличия .env файла
ls -la /ssd/www/bots/botsalebestwebstudio/.env

# 6. Проверка подключения к БД
php -r "
require 'vendor/autoload.php';
Core\Config::load('/ssd/www/bots/botsalebestwebstudio/.env');
try {
    \$result = Core\Database::fetch('SELECT 1 as test');
    echo '✅ БД работает\n';
} catch (Exception \$e) {
    echo '❌ Ошибка БД: ' . \$e->getMessage() . '\n';
}
"

# 7. Проверка подключения к Redis
php -r "
require 'vendor/autoload.php';
Core\Config::load('/ssd/www/bots/botsalebestwebstudio/.env');
try {
    Core\Redis::get('test');
    echo '✅ Redis работает\n';
} catch (Exception \$e) {
    echo '❌ Ошибка Redis: ' . \$e->getMessage() . '\n';
}
"
```

## 🔍 Частые причины ошибки 500

### 1. Отсутствует .env файл

```bash
# Проверка .env файла
ls -la /ssd/www/bots/botsalebestwebstudio/.env

# Если файла нет, создайте его на основе env.example.txt
cp /ssd/www/bots/botsalebestwebstudio/env.example.txt /ssd/www/bots/botsalebestwebstudio/.env
nano /ssd/www/bots/botsalebestwebstudio/.env
```

### 2. Неправильные права доступа

```bash
# Исправление прав доступа
sudo chown -R www-data:www-data /ssd/www/bots/botsalebestwebstudio
sudo chmod -R 755 /ssd/www/bots/botsalebestwebstudio
sudo chmod 644 /ssd/www/bots/botsalebestwebstudio/.env
```

### 3. Ошибки в PHP коде

```bash
# Проверка синтаксиса всех PHP файлов
find /ssd/www/bots/botsalebestwebstudio -name "*.php" -exec php -l {} \;
```

### 4. Отсутствуют зависимости Composer

```bash
# Установка зависимостей
cd /ssd/www/bots/botsalebestwebstudio
composer install --no-dev --optimize-autoloader
```

### 5. Ошибки подключения к БД или Redis

Проверьте настройки в `.env`:
- `DB_HOST=127.0.0.1`
- `DB_PORT=3306`
- `DB_NAME=telegram_bot`
- `DB_USER=telegram_bot`
- `DB_PASS=qweasd333123`

## 🔧 Детальная диагностика

```bash
# Включение отображения ошибок PHP (временно для диагностики)
# Редактирование webhook.php
sudo nano /ssd/www/bots/botsalebestwebstudio/bot/webhook.php
```

Добавьте в начало файла (после `<?php`):

```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', '/ssd/www/bots/botsalebestwebstudio/logs/php_errors.log');
```

**ВАЖНО:** После диагностики удалите эти строки для безопасности!

```bash
# Создание директории для логов
mkdir -p /ssd/www/bots/botsalebestwebstudio/logs
chmod 777 /ssd/www/bots/botsalebestwebstudio/logs

# Проверка webhook снова
curl -v https://botsale.1tlt.ru/bot/webhook.php

# Просмотр логов ошибок
tail -n 50 /ssd/www/bots/botsalebestwebstudio/logs/php_errors.log
```

## ✅ Проверка после исправления

```bash
# 1. Проверка webhook endpoint
curl -I https://botsale.1tlt.ru/bot/webhook.php

# 2. Проверка с правильным секретом
curl -X POST https://botsale.1tlt.ru/bot/webhook.php \
  -H "X-Telegram-Bot-Api-Secret-Token: fkew323f32f23f2332f" \
  -H "Content-Type: application/json" \
  -d '{"update_id": 123}'

# 3. Проверка логов
sudo tail -n 20 /var/log/apache2/botsalebestwebstudio_error.log
```

## 🔍 Альтернативная проверка

```bash
# Прямой запуск webhook.php через CLI для проверки
cd /ssd/www/bots/botsalebestwebstudio
php bot/webhook.php
```

Если есть ошибки, они будут видны в консоли.

---

**✅ После исправления ошибки webhook должен работать корректно!**
