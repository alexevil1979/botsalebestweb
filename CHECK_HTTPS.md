# 🔍 Проверка работы через HTTPS

## ✅ Текущий статус

Редирект с HTTP на HTTPS работает корректно:
- `http://botsale.1tlt.ru/index.php` → `https://botsale.1tlt.ru/index.php` (301)
- `http://botsale.1tlt.ru/admin/` → `https://botsale.1tlt.ru/admin/` (301)

## 🔍 Проверка HTTPS версии

Выполните следующие команды для проверки:

```bash
# 1. Проверка HTTPS версии index.php
curl -I https://botsale.1tlt.ru/index.php

# 2. Проверка HTTPS версии админ-панели
curl -I https://botsale.1tlt.ru/admin/

# 3. Проверка содержимого index.php (должен быть редирект на /admin/)
curl -L https://botsale.1tlt.ru/index.php

# 4. Проверка работы PHP через HTTPS
curl https://botsale.1tlt.ru/index.php -v

# 5. Проверка логов Apache
sudo tail -n 50 /var/log/apache2/botsalebestwebstudio_error.log
```

## 📋 Ожидаемые результаты

### Для index.php:
```bash
curl -I https://botsale.1tlt.ru/index.php
```
**Ожидается:**
```
HTTP/1.1 302 Found (или 301)
Location: /admin/
```

### Для админ-панели:
```bash
curl -I https://botsale.1tlt.ru/admin/
```
**Ожидается:**
```
HTTP/1.1 200 OK
Content-Type: text/html; charset=UTF-8
```
Или редирект на страницу логина, если не авторизован.

## 🔧 Если есть проблемы

### Проблема: 404 Not Found на HTTPS

Проверьте конфигурацию SSL виртуального хоста:

```bash
# Проверка SSL конфигурации
sudo nano /etc/apache2/sites-available/botsalebestwebstudio-le-ssl.conf

# Или если используется другая конфигурация
ls -la /etc/apache2/sites-enabled/ | grep botsale
```

Убедитесь, что в SSL конфигурации есть те же настройки, что и в HTTP:

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

    # PHP обработка - ВАЖНО!
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
</VirtualHost>
```

### Проблема: PHP код выводится как текст на HTTPS

Проверьте, что модуль PHP загружен:

```bash
# Проверка модулей Apache
apache2ctl -M | grep php

# Если модуль не загружен
sudo a2enmod php8.1
sudo systemctl restart apache2
```

### Проблема: 500 Internal Server Error

Проверьте логи:

```bash
# Логи ошибок
sudo tail -n 100 /var/log/apache2/botsalebestwebstudio_error.log

# Общие логи ошибок
sudo tail -n 100 /var/log/apache2/error.log
```

## ✅ Полная проверка работы

```bash
# 1. Проверка HTTP редиректа (уже работает)
curl -I http://botsale.1tlt.ru/index.php

# 2. Проверка HTTPS версии
curl -I https://botsale.1tlt.ru/index.php

# 3. Проверка админ-панели
curl -I https://botsale.1tlt.ru/admin/

# 4. Проверка webhook
curl -I https://botsale.1tlt.ru/bot/webhook.php

# 5. Проверка статуса Apache
sudo systemctl status apache2

# 6. Проверка SSL сертификата
openssl s_client -connect botsale.1tlt.ru:443 -servername botsale.1tlt.ru < /dev/null 2>/dev/null | openssl x509 -noout -dates
```

## 📝 Важные данные проекта

- **Домен:** `botsale.1tlt.ru`
- **Путь на VPS:** `/ssd/www/bots/botsalebestwebstudio`
- **HTTP конфигурация:** `/etc/apache2/sites-available/botsalebestwebstudio.conf`
- **HTTPS конфигурация:** `/etc/apache2/sites-available/botsalebestwebstudio-le-ssl.conf` (или подобное)

---

**✅ После проверки HTTPS версии всё должно работать корректно!**
