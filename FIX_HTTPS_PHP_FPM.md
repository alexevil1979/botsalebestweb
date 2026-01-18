# 🔧 Исправление обработки PHP через HTTPS

## ❌ Проблема

При запросе `curl https://botsale.1tlt.ru/test.php` выводится код PHP `<?php phpinfo(); ?>` вместо выполнения. Это означает, что Apache не передает PHP файлы в PHP-FPM через HTTPS.

## ✅ РЕШЕНИЕ - ВЫПОЛНИТЕ ВСЕ КОМАНДЫ:

```bash
# 1. Проверка включенных модулей Apache
apache2ctl -M | grep -E "proxy|fcgi"

# 2. Если модули не включены, включите их
sudo a2enmod proxy
sudo a2enmod proxy_fcgi

# 3. Проверка текущей HTTPS конфигурации
sudo cat /etc/apache2/sites-available/botsalebestwebstudio-le-ssl.conf | grep -A 5 "FilesMatch"

# 4. Редактирование HTTPS конфигурации
sudo nano /etc/apache2/sites-available/botsalebestwebstudio-le-ssl.conf
```

**В HTTPS конфигурации найдите секцию `<VirtualHost *:443>` и добавьте обработчик PHP-FPM:**

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

    # PHP-FPM обработка через TCP сокет - ВАЖНО!
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

**ВАЖНО:** Убедитесь, что строка `<FilesMatch \.php$>` находится ВНУТРИ секции `<VirtualHost *:443>`, после `<Directory>` директив.

**Сохраните:** `Ctrl+O`, `Enter`, `Ctrl+X`

```bash
# 5. Проверка конфигурации
sudo apache2ctl configtest

# 6. Перезапуск Apache
sudo systemctl restart apache2

# 7. Проверка HTTPS версии
curl https://botsale.1tlt.ru/test.php

# 8. Проверка логов на ошибки
sudo tail -n 20 /var/log/apache2/botsalebestwebstudio_error.log
```

## ✅ Ожидаемый результат

После правильной настройки:

```bash
curl https://botsale.1tlt.ru/test.php
```

**Должна выводиться HTML страница с информацией о PHP**, а не код `<?php phpinfo(); ?>`.

## 🔍 Если не работает

### Проверка модулей

```bash
# Проверка загруженных модулей
apache2ctl -M | grep proxy
apache2ctl -M | grep fcgi

# Если модули не загружены
sudo a2enmod proxy
sudo a2enmod proxy_fcgi
sudo systemctl restart apache2
```

### Проверка конфигурации

```bash
# Проверка синтаксиса
sudo apache2ctl configtest

# Просмотр полной конфигурации HTTPS
sudo cat /etc/apache2/sites-available/botsalebestwebstudio-le-ssl.conf
```

### Проверка PHP-FPM

```bash
# Проверка статуса PHP-FPM
sudo service php8.1-fpm status

# Проверка подключения к PHP-FPM
sudo ss -tulpn | grep 9000
```

## 🧹 Очистка после проверки

```bash
# Удаление тестового файла
rm /ssd/www/bots/botsalebestwebstudio/test.php
```

---

**✅ После выполнения всех команд PHP должен обрабатываться через HTTPS!**
