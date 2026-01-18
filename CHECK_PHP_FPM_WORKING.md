# 🔍 Проверка работы PHP-FPM

## ✅ Текущая ситуация

- HTTP редиректит на HTTPS (301) - это нормально
- PHP-FPM слушает на `127.0.0.1:9000` - подтверждено
- Нужно проверить HTTPS версию и конфигурацию Apache

## 🔧 ВЫПОЛНИТЕ ВСЕ КОМАНДЫ:

### Шаг 1: Проверка HTTPS версии

```bash
# Проверка HTTPS версии test.php
curl -k https://botsale.1tlt.ru/test.php

# Или с игнорированием SSL ошибок (если есть проблемы с сертификатом)
curl --insecure https://botsale.1tlt.ru/test.php
```

### Шаг 2: Проверка конфигурации Apache

```bash
# Проверка HTTP конфигурации
sudo cat /etc/apache2/sites-available/botsalebestwebstudio.conf | grep -A 3 "FilesMatch"

# Проверка HTTPS конфигурации
sudo cat /etc/apache2/sites-available/botsalebestwebstudio-le-ssl.conf | grep -A 3 "FilesMatch"
```

### Шаг 3: Редактирование конфигураций (если обработчик PHP отсутствует)

```bash
# Редактирование HTTP конфигурации
sudo nano /etc/apache2/sites-available/botsalebestwebstudio.conf
```

**Убедитесь, что в секции `<VirtualHost *:80>` есть:**

```apache
    # PHP-FPM обработка через TCP сокет
    <FilesMatch \.php$>
        SetHandler "proxy:fcgi://127.0.0.1:9000"
    </FilesMatch>
```

**Сохраните:** `Ctrl+O`, `Enter`, `Ctrl+X`

```bash
# Редактирование HTTPS конфигурации
sudo nano /etc/apache2/sites-available/botsalebestwebstudio-le-ssl.conf
```

**Убедитесь, что в секции `<VirtualHost *:443>` есть:**

```apache
    # PHP-FPM обработка через TCP сокет
    <FilesMatch \.php$>
        SetHandler "proxy:fcgi://127.0.0.1:9000"
    </FilesMatch>
```

**Сохраните:** `Ctrl+O`, `Enter`, `Ctrl+X`

### Шаг 4: Проверка модулей Apache

```bash
# Проверка загруженных модулей
apache2ctl -M | grep -E "proxy|fcgi"

# Если модули не загружены, включите их:
sudo a2enmod proxy
sudo a2enmod proxy_fcgi
```

### Шаг 5: Проверка конфигурации и перезапуск

```bash
# Проверка синтаксиса
sudo apache2ctl configtest

# Перезапуск Apache
sudo systemctl restart apache2

# Проверка статуса
sudo systemctl status apache2
```

### Шаг 6: Проверка работы PHP

```bash
# Проверка через HTTPS
curl -k https://botsale.1tlt.ru/test.php

# Проверка index.php
curl -k -I https://botsale.1tlt.ru/index.php

# Проверка админ-панели
curl -k -I https://botsale.1tlt.ru/admin/
```

## 📋 Полная конфигурация для HTTP (botsalebestwebstudio.conf)

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

## 📋 Полная конфигурация для HTTPS (botsalebestwebstudio-le-ssl.conf)

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

## ✅ Проверка после настройки

```bash
# Проверка логов
sudo tail -n 50 /var/log/apache2/botsalebestwebstudio_error.log

# Проверка работы test.php через HTTPS
curl -k https://botsale.1tlt.ru/test.php

# Проверка работы index.php
curl -k -I https://botsale.1tlt.ru/index.php

# Проверка работы админ-панели
curl -k -I https://botsale.1tlt.ru/admin/

# Удаление тестового файла
rm /ssd/www/bots/botsalebestwebstudio/test.php
```

## 🔍 Диагностика проблем

Если PHP код все еще выводится как текст:

```bash
# Проверка подключения к PHP-FPM
netstat -tlnp | grep 9000

# Проверка статуса PHP-FPM
sudo service php8.1-fpm status

# Проверка логов PHP-FPM
sudo tail -n 50 /var/log/php8.1-fpm.log
# или
sudo journalctl -u php8.1-fpm -n 50

# Проверка прав доступа
ls -la /ssd/www/bots/botsalebestwebstudio/test.php
```

---

**✅ После выполнения всех команд PHP-FPM должен работать через HTTPS!**
