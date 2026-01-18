# 🔧 Исправление проблемы с миграцией

## ❌ Проблема

Миграция выполнила 0 statements, таблицы не создаются:
```
✓ Executed 0 statements
✓ 0/6 tables verified
```

## ✅ Решение

### Вариант 1: Импорт схемы напрямую (рекомендуется)

```bash
cd /ssd/www/bots/botsalebestwebstudio

# Импортируйте схему напрямую через MySQL
mysql -u root -p telegram_bot < schema.sql

# Проверьте таблицы
mysql -u root -p telegram_bot -e "SHOW TABLES;"
```

Должны быть созданы таблицы:
- users
- dialogs
- messages
- leads
- services
- events

### Вариант 2: Выполнение SQL вручную

```bash
mysql -u root -p telegram_bot
```

Затем выполните:

```sql
SOURCE /ssd/www/bots/botsalebestwebstudio/schema.sql;
SHOW TABLES;
EXIT;
```

### Вариант 3: Проверка и исправление миграции

```bash
cd /ssd/www/bots/botsalebestwebstudio

# Получите последние изменения
git pull origin main

# Попробуйте миграцию снова
php migrations/migrate.php
```

## 🔍 Диагностика

### Проверка файла schema.sql

```bash
# Проверьте, что файл существует
ls -la schema.sql

# Проверьте размер файла
wc -l schema.sql

# Проверьте первые строки
head -20 schema.sql
```

### Проверка подключения к БД

```bash
php -r "
require 'vendor/autoload.php';
Core\Config::load('.env');
try {
    \$pdo = Core\Database::getInstance();
    \$result = \$pdo->query('SELECT DATABASE() as db')->fetch();
    echo '✅ Подключение успешно!' . PHP_EOL;
    echo 'База данных: ' . \$result['db'] . PHP_EOL;
} catch (Exception \$e) {
    echo '❌ Ошибка: ' . \$e->getMessage() . PHP_EOL;
}
"
```

### Проверка прав доступа

```bash
mysql -u root -p -e "SHOW GRANTS FOR 'root'@'localhost';"
```

## 🚀 Быстрое исправление (полный сброс)

Если ничего не помогает:

```bash
cd /ssd/www/bots/botsalebestwebstudio

# 1. Удалите базу (ОСТОРОЖНО! Это удалит все данные)
mysql -u root -p -e "DROP DATABASE IF EXISTS telegram_bot;"

# 2. Создайте базу заново
mysql -u root -p -e "CREATE DATABASE telegram_bot CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 3. Импортируйте схему напрямую
mysql -u root -p telegram_bot < schema.sql

# 4. Проверьте таблицы
mysql -u root -p telegram_bot -e "SHOW TABLES;"

# 5. Выполните дополнительную миграцию
php migrations/add_preferred_language.php
```

## ✅ Проверка после исправления

```bash
# Проверка всех таблиц
mysql -u root -p telegram_bot -e "SHOW TABLES;"

# Проверка структуры таблицы users
mysql -u root -p telegram_bot -e "DESCRIBE users;"

# Проверка через PHP
php -r "
require 'vendor/autoload.php';
Core\Config::load('.env');
\$tables = Core\Database::fetchAll('SHOW TABLES');
echo 'Таблицы в БД:' . PHP_EOL;
foreach (\$tables as \$table) {
    echo '  ✓ ' . array_values(\$table)[0] . PHP_EOL;
}
"
```

---

**✅ После выполнения этих шагов все таблицы должны быть созданы!**
