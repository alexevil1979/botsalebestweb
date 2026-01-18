# 📋 Инструкция по применению изменений на VPS

## 🎯 Быстрое применение (если проект уже развернут)

```bash
# 1. Подключитесь к серверу
ssh root@your-server-ip

# 2. Перейдите в директорию проекта
cd /ssd/www/bots/botsalebestwebstudio

# 3. Получите изменения из GitHub
git pull origin main

# 4. Обновите зависимости
composer install --no-dev --optimize-autoloader

# 5. Запустите миграции
php migrations/migrate.php
php migrations/add_preferred_language.php

# 6. Перезагрузите Apache
sudo systemctl reload apache2
```

**Или используйте скрипт:**
```bash
cd /ssd/www/bots/botsalebestwebstudio
bash deploy/deploy.sh
```

---

## 🆕 Первоначальная установка (если проект еще не развернут)

Следуйте полной инструкции: **[DEPLOY_GUIDE.md](DEPLOY_GUIDE.md)**

Или краткой: **[APACHE_SETUP.md](APACHE_SETUP.md)**

---

## ⚙️ Настройка Apache (если еще не настроен)

### 1. Включите модули
```bash
sudo a2enmod rewrite
sudo a2enmod headers
sudo a2enmod ssl
```

### 2. Создайте конфигурацию
```bash
sudo nano /etc/apache2/sites-available/botsalebestwebstudio.conf
```

Вставьте:
```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    ServerAlias www.yourdomain.com
    DocumentRoot /ssd/www/bots/botsalebestwebstudio

    ErrorLog ${APACHE_LOG_DIR}/botsalebestwebstudio_error.log
    CustomLog ${APACHE_LOG_DIR}/botsalebestwebstudio_access.log combined

    <Directory /ssd/www/bots/botsalebestwebstudio>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    <FilesMatch \.php$>
        SetHandler application/x-httpd-php
    </FilesMatch>

    <Directory /ssd/www/bots/botsalebestwebstudio/admin>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    <Directory /ssd/www/bots/botsalebestwebstudio/bot>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    <FilesMatch "^\.env">
        Require all denied
    </FilesMatch>

    <FilesMatch "^\.">
        Require all denied
    </FilesMatch>
</VirtualHost>
```

### 3. Активируйте
```bash
sudo a2ensite botsalebestwebstudio.conf
sudo apache2ctl configtest
sudo systemctl reload apache2
```

---

## 🔍 Проверка

```bash
# Проверка Apache
sudo systemctl status apache2

# Проверка webhook
php bot/setup-webhook.php

# Проверка логов
sudo tail -f /var/log/apache2/botsalebestwebstudio_error.log
```

---

## 📚 Дополнительная документация

- **[DEPLOY_GUIDE.md](DEPLOY_GUIDE.md)** - Полный гайд по деплою
- **[APACHE_SETUP.md](APACHE_SETUP.md)** - Детальная настройка Apache
- **[QUICK_START.md](QUICK_START.md)** - Быстрый старт
- **[README.md](README.md)** - Общая документация
