# 🔧 Исправление проблем с Git и Webhook

## ❌ Проблема 1: Git не может выполнить pull

Ошибка:
```
fatal: detected dubious ownership in repository
```

## ✅ Решение

Выполните на VPS:

```bash
cd /ssd/www/bots/botsalebestwebstudio

# Исправление прав доступа Git
git config --global --add safe.directory /ssd/www/bots/botsalebestwebstudio

# Теперь можно выполнить pull
git pull origin main
```

---

## ❌ Проблема 2: Ошибка в TelegramAPI.php

Ошибка:
```
Fatal error: Uncaught TypeError: Core\TelegramAPI::request(): Return value must be of type ?array, bool returned
```

## ✅ Решение

Это означает, что код не обновился. Выполните полное обновление:

```bash
cd /ssd/www/bots/botsalebestwebstudio

# 1. Исправление Git
git config --global --add safe.directory /ssd/www/bots/botsalebestwebstudio

# 2. Получение изменений
git pull origin main

# 3. Проверка, что файл обновился
head -20 core/TelegramAPI.php | tail -5

# 4. Если файл не обновился, принудительное обновление
git fetch origin main
git reset --hard origin/main

# 5. Обновление зависимостей
composer install --no-dev --optimize-autoloader

# 6. Перезагрузка Apache
sudo systemctl reload apache2

# 7. Проверка webhook
php bot/setup-webhook.php
```

---

## 🚀 ПОЛНЫЕ КОМАНДЫ ДЛЯ ИСПРАВЛЕНИЯ

```bash
# 1. Переход в директорию
cd /ssd/www/bots/botsalebestwebstudio

# 2. Исправление Git
git config --global --add safe.directory /ssd/www/bots/botsalebestwebstudio

# 3. Получение изменений
git pull origin main

# 4. Если pull не работает, принудительное обновление
git fetch origin main
git reset --hard origin/main

# 5. Обновление зависимостей
composer install --no-dev --optimize-autoloader

# 6. Перезагрузка Apache
sudo systemctl reload apache2

# 7. Проверка webhook
php bot/setup-webhook.php
```

---

## ✅ Проверка после исправления

```bash
# Проверка версии файла TelegramAPI.php
grep -n "json_decode returns null" core/TelegramAPI.php

# Должна быть строка с этим текстом (означает, что исправление применено)

# Проверка webhook
php bot/setup-webhook.php

# Должно вывести:
# ✅ Webhook set successfully!
```

---

**✅ После выполнения всех команд проблемы должны быть исправлены!**
