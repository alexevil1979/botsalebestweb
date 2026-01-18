# 🚀 БЫСТРОЕ ИСПРАВЛЕНИЕ PHP-FPM (TCP сокет) НА VPS

## ✅ Текущая ситуация

PHP-FPM использует **TCP сокет** `127.0.0.1:9000`, а не Unix socket.

## 🔧 ВЫПОЛНИТЕ ВСЕ КОМАНДЫ:

```bash
# 1. Включение модулей Apache для PHP-FPM
sudo a2enmod proxy
sudo a2enmod proxy_fcgi
sudo a2enmod rewrite
sudo a2enmod headers

# 2. Редактирование HTTP конфигурации
sudo nano /etc/apache2/sites-available/botsalebestwebstudio.conf
```

**В конфигурации замените обработчик PHP на:**

```apache
    # PHP-FPM обработка через TCP сокет - ВАЖНО!
    <FilesMatch \.php$>
        SetHandler "proxy:fcgi://127.0.0.1:9000"
    </FilesMatch>
```

**Сохраните:** `Ctrl+O`, `Enter`, `Ctrl+X`

```bash
# 3. Редактирование HTTPS конфигурации (если используется)
ls -la /etc/apache2/sites-enabled/ | grep botsale
sudo nano /etc/apache2/sites-available/botsalebestwebstudio-le-ssl.conf
# Добавьте тот же обработчик PHP-FPM в секцию <VirtualHost *:443>
```

**В HTTPS конфигурации добавьте:**

```apache
    # PHP-FPM обработка через TCP сокет - ВАЖНО!
    <FilesMatch \.php$>
        SetHandler "proxy:fcgi://127.0.0.1:9000"
    </FilesMatch>
```

**Сохраните:** `Ctrl+O`, `Enter`, `Ctrl+X`

```bash
# 4. Проверка конфигурации
sudo apache2ctl configtest

# 5. Перезапуск сервисов
sudo service php8.1-fpm restart
sudo systemctl restart apache2

# 6. Проверка статуса
sudo systemctl status apache2
sudo service php8.1-fpm status

# 7. Тест PHP
echo "<?php phpinfo(); ?>" > /ssd/www/bots/botsalebestwebstudio/test.php
curl http://botsale.1tlt.ru/test.php
rm /ssd/www/bots/botsalebestwebstudio/test.php
```

## 📋 Полная конфигурация для HTTP

```apache
<VirtualHost *:80>
    ServerName botsale.1tlt.ru
    ServerAlias www.botsale.1tlt.ru
    DocumentRoot /ssd/www/bots/botsalebestwebstudio

    ErrorLog ${APACHE_LOG_DIR}/botsalebestwebstudio_error.log
    CustomLog ${APACHE_LOG_DIR}/botsalebestwebstudio_access.log combined

    <Directory /ssd/www/bots/botsalebestwebstudio>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    # PHP-FPM обработка через TCP сокет
    <FilesMatch \.php$>
        SetHandler "proxy:fcgi://127.0.0.1:9000"
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
</VirtualHost>
```

## 📋 Полная конфигурация для HTTPS

```apache
<VirtualHost *:443>
    ServerName botsale.1tlt.ru
    ServerAlias www.botsale.1tlt.ru
    DocumentRoot /ssd/www/bots/botsalebestwebstudio

    SSLEngine on
    SSLCertificateFile /etc/letsencrypt/live/botsale.1tlt.ru/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/botsale.1tlt.ru/privkey.pem

    ErrorLog ${APACHE_LOG_DIR}/botsalebestwebstudio_error.log
    CustomLog ${APACHE_LOG_DIR}/botsalebestwebstudio_access.log combined

    <Directory /ssd/www/bots/botsalebestwebstudio>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    # PHP-FPM обработка через TCP сокет
    <FilesMatch \.php$>
        SetHandler "proxy:fcgi://127.0.0.1:9000"
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
</VirtualHost>
```

## ✅ Проверка после настройки

```bash
# Проверка логов
sudo tail -n 50 /var/log/apache2/botsalebestwebstudio_error.log

# Проверка работы index.php
curl -I http://botsale.1tlt.ru/index.php

# Проверка работы админ-панели
curl -I http://botsale.1tlt.ru/admin/

# Проверка HTTPS версии
curl -I https://botsale.1tlt.ru/index.php
curl -I https://botsale.1tlt.ru/admin/
```

## 🔍 Проверка подключения к PHP-FPM

```bash
# Проверка, что PHP-FPM слушает на порту 9000
netstat -tlnp | grep 9000
# или
ss -tlnp | grep 9000

# Проверка статуса PHP-FPM
sudo service php8.1-fpm status
```

---

**✅ После выполнения всех команд PHP-FPM должен работать через TCP сокет!**
