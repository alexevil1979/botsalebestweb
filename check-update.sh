#!/bin/bash

# Скрипт для проверки обновлений на VPS

echo "🔍 Проверка обновлений на VPS"
echo "================================"
echo ""

# Путь к проекту
PROJECT_PATH="/ssd/www/bots/botsalebestwebstudio"
cd "$PROJECT_PATH" || exit 1

echo "📍 Текущая директория: $(pwd)"
echo ""

# Проверка Git
echo "📦 Проверка Git:"
echo "  - Текущая ветка: $(git branch --show-current)"
echo "  - Последний коммит: $(git log -1 --oneline)"
echo "  - Статус:"
git status --short
echo ""

# Проверка удаленного репозитория
echo "🌐 Проверка удаленного репозитория:"
git fetch origin main 2>&1 | head -5
echo ""

# Сравнение локальной и удаленной версий
LOCAL_COMMIT=$(git rev-parse HEAD)
REMOTE_COMMIT=$(git rev-parse origin/main 2>/dev/null)

if [ -z "$REMOTE_COMMIT" ]; then
    echo "❌ Не удалось получить информацию об удаленной ветке"
    echo "   Попробуйте: git fetch origin main"
else
    echo "  - Локальный коммит:  ${LOCAL_COMMIT:0:7}"
    echo "  - Удаленный коммит:   ${REMOTE_COMMIT:0:7}"
    
    if [ "$LOCAL_COMMIT" = "$REMOTE_COMMIT" ]; then
        echo "  ✅ Локальная версия актуальна"
    else
        echo "  ⚠️  Есть обновления на удаленном репозитории!"
        echo "  📝 Новые коммиты:"
        git log --oneline HEAD..origin/main | head -5
    fi
fi
echo ""

# Проверка файла TelegramAPI.php
echo "📄 Проверка core/TelegramAPI.php:"
if [ -f "core/TelegramAPI.php" ]; then
    if grep -q "json_decode returns null" core/TelegramAPI.php; then
        echo "  ✅ Исправление применено (строка с 'json_decode returns null' найдена)"
    else
        echo "  ❌ Исправление НЕ применено (строка не найдена)"
        echo "  📝 Текущая версия файла:"
        echo "     $(git log -1 --format='%h %s' -- core/TelegramAPI.php 2>/dev/null || echo 'Не в git')"
    fi
else
    echo "  ❌ Файл не найден!"
fi
echo ""

# Проверка версии файла
echo "📋 Информация о файле TelegramAPI.php:"
if [ -f "core/TelegramAPI.php" ]; then
    echo "  - Размер: $(wc -l < core/TelegramAPI.php) строк"
    echo "  - Последнее изменение: $(stat -c %y core/TelegramAPI.php 2>/dev/null || stat -f %Sm core/TelegramAPI.php 2>/dev/null)"
    echo "  - Хеш: $(md5sum core/TelegramAPI.php 2>/dev/null | cut -d' ' -f1 || md5 core/TelegramAPI.php 2>/dev/null | cut -d' ' -f4)"
fi
echo ""

# Проверка Git конфигурации
echo "⚙️  Проверка Git конфигурации:"
SAFE_DIR=$(git config --global --get-all safe.directory | grep "$PROJECT_PATH")
if [ -n "$SAFE_DIR" ]; then
    echo "  ✅ safe.directory настроен: $SAFE_DIR"
else
    echo "  ⚠️  safe.directory не настроен"
    echo "  💡 Выполните: git config --global --add safe.directory $PROJECT_PATH"
fi
echo ""

echo "================================"
echo "✅ Проверка завершена"
