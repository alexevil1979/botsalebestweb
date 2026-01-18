# ✅ Проверка работы Telegram бота

## 🎉 PHP работает! Теперь проверим бота

## 📋 Шаг 1: Проверка webhook

```bash
# 1. Переход в директорию проекта
cd /ssd/www/bots/botsalebestwebstudio

# 2. Проверка настройки webhook
php bot/setup-webhook.php

# 3. Проверка информации о webhook
curl "https://api.telegram.org/bot8496559310:AAFDB-mRyv4pOh_4Sj2LdtNWYZ4XK0v_DE8/getWebhookInfo"
```

**Ожидаемый результат:**
- Webhook должен быть установлен на `https://botsale.1tlt.ru/bot/webhook.php`
- Статус должен быть `pending` или `ok`

## 📋 Шаг 2: Проверка работы webhook endpoint

```bash
# Проверка доступности webhook endpoint
curl -I https://botsale.1tlt.ru/bot/webhook.php

# Проверка с правильным секретом
curl -X POST https://botsale.1tlt.ru/bot/webhook.php \
  -H "X-Telegram-Bot-Api-Secret-Token: fkew323f32f23f2332f" \
  -H "Content-Type: application/json" \
  -d '{"update_id": 123}'
```

**Ожидаемый результат:**
- HTTP 200 OK или другой успешный статус
- Не должно быть ошибок в логах

## 📋 Шаг 3: Проверка базы данных

```bash
# Проверка подключения к БД
php -r "
require 'vendor/autoload.php';
Core\Config::load('.env');
try {
    \$result = Core\Database::fetch('SELECT 1 as test');
    echo '✅ Подключение к БД успешно!\n';
} catch (Exception \$e) {
    echo '❌ Ошибка: ' . \$e->getMessage() . '\n';
}
"

# Проверка таблиц
mysql -u telegram_bot -p telegram_bot -e "SHOW TABLES;"
```

**Ожидаемый результат:**
- Должны быть таблицы: `users`, `dialogs`, `messages`, `leads`, `services`, `events`

## 📋 Шаг 4: Проверка Redis

```bash
# Проверка подключения к Redis
redis-cli ping

# Проверка статуса Redis
sudo systemctl status redis-server
```

**Ожидаемый результат:**
- `PONG` от Redis
- Сервис должен быть активен

## 📋 Шаг 5: Проверка админ-панели

```bash
# Проверка доступности админ-панели
curl -I https://botsale.1tlt.ru/admin/

# Проверка страницы логина
curl -I https://botsale.1tlt.ru/admin/login.php
```

**Ожидаемый результат:**
- HTTP 200 OK
- Должна открываться страница логина

## 📋 Шаг 6: Проверка логов

```bash
# Проверка логов Apache
sudo tail -n 50 /var/log/apache2/botsalebestwebstudio_error.log

# Проверка логов PHP (если есть)
sudo tail -n 50 /var/log/php8.1-fpm.log

# Проверка логов приложения
ls -la /ssd/www/bots/botsalebestwebstudio/logs/
tail -n 50 /ssd/www/bots/botsalebestwebstudio/logs/*.log 2>/dev/null || echo "Логи не найдены"
```

## 📋 Шаг 7: Тестирование бота в Telegram

1. **Откройте Telegram** и найдите бота: `@bestwebstudiobot` или `@bestwebstudiorubot`

2. **Отправьте команду** `/start` боту

3. **Проверьте ответ:**
   - Бот должен ответить приветствием
   - Должен начаться диалог

4. **Проверьте в админ-панели:**
   - Откройте `https://botsale.1tlt.ru/admin/`
   - Войдите с паролем: `qweasd333123`
   - Проверьте раздел "Диалоги" - должен появиться новый диалог
   - Проверьте раздел "Пользователи" - должен появиться новый пользователь

## 📋 Шаг 8: Проверка обработки сообщений

```bash
# Проверка последних сообщений в БД
mysql -u telegram_bot -p telegram_bot -e "
SELECT m.id, m.direction, m.text, m.created_at 
FROM messages m 
ORDER BY m.created_at DESC 
LIMIT 5;
"

# Проверка последних диалогов
mysql -u telegram_bot -p telegram_bot -e "
SELECT d.id, d.current_step, d.status, d.created_at 
FROM dialogs d 
ORDER BY d.created_at DESC 
LIMIT 5;
"
```

## 📋 Шаг 9: Проверка услуг

```bash
# Проверка услуг в БД
mysql -u telegram_bot -p telegram_bot -e "
SELECT id, name, price_from, price_to, active 
FROM services;
"
```

**Ожидаемый результат:**
- Должны быть услуги в базе данных
- Они должны отображаться в админ-панели

## 📋 Шаг 10: Полная проверка системы

```bash
# Проверка всех сервисов
sudo systemctl status apache2
sudo systemctl status mysql
sudo systemctl status redis-server
sudo service php8.1-fpm status

# Проверка прав доступа
ls -la /ssd/www/bots/botsalebestwebstudio/ | head -20

# Проверка .env файла
ls -la /ssd/www/bots/botsalebestwebstudio/.env
```

## ✅ Чек-лист проверки

- [ ] PHP обрабатывается корректно
- [ ] Webhook установлен и работает
- [ ] База данных подключена
- [ ] Redis работает
- [ ] Админ-панель доступна
- [ ] Бот отвечает на команду /start
- [ ] Сообщения сохраняются в БД
- [ ] Диалоги создаются в БД
- [ ] Админ-панель показывает данные

## 🔧 Если что-то не работает

### Webhook не работает

```bash
# Переустановка webhook
php bot/setup-webhook.php

# Проверка логов
sudo tail -n 50 /var/log/apache2/botsalebestwebstudio_error.log
```

### Бот не отвечает

```bash
# Проверка логов webhook
sudo tail -f /var/log/apache2/botsalebestwebstudio_error.log

# Проверка подключения к БД
php -r "require 'vendor/autoload.php'; Core\Config::load('.env'); Core\Database::fetch('SELECT 1');"
```

### Админ-панель не работает

```bash
# Проверка прав доступа
sudo chown -R www-data:www-data /ssd/www/bots/botsalebestwebstudio
sudo chmod -R 755 /ssd/www/bots/botsalebestwebstudio

# Проверка .env файла
cat /ssd/www/bots/botsalebestwebstudio/.env | grep -v "PASS\|SECRET\|TOKEN"
```

---

**✅ После выполнения всех проверок бот должен работать корректно!**
