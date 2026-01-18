# 🔧 Настройка Apache для PHP-FPM

## ✅ Текущая ситуация

На сервере используется **PHP-FPM** (FastCGI Process Manager), а не mod_php. Нужно настроить Apache для работы с PHP-FPM через proxy.

## 🚀 РЕШЕНИЕ - ВЫПОЛНИТЕ ВСЕ КОМАНДЫ:

### Шаг 1: Найти путь к сокету PHP-FPM

```bash
# Поиск сокета PHP-FPM
sudo find /var/run -name "php*.sock" 2>/dev/null

# Или проверка стандартных путей
ls -la /var/run/php/
ls -la /usr/local/php8.1/var/run/

# Проверка конфигурации PHP-FPM
cat /usr/local/php8.1/etc/php-fpm.conf | grep listen
# или
cat /usr/local/php8.1/etc/php-fpm.d/www.conf | grep listen
```

**Ожидаемый результат:** путь к сокету, например:
- `/var/run/php/php8.1-fpm.sock`
- `/usr/local/php8.1/var/run/php-fpm.sock`
- `/tmp/php8.1-fpm.sock`

### Шаг 2: Включить необходимые модули Apache

```bash
# Включение модулей для работы с PHP-FPM
sudo a2enmod proxy
sudo a2enmod proxy_fcgi
sudo a2enmod rewrite
sudo a2enmod headers

# Проверка загруженных модулей
apache2ctl -M | grep -E "proxy|fcgi"
```

### Шаг 3: Редактировать конфигурацию Apache

```bash
# Редактирование конфигурации
sudo nano /etc/apache2/sites-available/botsalebestwebstudio.conf
```

**Замените обработчик PHP на PHP-FPM:**

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

    # PHP-FPM обработка - ВАЖНО!
    # Вариант 1: Если используется TCP (127.0.0.1:9000) - РЕКОМЕНДУЕТСЯ
    <FilesMatch \.php$>
        SetHandler "proxy:fcgi://127.0.0.1:9000"
    </FilesMatch>

    # Вариант 2: Если сокет в /var/run/php/
    # <FilesMatch \.php$>
    #     SetHandler "proxy:unix:/var/run/php/php8.1-fpm.sock|fcgi://localhost"
    # </FilesMatch>

    # Вариант 3: Если сокет в /usr/local/php8.1/var/run/
    # <FilesMatch \.php$>
    #     SetHandler "proxy:unix:/usr/local/php8.1/var/run/php-fpm.sock|fcgi://localhost"
    # </FilesMatch>

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

**ВАЖНО:** Используйте правильный путь к сокету из Шага 1!

**Сохраните:** `Ctrl+O`, `Enter`, `Ctrl+X`

### Шаг 4: Обновить SSL конфигурацию (если используется HTTPS)

```bash
# Найти SSL конфигурацию
ls -la /etc/apache2/sites-enabled/ | grep botsale

# Редактирование SSL конфигурации
sudo nano /etc/apache2/sites-available/botsalebestwebstudio-le-ssl.conf
# или
sudo nano /etc/apache2/sites-available/botsalebestwebstudio-ssl.conf
```

**Добавьте тот же обработчик PHP-FPM в SSL конфигурацию:**

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

    # PHP-FPM обработка - ВАЖНО!
    <FilesMatch \.php$>
        SetHandler "proxy:unix:/var/run/php/php8.1-fpm.sock|fcgi://localhost"
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

### Шаг 5: Проверка конфигурации

```bash
# Проверка синтаксиса
sudo apache2ctl configtest
```

Должно быть: `Syntax OK`

### Шаг 6: Перезапуск сервисов

```bash
# Перезапуск PHP-FPM
sudo service php8.1-fpm restart

# Перезапуск Apache
sudo systemctl restart apache2

# Проверка статуса
sudo systemctl status apache2
sudo service php8.1-fpm status
```

### Шаг 7: Проверка работы

```bash
# Тест PHP
echo "<?php phpinfo(); ?>" > /ssd/www/bots/botsalebestwebstudio/test.php

# Проверка через curl
curl http://botsale.1tlt.ru/test.php

# Откройте в браузере: http://botsale.1tlt.ru/test.php
# Если видите страницу с информацией о PHP - всё работает!

# Удалите тестовый файл
rm /ssd/www/bots/botsalebestwebstudio/test.php
```

## 🔍 Определение правильного пути к сокету

Если стандартные пути не работают, проверьте конфигурацию PHP-FPM:

```bash
# Проверка конфигурации PHP-FPM
cat /usr/local/php8.1/etc/php-fpm.conf | grep -E "listen|socket"

# Или проверка пула www
cat /usr/local/php8.1/etc/php-fpm.d/www.conf | grep -E "listen|socket"
```

**Примеры путей:**
- Unix socket: `listen = /var/run/php/php8.1-fpm.sock`
- Unix socket: `listen = /usr/local/php8.1/var/run/php-fpm.sock`
- TCP socket: `listen = 127.0.0.1:9000`

## 📋 Полная последовательность команд

```bash
# 1. Поиск сокета PHP-FPM
sudo find /var/run -name "php*.sock" 2>/dev/null
cat /usr/local/php8.1/etc/php-fpm.conf | grep listen

# 2. Включение модулей Apache
sudo a2enmod proxy
sudo a2enmod proxy_fcgi
sudo a2enmod rewrite
sudo a2enmod headers

# 3. Редактирование конфигурации HTTP
sudo nano /etc/apache2/sites-available/botsalebestwebstudio.conf

# 4. Редактирование конфигурации HTTPS (если есть)
sudo nano /etc/apache2/sites-available/botsalebestwebstudio-le-ssl.conf

# 5. Проверка конфигурации
sudo apache2ctl configtest

# 6. Перезапуск сервисов
sudo service php8.1-fpm restart
sudo systemctl restart apache2

# 7. Проверка статуса
sudo systemctl status apache2
sudo service php8.1-fpm status

# 8. Тест PHP
echo "<?php phpinfo(); ?>" > /ssd/www/bots/botsalebestwebstudio/test.php
curl http://botsale.1tlt.ru/test.php
rm /ssd/www/bots/botsalebestwebstudio/test.php
```

## ✅ Проверка после настройки

```bash
# Проверка логов
sudo tail -n 50 /var/log/apache2/botsalebestwebstudio_error.log

# Проверка работы index.php
curl -I http://botsale.1tlt.ru/index.php

# Проверка работы админ-панели
curl -I http://botsale.1tlt.ru/admin/

# Проверка webhook
curl -I http://botsale.1tlt.ru/bot/webhook.php
```

## 📝 Важные данные проекта

- **Домен:** `botsale.1tlt.ru`
- **Путь на VPS:** `/ssd/www/bots/botsalebestwebstudio`
- **PHP-FPM:** `/usr/local/php8.1/etc/php-fpm.conf`
- **Конфигурация Apache:** `/etc/apache2/sites-available/botsalebestwebstudio.conf`

---

**✅ После выполнения всех команд PHP-FPM должен работать корректно с Apache!**
