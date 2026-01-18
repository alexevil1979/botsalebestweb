# 🔍 Проверка обновлений на VPS

## ⚡ Быстрая проверка

Выполните на VPS:

```bash
cd /ssd/www/bots/botsalebestwebstudio

# Использование скрипта проверки
bash check-update.sh
```

---

## 📋 Ручная проверка (полные команды)

```bash
# 1. Переход в директорию
cd /ssd/www/bots/botsalebestwebstudio

# 2. Проверка текущей версии
echo "Текущий коммит:"
git log -1 --oneline

# 3. Получение информации об удаленной версии
git fetch origin main

# 4. Сравнение версий
echo "Локальный коммит:"
git rev-parse HEAD

echo "Удаленный коммит:"
git rev-parse origin/main

# 5. Проверка, есть ли обновления
LOCAL=$(git rev-parse HEAD)
REMOTE=$(git rev-parse origin/main)

if [ "$LOCAL" != "$REMOTE" ]; then
    echo "⚠️ Есть обновления!"
    echo "Новые коммиты:"
    git log --oneline HEAD..origin/main
else
    echo "✅ Локальная версия актуальна"
fi

# 6. Проверка файла TelegramAPI.php
echo ""
echo "Проверка core/TelegramAPI.php:"
if grep -q "json_decode returns null" core/TelegramAPI.php; then
    echo "✅ Исправление применено"
else
    echo "❌ Исправление НЕ применено"
    echo "Текущая версия файла:"
    git log -1 --format='%h %s' -- core/TelegramAPI.php
fi
```

---

## 🔧 Если изменения не подтягиваются

### Вариант 1: Исправление Git и принудительное обновление

```bash
cd /ssd/www/bots/botsalebestwebstudio

# Исправление Git
git config --global --add safe.directory /ssd/www/bots/botsalebestwebstudio

# Получение изменений
git fetch origin main

# Принудительное обновление
git reset --hard origin/main

# Проверка
git log -1 --oneline
```

### Вариант 2: Пересоздание репозитория

```bash
cd /ssd/www/bots

# Создание резервной копии .env
cp botsalebestwebstudio/.env /tmp/.env.backup

# Удаление старого репозитория
rm -rf botsalebestwebstudio

# Клонирование заново
git clone https://github.com/alexevil1979/botsalebestweb.git botsalebestwebstudio

# Восстановление .env
cp /tmp/.env.backup botsalebestwebstudio/.env

# Переход в директорию
cd botsalebestwebstudio

# Установка зависимостей
composer install --no-dev --optimize-autoloader

# Проверка
php bot/setup-webhook.php
```

---

## ✅ Проверка после обновления

```bash
# Проверка версии файла TelegramAPI.php
grep -n "json_decode returns null" core/TelegramAPI.php

# Должна быть строка (например, строка 136 или 137)

# Проверка webhook
php bot/setup-webhook.php

# Должно работать без ошибок
```

---

## 📊 Полная диагностика

```bash
cd /ssd/www/bots/botsalebestwebstudio

echo "=== Git статус ==="
git status

echo ""
echo "=== Последние коммиты ==="
git log -5 --oneline

echo ""
echo "=== Удаленные ветки ==="
git branch -r

echo ""
echo "=== Проверка TelegramAPI.php ==="
if [ -f "core/TelegramAPI.php" ]; then
    echo "Файл существует"
    echo "Строк в файле: $(wc -l < core/TelegramAPI.php)"
    echo "Проверка исправления:"
    if grep -q "json_decode returns null" core/TelegramAPI.php; then
        echo "✅ Исправление найдено"
        grep -n "json_decode returns null" core/TelegramAPI.php
    else
        echo "❌ Исправление НЕ найдено"
    fi
else
    echo "❌ Файл не найден!"
fi
```

---

**✅ Используйте эти команды для проверки, подтягиваются ли изменения на VPS!**
