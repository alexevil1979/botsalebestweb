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

### 5. Настройка Nginx

Создайте файл `/etc/nginx/sites-available/botsalebestwebstudio`:

```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /ssd/www/bots/botsalebestwebstudio;
    index index.php;

    location / {
        try_files $uri $uri/ =404;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location /bot/webhook.php {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index webhook.php;
        fastcgi_param SCRIPT_FILENAME $document_root/bot/webhook.php;
        include fastcgi_params;
        fastcgi_param HTTP_X_TELEGRAM_BOT_API_SECRET_TOKEN $http_x_telegram_bot_api_secret_token;
    }

    location /admin {
        try_files $uri $uri/ /admin/index.php;
    }
}
```

Активируйте:

```bash
sudo ln -s /etc/nginx/sites-available/botsalebestwebstudio /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### 6. Настройка SSL (Let's Encrypt)

```bash
sudo certbot --nginx -d yourdomain.com
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
sudo systemctl reload php8.1-fpm
```

## Проверка работы

1. Проверьте webhook:
   ```bash
   php bot/setup-webhook.php
   ```

2. Проверьте логи:
   ```bash
   tail -f /var/log/nginx/error.log
   tail -f /var/log/php8.1-fpm.log
   ```

3. Откройте админку:
   ```
   https://yourdomain.com/admin
   ```
