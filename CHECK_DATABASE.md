# 🔍 Проверка базы данных

## Проблема: Таблицы не создаются

Если после выполнения `php migrations/migrate.php` таблицы не создаются, выполните проверку:

### Шаг 1: Проверьте подключение к БД

```bash
cd /ssd/www/bots/botsalebestwebstudio
php -r "
require 'vendor/autoload.php';
Core\Config::load('.env');
try {
    \$result = Core\Database::fetch('SELECT DATABASE() as db');
    echo '✅ Подключение к БД успешно!';
    echo PHP_EOL;
    echo 'База данных: ' . (\$result['db'] ?? 'не определена');
    echo PHP_EOL;
} catch (Exception \$e) {
    echo '❌ Ошибка: ' . \$e->getMessage();
    echo PHP_EOL;
}
"
```

### Шаг 2: Проверьте существование базы данных

```bash
mysql -u root -p -e "SHOW DATABASES LIKE 'telegram_bot';"
```

Если база не существует, создайте её:

```bash
mysql -u root -p
```

```sql
CREATE DATABASE IF NOT EXISTS telegram_bot CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

### Шаг 3: Проверьте .env файл

```bash
cat .env | grep DB_
```

Должно быть:
```env
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=telegram_bot
DB_USER=root
DB_PASS=qweasd333123
```

### Шаг 4: Проверьте существующие таблицы

```bash
mysql -u root -p telegram_bot -e "SHOW TABLES;"
```

### Шаг 5: Выполните миграцию вручную

Если миграция не работает, можно импортировать схему напрямую:

```bash
mysql -u root -p telegram_bot < schema.sql
```

Затем проверьте:

```bash
mysql -u root -p telegram_bot -e "SHOW TABLES;"
```

Должны быть таблицы:
- users
- dialogs
- messages
- leads
- services
- events

### Шаг 6: Проверьте права доступа

```bash
mysql -u root -p
```

```sql
GRANT ALL PRIVILEGES ON telegram_bot.* TO 'root'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

## Быстрое решение

Если ничего не помогает, выполните полный сброс:

```bash
cd /ssd/www/bots/botsalebestwebstudio

# 1. Удалите базу (ОСТОРОЖНО! Это удалит все данные)
mysql -u root -p -e "DROP DATABASE IF EXISTS telegram_bot;"

# 2. Создайте базу заново
mysql -u root -p -e "CREATE DATABASE telegram_bot CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 3. Импортируйте схему
mysql -u root -p telegram_bot < schema.sql

# 4. Проверьте таблицы
mysql -u root -p telegram_bot -e "SHOW TABLES;"

# 5. Выполните дополнительную миграцию
php migrations/add_preferred_language.php
```

## Проверка после исправления

```bash
# Проверка таблиц
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
    echo '  - ' . array_values(\$table)[0] . PHP_EOL;
}
"
```

---

**✅ После выполнения этих шагов база данных должна быть настроена корректно!**
