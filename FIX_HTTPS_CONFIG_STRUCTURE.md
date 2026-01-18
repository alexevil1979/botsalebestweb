# 🔧 Исправление структуры HTTPS конфигурации

## ❌ Проблема

Обработчик PHP-FPM есть в конфигурации, но находится **ВНЕ** секции `<VirtualHost *:443>`. Он должен быть **ВНУТРИ** секции.

## ✅ РЕШЕНИЕ - ВЫПОЛНИТЕ ВСЕ КОМАНДЫ:

```bash
# 1. Просмотр полной структуры конфигурации
sudo cat /etc/apache2/sites-available/botsalebestwebstudio-le-ssl.conf

# 2. Редактирование конфигурации
sudo nano /etc/apache2/sites-available/botsalebestwebstudio-le-ssl.conf
```

## 📋 Правильная структура конфигурации

Конфигурация должна выглядеть так:

```apache
<IfModule mod_ssl.c>
<VirtualHost *:443>
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

    # PHP-FPM обработка - ВАЖНО! Должно быть ВНУТРИ <VirtualHost *:443>
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

    <FilesMatch "^\.">
        Require all denied
    </FilesMatch>

    Include /etc/letsencrypt/options-ssl-apache.conf
    SSLCertificateFile /etc/letsencrypt/live/botsale.1tlt.ru/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/botsale.1tlt.ru/privkey.pem
</VirtualHost>
</IfModule>
```

**ВАЖНО:** 
- Секция `<FilesMatch \.php$>` должна быть **ВНУТРИ** `<VirtualHost *:443>`
- Она должна быть **ПОСЛЕ** директив `<Directory>`
- Она должна быть **ДО** закрывающего тега `</VirtualHost>`

**Сохраните:** `Ctrl+O`, `Enter`, `Ctrl+X`

```bash
# 3. Проверка конфигурации
sudo apache2ctl configtest

# 4. Перезапуск Apache
sudo systemctl restart apache2

# 5. Проверка HTTPS версии
curl https://botsale.1tlt.ru/test.php

# 6. Проверка логов на ошибки
sudo tail -n 20 /var/log/apache2/botsalebestwebstudio_error.log
```

## 🔍 Проверка структуры

После редактирования проверьте структуру:

```bash
# Проверка, что обработчик внутри VirtualHost
sudo grep -A 10 "<VirtualHost \*:443>" /etc/apache2/sites-available/botsalebestwebstudio-le-ssl.conf | grep -A 5 "FilesMatch"
```

## ✅ Ожидаемый результат

После правильной настройки:

```bash
curl https://botsale.1tlt.ru/test.php
```

**Должна выводиться HTML страница с информацией о PHP**, а не код `<?php phpinfo(); ?>`.

## 🧹 Очистка после проверки

```bash
# Удаление тестового файла
rm /ssd/www/bots/botsalebestwebstudio/test.php
```

---

**✅ После перемещения обработчика внутрь <VirtualHost *:443> PHP должен обрабатываться через HTTPS!**
