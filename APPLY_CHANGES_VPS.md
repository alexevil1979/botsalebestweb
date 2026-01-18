# 🚀 ПОЛНАЯ ИНСТРУКЦИЯ ПО ОБНОВЛЕНИЮ НА VPS

## 📍 Путь проекта
```
/ssd/www/bots/botsalebestwebstudio
```

---

## ⚡ ПОЛНЫЕ КОМАНДЫ ДЛЯ ОБНОВЛЕНИЯ (ВСЕГДА ИСПОЛЬЗУЙТЕ ЭТО!)

### 📋 КОПИРУЙТЕ И ВЫПОЛНЯЙТЕ ВСЕ КОМАНДЫ ПОСЛЕДОВАТЕЛЬНО:

```bash
# 1. Подключение к серверу
ssh root@your-server-ip

# 2. Переход в директорию проекта
cd /ssd/www/bots/botsalebestwebstudio

# 3. Получение изменений из GitHub
git pull origin main

# 4. Обновление зависимостей
composer install --no-dev --optimize-autoloader

# 5. Запуск миграций БД (в правильном порядке!)
php migrations/migrate.php
php migrations/add_preferred_language.php

# 6. Перезагрузка Apache
sudo systemctl reload apache2

# 7. Проверка webhook (опционально)
php bot/setup-webhook.php
```

---

## 🔄 Альтернатива: Использование скрипта деплоя

```bash
cd /ssd/www/bots/botsalebestwebstudio
git pull origin main
bash deploy/deploy.sh
```

---

## ✅ Проверка после обновления

```bash
# Проверка статуса Apache
sudo systemctl status apache2

# Проверка подключения к БД
php -r "
require 'vendor/autoload.php';
Core\Config::load('.env');
try {
    \$result = Core\Database::fetch('SELECT 1 as test');
    echo '✅ Подключение к БД успешно!';
} catch (Exception \$e) {
    echo '❌ Ошибка: ' . \$e->getMessage();
}
"

# Проверка webhook
php bot/setup-webhook.php

# Проверка логов
sudo tail -f /var/log/apache2/botsalebestwebstudio_error.log
```

---

## 📝 Важные данные проекта

- **Домен:** `botsale.1tlt.ru`
- **Путь на VPS:** `/ssd/www/bots/botsalebestwebstudio`
- **Репозиторий:** `https://github.com/alexevil1979/botsalebestweb.git`

---

**✅ После выполнения всех команд изменения применены на VPS!**
