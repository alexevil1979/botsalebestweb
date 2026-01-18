# ✅ Проверка работы webhook

## 🎉 Webhook работает!

HTTP 403 Forbidden - это **нормальное поведение**! Webhook проверяет секрет и блокирует запросы без правильного заголовка.

## ✅ Проверка с правильным секретом

```bash
# 1. Проверка webhook с правильным секретом
curl -X POST https://botsale.1tlt.ru/bot/webhook.php \
  -H "X-Telegram-Bot-Api-Secret-Token: fkew323f32f23f2332f" \
  -H "Content-Type: application/json" \
  -d '{"update_id": 123}'

# 2. Проверка с реальным обновлением от Telegram
curl -X POST https://botsale.1tlt.ru/bot/webhook.php \
  -H "X-Telegram-Bot-Api-Secret-Token: fkew323f32f23f2332f" \
  -H "Content-Type: application/json" \
  -d '{
    "update_id": 123,
    "message": {
      "message_id": 1,
      "from": {
        "id": 123456789,
        "is_bot": false,
        "first_name": "Test",
        "username": "testuser",
        "language_code": "ru"
      },
      "chat": {
        "id": 123456789,
        "first_name": "Test",
        "username": "testuser",
        "type": "private"
      },
      "date": 1705600000,
      "text": "/start"
    }
  }'
```

**Ожидаемый результат:**
- HTTP 200 OK (пустой ответ или "OK")
- Не должно быть ошибок в логах

## ✅ Проверка информации о webhook

```bash
# Проверка статуса webhook в Telegram API
curl "https://api.telegram.org/bot8496559310:AAFDB-mRyv4pOh_4Sj2LdtNWYZ4XK0v_DE8/getWebhookInfo"
```

**Ожидаемый результат:**
```json
{
  "ok": true,
  "result": {
    "url": "https://botsale.1tlt.ru/bot/webhook.php",
    "has_custom_certificate": false,
    "pending_update_count": 0,
    "max_connections": 40
  }
}
```

## ✅ Тестирование бота в Telegram

1. **Откройте Telegram** и найдите бота: `@bestwebstudiobot` или `@bestwebstudiorubot`

2. **Отправьте команду** `/start` боту

3. **Проверьте ответ:**
   - Бот должен ответить приветствием
   - Должен начаться диалог

4. **Проверьте логи в реальном времени:**
```bash
# Просмотр логов Apache
sudo tail -f /var/log/apache2/botsalebestwebstudio_error.log
```

## ✅ Проверка в админ-панели

1. **Откройте админ-панель:** `https://botsale.1tlt.ru/admin/`

2. **Войдите** с паролем: `qweasd333123`

3. **Проверьте разделы:**
   - **Диалоги** - должен появиться новый диалог после отправки `/start`
   - **Пользователи** - должен появиться новый пользователь
   - **Сообщения** - должны сохраняться все сообщения

## 🔍 Проверка базы данных

```bash
# Проверка последних сообщений
mysql -u telegram_bot -p telegram_bot -e "
SELECT m.id, m.direction, LEFT(m.text, 50) as text, m.created_at 
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

# Проверка пользователей
mysql -u telegram_bot -p telegram_bot -e "
SELECT u.id, u.telegram_id, u.first_name, u.username, u.created_at 
FROM users u 
ORDER BY u.created_at DESC 
LIMIT 5;
"
```

## 🔍 Проверка Redis

```bash
# Проверка данных в Redis
redis-cli

# В Redis CLI выполните:
KEYS *
# Должны быть ключи вида: dialog:USER_ID:step, dialog:USER_ID:data

# Выход из Redis CLI
exit
```

## ✅ Чек-лист проверки

- [x] Webhook установлен (403 Forbidden без секрета - нормально)
- [ ] Webhook отвечает 200 OK с правильным секретом
- [ ] Бот отвечает на команду /start в Telegram
- [ ] Сообщения сохраняются в БД
- [ ] Диалоги создаются в БД
- [ ] Пользователи создаются в БД
- [ ] Админ-панель показывает данные

## 🔧 Если бот не отвечает

```bash
# 1. Проверка логов в реальном времени
sudo tail -f /var/log/apache2/botsalebestwebstudio_error.log

# 2. Проверка подключения к БД
php -r "
require 'vendor/autoload.php';
Core\Config::load('.env');
try {
    \$result = Core\Database::fetch('SELECT 1 as test');
    echo '✅ БД работает\n';
} catch (Exception \$e) {
    echo '❌ Ошибка БД: ' . \$e->getMessage() . '\n';
}
"

# 3. Проверка подключения к Redis
redis-cli ping

# 4. Проверка webhook с реальным обновлением
# Отправьте /start боту и проверьте логи
```

---

**✅ Webhook работает! Теперь протестируйте бота в Telegram!**
