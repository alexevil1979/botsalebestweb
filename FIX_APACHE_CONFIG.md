# 🔧 Исправление конфигурации Apache

## ❌ Проблема

Ошибка при запуске Apache:
```
AH00526: Syntax error on line 40 of /etc/apache2/sites-enabled/botsalebestwebstudio.conf:
Can't parse value expression : Variable 'HTTP_X_TELEGRAM_BOT_API_SECRET_TOKEN' does not exist
```

## ✅ Причина

Строка с `RequestHeader set X-Telegram-Bot-Api-Secret-Token "expr=%{HTTP_X_TELEGRAM_BOT_API_SECRET_TOKEN}"` использует неправильный синтаксис для Apache 2.4+.

## 🚀 Решение

Удалите проблемную строку из конфигурации Apache. PHP автоматически получает заголовки через `$_SERVER`.

### Шаг 1: Отредактируйте конфигурацию

```bash
sudo nano /etc/apache2/sites-available/botsalebestwebstudio.conf
```

### Шаг 2: Найдите и удалите проблемные строки

Найдите и удалите эти строки (обычно в конце файла):

```apache
# Передача заголовков для webhook secret
<IfModule mod_headers.c>
    RequestHeader set X-Telegram-Bot-Api-Secret-Token "expr=%{HTTP_X_TELEGRAM_BOT_API_SECRET_TOKEN}"
</IfModule>
```

### Шаг 3: Правильная конфигурация

Конфигурация должна заканчиваться так:

```apache
    # Защита других скрытых файлов
    <FilesMatch "^\.">
        Require all denied
    </FilesMatch>
</VirtualHost>
```

### Шаг 4: Проверьте конфигурацию

```bash
sudo apache2ctl configtest
```

Должно быть: `Syntax OK`

### Шаг 5: Перезапустите Apache

```bash
sudo systemctl restart apache2
```

### Шаг 6: Проверьте статус

```bash
sudo systemctl status apache2
```

## 📝 Примечание

PHP автоматически получает заголовки HTTP через `$_SERVER['HTTP_X_TELEGRAM_BOT_API_SECRET_TOKEN']`, поэтому дополнительная настройка в Apache не требуется.

В коде бота заголовок уже читается правильно через:
```php
$_SERVER['HTTP_X_TELEGRAM_BOT_API_SECRET_TOKEN']
```

## ✅ Проверка после исправления

```bash
# Проверка синтаксиса
sudo apache2ctl configtest

# Проверка статуса
sudo systemctl status apache2

# Проверка логов
sudo tail -f /var/log/apache2/error.log
```

---

**✅ После исправления Apache должен запуститься без ошибок!**
