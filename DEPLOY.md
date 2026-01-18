# 🚀 Инструкция по деплою на VPS

## Путь к проекту
```
/ssd/www/bots/botsalebestwebstudio
```

## Быстрый старт

### 1. Первоначальная настройка

```bash
# Перейти в директорию проекта
cd /ssd/www/bots/botsalebestwebstudio

# Скопировать конфигурацию
cp env.example.txt .env

# Отредактировать .env
nano .env
```

### 2. Установка зависимостей

```bash
composer install --no-dev --optimize-autoloader
```

### 3. Настройка базы данных

```bash
# Создать БД (если еще не создана)
mysql -u root -p
```

```sql
CREATE DATABASE telegram_bot CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'telegram_bot'@'localhost' IDENTIFIED BY 'your_password';
GRANT ALL PRIVILEGES ON telegram_bot.* TO 'telegram_bot'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

```bash
# Запустить миграции
php migrations/migrate.php
```

### 4. Настройка прав доступа

```bash
sudo chown -R www-data:www-data /ssd/www/bots/botsalebestwebstudio
sudo chmod -R 755 /ssd/www/bots/botsalebestwebstudio
sudo chmod -R 777 /ssd/www/bots/botsalebestwebstudio/logs
```

### 5. Настройка Apache

#### Включение необходимых модулей
```bash
sudo a2enmod rewrite
sudo a2enmod headers
sudo a2enmod ssl
```

#### Создание конфигурации

Создайте файл `/etc/apache2/sites-available/botsalebestwebstudio.conf`:

```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    ServerAlias www.yourdomain.com
    DocumentRoot /ssd/www/bots/botsalebestwebstudio

    # Логи
    ErrorLog ${APACHE_LOG_DIR}/botsalebestwebstudio_error.log
    CustomLog ${APACHE_LOG_DIR}/botsalebestwebstudio_access.log combined

    # Основные настройки
    <Directory /ssd/www/bots/botsalebestwebstudio>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    # PHP обработка
    <FilesMatch \.php$>
        SetHandler application/x-httpd-php
    </FilesMatch>

    # Админ-панель
    <Directory /ssd/www/bots/botsalebestwebstudio/admin>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    # Webhook для Telegram
    <Directory /ssd/www/bots/botsalebestwebstudio/bot>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    # Защита .env файла
    <FilesMatch "^\.env">
        Require all denied
    </FilesMatch>

    # Защита других скрытых файлов
    <FilesMatch "^\.">
        Require all denied
    </FilesMatch>

    # Передача заголовков для webhook secret
    <IfModule mod_headers.c>
        RequestHeader set X-Telegram-Bot-Api-Secret-Token "expr=%{HTTP_X_TELEGRAM_BOT_API_SECRET_TOKEN}"
    </IfModule>
</VirtualHost>
```

Активируйте:

```bash
sudo a2ensite botsalebestwebstudio.conf
sudo apache2ctl configtest
sudo systemctl reload apache2
```

### 6. Настройка SSL (Let's Encrypt)

```bash
sudo apt install -y certbot python3-certbot-apache
sudo certbot --apache -d yourdomain.com -d www.yourdomain.com
```

### 7. Настройка Telegram Webhook

```bash
cd /ssd/www/bots/botsalebestwebstudio
php bot/setup-webhook.php
```

## Автоматический деплой

### GitHub Actions

1. В настройках репозитория GitHub добавьте Secrets:
   - `VPS_HOST` - IP или домен VPS
   - `VPS_USER` - пользователь SSH (обычно `root` или ваш пользователь)
   - `VPS_SSH_KEY` - приватный SSH ключ
   - `VPS_PATH` - `/ssd/www/bots/botsalebestwebstudio`

2. При каждом push в `main` или `master` будет автоматический деплой.

### Git Webhook

1. Добавьте в `.env`:
   ```env
   WEBHOOK_SECRET=your_secure_random_string
   ```

2. Настройте webhook в Git репозитории:
   - URL: `https://yourdomain.com/deploy/webhook.php`
   - Secret: значение из `WEBHOOK_SECRET`
   - Events: Push

### Ручной деплой

```bash
cd /ssd/www/bots/botsalebestwebstudio
bash deploy/deploy.sh
```

## Обновление

```bash
cd /ssd/www/bots/botsalebestwebstudio
git pull
composer install --no-dev
php migrations/migrate.php
php migrations/add_preferred_language.php
sudo systemctl reload apache2
```

## Проверка работы

1. Проверьте webhook:
   ```bash
   php bot/setup-webhook.php
   ```

2. Проверьте логи:
   ```bash
   tail -f /var/log/apache2/error.log
   tail -f /var/log/apache2/botsalebestwebstudio_error.log
   ```

3. Откройте админку:
   ```
   https://yourdomain.com/admin
   ```
