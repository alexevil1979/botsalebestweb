# 🔌 Настройка MySQL через сокет

## 📋 Определение сокета MySQL

Если на вашем сервере MySQL использует сокет вместо TCP/IP подключения, нужно указать путь к сокету.

### Проверка сокета MySQL

Выполните на сервере:

```bash
php -i | grep "mysql.default_socket"
```

Или:

```bash
php -i | grep "pdo_mysql.default_socket"
```

**Пример вывода:**
```
pdo_mysql.default_socket => /tmp/mysql.sock => /tmp/mysql.sock
```

Это означает, что MySQL использует сокет `/tmp/mysql.sock`.

### Альтернативные способы проверки

```bash
# Через MySQL конфигурацию
mysql --help | grep "socket"

# Через my.cnf
cat /etc/mysql/my.cnf | grep socket

# Через mysqld
ps aux | grep mysqld
```

## ⚙️ Настройка в .env

### Вариант 1: Использование сокета (рекомендуется для localhost)

Если MySQL использует сокет, укажите его в `.env`:

```env
# Database
DB_HOST=localhost
DB_NAME=telegram_bot
DB_USER=root
DB_PASS=your_password
DB_CHARSET=utf8mb4
DB_SOCKET=/tmp/mysql.sock  # Укажите путь к сокету
```

**Важно:** Если указан `DB_SOCKET`, он будет использоваться вместо `DB_HOST`.

### Вариант 2: Использование TCP/IP (стандартный)

Если MySQL использует стандартное TCP/IP подключение:

```env
# Database
DB_HOST=localhost
DB_NAME=telegram_bot
DB_USER=root
DB_PASS=your_password
DB_CHARSET=utf8mb4
# DB_SOCKET не указывайте или закомментируйте
```

## 🔧 Для вашего сервера (botsale.1tlt.ru)

На вашем сервере MySQL использует сокет `/tmp/mysql.sock`, поэтому в `.env` должно быть:

```env
# Database
DB_HOST=localhost
DB_NAME=telegram_bot
DB_USER=root
DB_PASS=qweasd333123
DB_CHARSET=utf8mb4
DB_SOCKET=/tmp/mysql.sock
```

## ✅ Проверка подключения

После настройки проверьте подключение:

```bash
cd /ssd/www/bots/botsalebestwebstudio
php -r "
require 'vendor/autoload.php';
Core\Config::load('.env');
try {
    \$result = Core\Database::fetch('SELECT 1 as test');
    echo '✅ Подключение к БД успешно!';
    var_dump(\$result);
} catch (Exception \$e) {
    echo '❌ Ошибка подключения: ' . \$e->getMessage();
}
"
```

## 🐛 Решение проблем

### Проблема: "Can't connect to local MySQL server through socket"

**Решение 1:** Проверьте путь к сокету:
```bash
ls -la /tmp/mysql.sock
```

**Решение 2:** Если сокет в другом месте, найдите его:
```bash
find /var -name mysql.sock 2>/dev/null
find /tmp -name mysql.sock 2>/dev/null
```

**Решение 3:** Используйте TCP/IP вместо сокета:
```env
DB_HOST=127.0.0.1
# Закомментируйте или удалите DB_SOCKET
```

### Проблема: "Access denied for user"

**Решение:** Проверьте права пользователя MySQL:
```bash
mysql -u root -p
```

```sql
GRANT ALL PRIVILEGES ON telegram_bot.* TO 'root'@'localhost';
FLUSH PRIVILEGES;
```

### Проблема: "Unknown database"

**Решение:** Создайте базу данных:
```bash
mysql -u root -p
```

```sql
CREATE DATABASE telegram_bot CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

## 📝 Примечания

1. **Сокет vs TCP/IP:**
   - Сокет (`/tmp/mysql.sock`) - быстрее для localhost подключений
   - TCP/IP (`localhost:3306`) - стандартный способ

2. **Приоритет:**
   - Если указан `DB_SOCKET`, он используется вместо `DB_HOST`
   - Если `DB_SOCKET` не указан, используется `DB_HOST`

3. **Безопасность:**
   - Сокет доступен только на localhost
   - TCP/IP может быть доступен извне (если настроено)

---

**✅ После настройки сокета подключение к БД должно работать корректно!**
