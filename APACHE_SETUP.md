# 🔧 Инструкция по применению изменений на VPS с Apache

## 📍 Путь проекта
```
/ssd/www/bots/botsalebestwebstudio
```

---

## 🚀 Быстрое применение изменений

### 1. Подключитесь к серверу
```bash
ssh root@your-server-ip
# или
ssh your-username@your-server-ip
```

### 2. Перейдите в директорию проекта
```bash
cd /ssd/www/bots/botsalebestwebstudio
```

### 3. Получите последние изменения из GitHub
```bash
git pull origin main
```

### 4. Установите/обновите зависимости
```bash
composer install --no-dev --optimize-autoloader
```

### 5. Запустите миграции БД
```bash
php migrations/migrate.php
php migrations/add_preferred_language.php
```

### 6. Настройте Apache (если еще не настроен)

#### Включите необходимые модули
```bash
sudo a2enmod rewrite
sudo a2enmod headers
sudo a2enmod ssl
```

#### Создайте конфигурацию
```bash
sudo nano /etc/apache2/sites-available/botsalebestwebstudio.conf
```

Вставьте следующую конфигурацию:

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
</VirtualHost>
```

**Сохраните:** `Ctrl+O`, `Enter`, `Ctrl+X`

#### Активируйте конфигурацию
```bash
sudo a2ensite botsalebestwebstudio.conf
sudo apache2ctl configtest
```

Если тест прошел успешно:
```bash
sudo systemctl reload apache2
```

### 7. Настройте SSL (если еще не настроен)
```bash
sudo apt install -y certbot python3-certbot-apache
sudo certbot --apache -d yourdomain.com -d www.yourdomain.com
```

### 8. Настройте права доступа
```bash
sudo chown -R www-data:www-data /ssd/www/bots/botsalebestwebstudio
sudo chmod -R 755 /ssd/www/bots/botsalebestwebstudio
sudo chmod -R 777 /ssd/www/bots/botsalebestwebstudio/logs
```

### 9. Настройте Telegram Webhook
```bash
php bot/setup-webhook.php
```

### 10. Перезагрузите Apache
```bash
sudo systemctl reload apache2
```

---

## ✅ Проверка работы

### Проверка сервисов
```bash
# Apache
sudo systemctl status apache2

# MySQL
sudo systemctl status mysql

# Redis
sudo systemctl status redis-server
redis-cli ping
```

### Проверка webhook
```bash
php bot/setup-webhook.php
```

### Проверка админки
Откройте в браузере: `https://yourdomain.com/admin`

### Проверка логов
```bash
# Логи Apache
sudo tail -f /var/log/apache2/botsalebestwebstudio_error.log
sudo tail -f /var/log/apache2/error.log
```

---

## 🔄 Обновление проекта (после изменений в GitHub)

```bash
cd /ssd/www/bots/botsalebestwebstudio
git pull origin main
composer install --no-dev --optimize-autoloader
php migrations/migrate.php
php migrations/add_preferred_language.php
sudo systemctl reload apache2
```

**Или используйте скрипт деплоя:**
```bash
cd /ssd/www/bots/botsalebestwebstudio
bash deploy/deploy.sh
```

---

## 🐛 Решение проблем

### Apache не запускается
```bash
# Проверьте конфигурацию
sudo apache2ctl configtest

# Проверьте логи
sudo tail -f /var/log/apache2/error.log

# Перезапустите Apache
sudo systemctl restart apache2
```

### 403 Forbidden
```bash
# Проверьте права доступа
ls -la /ssd/www/bots/botsalebestwebstudio

# Исправьте права
sudo chown -R www-data:www-data /ssd/www/bots/botsalebestwebstudio
sudo chmod -R 755 /ssd/www/bots/botsalebestwebstudio
```

### PHP не работает
```bash
# Проверьте модуль PHP
apache2ctl -M | grep php

# Если модуль не загружен
sudo a2enmod php8.1
sudo systemctl restart apache2
```

### Webhook не работает
```bash
# Проверьте логи
sudo tail -f /var/log/apache2/botsalebestwebstudio_error.log

# Проверьте права на файл
ls -la /ssd/www/bots/botsalebestwebstudio/bot/webhook.php

# Проверьте webhook
php bot/setup-webhook.php
```

---

## 📝 Важные заметки

1. **Apache использует mod_php** вместо PHP-FPM (как в Nginx)
2. **Конфигурация находится** в `/etc/apache2/sites-available/`
3. **Логи находятся** в `/var/log/apache2/`
4. **Перезагрузка:** `sudo systemctl reload apache2` (без простоя)
5. **Полный перезапуск:** `sudo systemctl restart apache2` (с простоем)

---

**✅ Готово! Проект настроен для работы с Apache.**
