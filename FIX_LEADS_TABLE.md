# 🔧 Исправление таблицы leads

## ❌ Проблема

Ошибка при создании таблицы `leads`:
```
✗ [6] Error: CREATE TABLE IF NOT EXISTS `leads` ...
   Message: SQLSTATE[HY000]: General error: 1215 Cannot add foreign key constraint
```

## ✅ Причина

Таблица `leads` создавалась ДО таблицы `services`, но имеет внешний ключ на `services`.

## 🚀 Решение

### Вариант 1: Создать таблицу leads вручную (быстрое решение)

```bash
cd /ssd/www/bots/botsalebestwebstudio

mysql -u root -p telegram_bot <<EOF
CREATE TABLE IF NOT EXISTS \`leads\` (
  \`id\` int(11) NOT NULL AUTO_INCREMENT,
  \`user_id\` int(11) NOT NULL,
  \`dialog_id\` int(11) NOT NULL,
  \`name\` varchar(255) DEFAULT NULL,
  \`phone\` varchar(50) DEFAULT NULL,
  \`email\` varchar(255) DEFAULT NULL,
  \`service_id\` int(11) DEFAULT NULL,
  \`budget_from\` decimal(10,2) DEFAULT NULL,
  \`budget_to\` decimal(10,2) DEFAULT NULL,
  \`task_description\` text,
  \`status\` enum('new','contacted','qualified','converted','lost') DEFAULT 'new',
  \`notes\` text,
  \`created_at\` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  \`updated_at\` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (\`id\`),
  KEY \`user_id\` (\`user_id\`),
  KEY \`dialog_id\` (\`dialog_id\`),
  KEY \`service_id\` (\`service_id\`),
  KEY \`status\` (\`status\`),
  CONSTRAINT \`leads_ibfk_1\` FOREIGN KEY (\`user_id\`) REFERENCES \`users\` (\`id\`) ON DELETE CASCADE,
  CONSTRAINT \`leads_ibfk_2\` FOREIGN KEY (\`dialog_id\`) REFERENCES \`dialogs\` (\`id\`) ON DELETE CASCADE,
  CONSTRAINT \`leads_ibfk_3\` FOREIGN KEY (\`service_id\`) REFERENCES \`services\` (\`id\`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
EOF
```

### Вариант 2: Обновить код и пересоздать (рекомендуется)

```bash
cd /ssd/www/bots/botsalebestwebstudio

# 1. Получите обновления
git pull origin main

# 2. Удалите таблицу leads (если она частично создана)
mysql -u root -p telegram_bot -e "DROP TABLE IF EXISTS leads;"

# 3. Выполните миграцию заново
php migrations/migrate.php

# 4. Проверьте таблицы
mysql -u root -p telegram_bot -e "SHOW TABLES;"
```

### Вариант 3: Полный пересоздание БД

```bash
cd /ssd/www/bots/botsalebestwebstudio

# 1. Получите обновления
git pull origin main

# 2. Удалите базу (ОСТОРОЖНО! Это удалит все данные)
mysql -u root -p -e "DROP DATABASE IF EXISTS telegram_bot;"

# 3. Создайте базу заново
mysql -u root -p -e "CREATE DATABASE telegram_bot CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 4. Импортируйте схему
mysql -u root -p telegram_bot < schema.sql

# 5. Проверьте таблицы
mysql -u root -p telegram_bot -e "SHOW TABLES;"
```

## ✅ Проверка после исправления

```bash
# Проверка всех таблиц
mysql -u root -p telegram_bot -e "SHOW TABLES;"

# Проверка структуры leads
mysql -u root -p telegram_bot -e "DESCRIBE leads;"

# Проверка внешних ключей
mysql -u root -p telegram_bot -e "SHOW CREATE TABLE leads\G"
```

Должны быть созданы все 6 таблиц:
- ✓ users
- ✓ dialogs
- ✓ messages
- ✓ services
- ✓ leads
- ✓ events

---

**✅ После исправления все таблицы должны быть созданы корректно!**
