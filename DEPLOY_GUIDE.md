# 🚀 Полный гайд по деплою на VPS

## 📍 Путь проекта
```
/ssd/www/bots/botsalebestwebstudio
```

---

## 📋 Шаг 1: Подготовка сервера

### 1.1. Подключение к серверу
```bash
ssh root@your-server-ip
# или
ssh your-username@your-server-ip
```

### 1.2. Обновление системы
```bash
sudo apt update && sudo apt upgrade -y
```

### 1.3. Установка необходимых компонентов

#### PHP 8.1 и расширения
```bash
sudo apt install -y php8.1 php8.1-fpm php8.1-mysql php8.1-redis php8.1-curl php8.1-mbstring php8.1-xml php8.1-zip
```

#### MySQL
```bash
sudo apt install -y mysql-server
sudo mysql_secure_installation
```

#### Redis
```bash
sudo apt install -y redis-server
sudo systemctl enable redis-server
sudo systemctl start redis-server
```

#### Composer
```bash
cd /tmp
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer
```

#### Git
```bash
sudo apt install -y git
```

#### Nginx
```bash
sudo apt install -y nginx
```

---

## 📋 Шаг 2: Настройка базы данных

### 2.1. Создание базы данных
```bash
sudo mysql -u root -p
```

Выполните в MySQL:
```sql
CREATE DATABASE telegram_bot CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'telegram_bot'@'localhost' IDENTIFIED BY 'your_secure_password_here';
GRANT ALL PRIVILEGES ON telegram_bot.* TO 'telegram_bot'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

**⚠️ ВАЖНО:** Замените `your_secure_password_here` на надежный пароль!

---

## 📋 Шаг 3: Клонирование проекта

### 3.1. Создание директории
```bash
sudo mkdir -p /ssd/www/bots
cd /ssd/www/bots
```

### 3.2. Клонирование репозитория
```bash
git clone https://github.com/your-username/your-repo.git botsalebestwebstudio
cd botsalebestwebstudio
```

**Или если репозиторий приватный:**
```bash
git clone git@github.com:your-username/your-repo.git botsalebestwebstudio
cd botsalebestwebstudio
```

---

## 📋 Шаг 4: Настройка окружения

### 4.1. Создание файла .env
```bash
cp env.example.txt .env
nano .env
```

### 4.2. Заполнение .env файла

Отредактируйте `.env` и заполните все параметры:

```env
# Telegram Bot
TELEGRAM_BOT_TOKEN=your_bot_token_from_botfather
TELEGRAM_WEBHOOK_SECRET=your_random_secure_string_here
TELEGRAM_WEBHOOK_URL=https://yourdomain.com/bot/webhook.php

# Database
DB_HOST=localhost
DB_NAME=telegram_bot
DB_USER=telegram_bot
DB_PASS=your_secure_password_here
DB_CHARSET=utf8mb4

# Redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_PASSWORD=
REDIS_DB=0

# Admin Panel
ADMIN_USERNAME=admin
ADMIN_PASSWORD=your_secure_admin_password
ADMIN_SESSION_LIFETIME=3600

# LLM (Optional - можно оставить выключенным)
LLM_ENABLED=false
LLM_PROVIDER=yandex
YANDEX_API_KEY=
YANDEX_FOLDER_ID=
GIGACHAT_CLIENT_ID=
GIGACHAT_CLIENT_SECRET=
GIGACHAT_SCOPE=https://gigachat.dev/v1

# App
APP_ENV=production
APP_DEBUG=false
TIMEZONE=Europe/Moscow
```

**Сохраните:** `Ctrl+O`, `Enter`, `Ctrl+X`

---

## 📋 Шаг 5: Установка зависимостей

```bash
cd /ssd/www/bots/botsalebestwebstudio
composer install --no-dev --optimize-autoloader
```

---

## 📋 Шаг 6: Настройка базы данных

### 6.1. Запуск миграций
```bash
php migrations/migrate.php
php migrations/add_preferred_language.php
```

**Или импорт схемы напрямую:**
```bash
mysql -u telegram_bot -p telegram_bot < schema.sql
```

---

## 📋 Шаг 7: Настройка прав доступа

```bash
sudo chown -R www-data:www-data /ssd/www/bots/botsalebestwebstudio
sudo chmod -R 755 /ssd/www/bots/botsalebestwebstudio
sudo chmod -R 777 /ssd/www/bots/botsalebestwebstudio/logs
```

---

## 📋 Шаг 8: Настройка Nginx

### 8.1. Создание конфигурации
```bash
sudo nano /etc/nginx/sites-available/botsalebestwebstudio
```

### 8.2. Содержимое конфигурации

```nginx
server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;
    root /ssd/www/bots/botsalebestwebstudio;
    index index.php;

    # Логи
    access_log /var/log/nginx/botsalebestwebstudio_access.log;
    error_log /var/log/nginx/botsalebestwebstudio_error.log;

    # Основные настройки
    client_max_body_size 10M;

    # Корневая директория
    location / {
        try_files $uri $uri/ =404;
    }

    # PHP обработка
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Webhook для Telegram
    location /bot/webhook.php {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index webhook.php;
        fastcgi_param SCRIPT_FILENAME $document_root/bot/webhook.php;
        include fastcgi_params;
        fastcgi_param HTTP_X_TELEGRAM_BOT_API_SECRET_TOKEN $http_x_telegram_bot_api_secret_token;
        
        # Отключение логирования для webhook
        access_log off;
    }

    # Админ-панель
    location /admin {
        try_files $uri $uri/ /admin/index.php;
    }

    # Защита .env файла
    location ~ /\.env {
        deny all;
        return 404;
    }

    # Защита других скрытых файлов
    location ~ /\. {
        deny all;
        return 404;
    }
}
```

**Сохраните:** `Ctrl+O`, `Enter`, `Ctrl+X`

### 8.3. Активация конфигурации
```bash
sudo ln -s /etc/nginx/sites-available/botsalebestwebstudio /etc/nginx/sites-enabled/
sudo nginx -t
```

Если тест прошел успешно:
```bash
sudo systemctl reload nginx
```

---

## 📋 Шаг 9: Настройка SSL (Let's Encrypt)

### 9.1. Установка Certbot
```bash
sudo apt install -y certbot python3-certbot-nginx
```

### 9.2. Получение сертификата
```bash
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com
```

Следуйте инструкциям на экране. Certbot автоматически обновит конфигурацию Nginx.

### 9.3. Автоматическое обновление
```bash
sudo certbot renew --dry-run
```

---

## 📋 Шаг 10: Настройка Telegram Webhook

```bash
cd /ssd/www/bots/botsalebestwebstudio
php bot/setup-webhook.php
```

**Или вручную через curl:**
```bash
curl -X POST "https://api.telegram.org/bot<YOUR_BOT_TOKEN>/setWebhook" \
  -d "url=https://yourdomain.com/bot/webhook.php" \
  -d "secret_token=your_webhook_secret_from_env"
```

---

## 📋 Шаг 11: Проверка работы

### 11.1. Проверка сервисов
```bash
# Проверка PHP-FPM
sudo systemctl status php8.1-fpm

# Проверка Nginx
sudo systemctl status nginx

# Проверка MySQL
sudo systemctl status mysql

# Проверка Redis
sudo systemctl status redis-server
redis-cli ping
```

### 11.2. Проверка webhook
```bash
php bot/setup-webhook.php
```

### 11.3. Проверка админки
Откройте в браузере: `https://yourdomain.com/admin`

Логин и пароль из `.env`:
- `ADMIN_USERNAME`
- `ADMIN_PASSWORD`

---

## 📋 Шаг 12: Настройка автоматического деплоя (опционально)

### Вариант A: GitHub Actions

1. В настройках репозитория GitHub → Settings → Secrets and variables → Actions
2. Добавьте следующие Secrets:
   - `VPS_HOST` - IP адрес или домен вашего VPS
   - `VPS_USER` - пользователь SSH (обычно `root`)
   - `VPS_SSH_KEY` - приватный SSH ключ
   - `VPS_PATH` - `/ssd/www/bots/botsalebestwebstudio`

3. При каждом push в `main` или `master` будет автоматический деплой

### Вариант B: Git Webhook

1. Добавьте в `.env`:
   ```env
   WEBHOOK_SECRET=your_secure_random_string
   ```

2. Настройте webhook в Git репозитории:
   - URL: `https://yourdomain.com/deploy/webhook.php`
   - Secret: значение из `WEBHOOK_SECRET`
   - Events: Push

### Вариант C: Ручной деплой

```bash
cd /ssd/www/bots/botsalebestwebstudio
git pull
composer install --no-dev --optimize-autoloader
php migrations/migrate.php
php migrations/add_preferred_language.php
sudo systemctl reload php8.1-fpm
```

**Или используйте скрипт:**
```bash
cd /ssd/www/bots/botsalebestwebstudio
bash deploy/deploy.sh
```

---

## 🔄 Обновление проекта

### Быстрое обновление
```bash
cd /ssd/www/bots/botsalebestwebstudio
git pull
composer install --no-dev --optimize-autoloader
php migrations/migrate.php
php migrations/add_preferred_language.php
sudo systemctl reload php8.1-fpm
```

### Полное обновление (со скриптом)
```bash
cd /ssd/www/bots/botsalebestwebstudio
bash deploy/deploy.sh
```

---

## 🐛 Решение проблем

### Проблема: 502 Bad Gateway
```bash
# Проверьте PHP-FPM
sudo systemctl status php8.1-fpm
sudo systemctl restart php8.1-fpm

# Проверьте логи
sudo tail -f /var/log/nginx/error.log
sudo tail -f /var/log/php8.1-fpm.log
```

### Проблема: Webhook не работает
```bash
# Проверьте webhook
php bot/setup-webhook.php

# Проверьте права доступа
ls -la /ssd/www/bots/botsalebestwebstudio/bot/webhook.php

# Проверьте логи
sudo tail -f /var/log/nginx/botsalebestwebstudio_error.log
```

### Проблема: Ошибки подключения к БД
```bash
# Проверьте подключение
mysql -u telegram_bot -p telegram_bot

# Проверьте .env файл
cat /ssd/www/bots/botsalebestwebstudio/.env | grep DB_
```

### Проблема: Redis не работает
```bash
# Проверьте Redis
redis-cli ping

# Перезапустите Redis
sudo systemctl restart redis-server
```

### Проблема: Права доступа
```bash
# Исправьте права
sudo chown -R www-data:www-data /ssd/www/bots/botsalebestwebstudio
sudo chmod -R 755 /ssd/www/bots/botsalebestwebstudio
sudo chmod -R 777 /ssd/www/bots/botsalebestwebstudio/logs
```

---

## 📊 Мониторинг

### Просмотр логов
```bash
# Логи Nginx
sudo tail -f /var/log/nginx/botsalebestwebstudio_error.log
sudo tail -f /var/log/nginx/botsalebestwebstudio_access.log

# Логи PHP-FPM
sudo tail -f /var/log/php8.1-fpm.log

# Логи приложения (если настроены)
tail -f /ssd/www/bots/botsalebestwebstudio/logs/php_errors.log
```

### Проверка использования ресурсов
```bash
# CPU и память
htop

# Дисковое пространство
df -h

# Использование MySQL
mysqladmin -u root -p status
```

---

## 🔐 Безопасность

### Рекомендации:
1. ✅ Используйте сильные пароли в `.env`
2. ✅ Регулярно обновляйте систему: `sudo apt update && sudo apt upgrade`
3. ✅ Настройте firewall (UFW):
   ```bash
   sudo ufw allow 22/tcp
   sudo ufw allow 80/tcp
   sudo ufw allow 443/tcp
   sudo ufw enable
   ```
4. ✅ Регулярно делайте бэкапы БД:
   ```bash
   mysqldump -u telegram_bot -p telegram_bot > backup_$(date +%Y%m%d).sql
   ```
5. ✅ Не храните `.env` в Git (уже в `.gitignore`)

---

## ✅ Чеклист деплоя

- [ ] Установлены все зависимости (PHP, MySQL, Redis, Nginx, Composer)
- [ ] Создана база данных и пользователь
- [ ] Проект склонирован в `/ssd/www/bots/botsalebestwebstudio`
- [ ] Создан и заполнен `.env` файл
- [ ] Установлены зависимости через Composer
- [ ] Запущены миграции БД
- [ ] Настроены права доступа
- [ ] Настроен Nginx
- [ ] Настроен SSL (Let's Encrypt)
- [ ] Настроен Telegram webhook
- [ ] Проверена работа админки
- [ ] Настроен автоматический деплой (опционально)

---

## 📞 Поддержка

При возникновении проблем:
1. Проверьте логи (см. раздел "Мониторинг")
2. Убедитесь, что все сервисы запущены
3. Проверьте права доступа к файлам
4. Убедитесь, что `.env` настроен корректно
5. Проверьте webhook через `php bot/setup-webhook.php`

---

**🎉 Готово! Бот развернут и готов к работе!**
