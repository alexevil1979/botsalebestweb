# 🔧 Исправление обработки PHP в Apache

## ❌ Проблема

PHP код не обрабатывается, выводится как текст. Например, вместо выполнения кода:
```php
<?php
header('Location: /admin/');
exit;
```
Выводится сам код.

## ✅ Решение

### Шаг 1: Проверка установки PHP

```bash
# Проверка версии PHP
php -v

# Проверка установленных модулей PHP
php -m
```

Если PHP не установлен, установите:
```bash
sudo apt update
sudo apt install -y php php-cli php-fpm libapache2-mod-php php-mysql php-redis php-curl php-mbstring php-xml
```

### Шаг 2: Проверка модуля PHP в Apache

```bash
# Проверка загруженных модулей Apache
apache2ctl -M | grep php
```

Если модуль не найден, определите версию PHP и подключите модуль:

```bash
# Определите версию PHP
php -v | head -1

# Для PHP 8.1
sudo a2enmod php8.1

# Для PHP 8.2
sudo a2enmod php8.2

# Для PHP 8.3
sudo a2enmod php8.3

# Или для старой версии
sudo a2enmod php7.4
```

### Шаг 3: Проверка конфигурации Apache

Откройте конфигурацию виртуального хоста:

```bash
sudo nano /etc/apache2/sites-available/botsalebestwebstudio.conf
```

Убедитесь, что в конфигурации есть обработчик PHP. Конфигурация должна содержать:

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

    # PHP обработка - ВАЖНО!
    <FilesMatch \.php$>
        SetHandler application/x-httpd-php
    </FilesMatch>

    # Альтернативный вариант (если первый не работает)
    # <FilesMatch \.php$>
    #     SetHandler "proxy:unix:/var/run/php/php8.1-fpm.sock|fcgi://localhost"
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

### Шаг 4: Проверка конфигурации Apache

```bash
# Проверка синтаксиса конфигурации
sudo apache2ctl configtest
```

Должно быть: `Syntax OK`

### Шаг 5: Перезапуск Apache

```bash
# Перезапуск Apache
sudo systemctl restart apache2

# Проверка статуса
sudo systemctl status apache2
```

### Шаг 6: Проверка работы PHP

Создайте тестовый файл:

```bash
echo "<?php phpinfo(); ?>" > /ssd/www/bots/botsalebestwebstudio/test.php
```

Откройте в браузере: `http://botsale.1tlt.ru/test.php`

Если видите страницу с информацией о PHP - всё работает!

**Удалите тестовый файл после проверки:**
```bash
rm /ssd/www/bots/botsalebestwebstudio/test.php
```

## 🔍 Альтернативные решения

### Если используется PHP-FPM вместо mod_php

Если на сервере используется PHP-FPM, измените обработчик в конфигурации:

```apache
<FilesMatch \.php$>
    SetHandler "proxy:unix:/var/run/php/php8.1-fpm.sock|fcgi://localhost"
</FilesMatch>
```

Или для другой версии:
```apache
<FilesMatch \.php$>
    SetHandler "proxy:unix:/var/run/php/php8.2-fpm.sock|fcgi://localhost"
</FilesMatch>
```

Проверьте путь к сокету:
```bash
ls -la /var/run/php/
```

### Если используется другой путь к PHP-FPM

Найдите правильный путь:
```bash
sudo find /var/run -name "php*.sock" 2>/dev/null
```

## 📋 Полная последовательность команд для исправления

```bash
# 1. Проверка PHP
php -v

# 2. Установка PHP (если не установлен)
sudo apt update
sudo apt install -y php php-cli libapache2-mod-php php-mysql php-redis php-curl php-mbstring php-xml

# 3. Определение версии PHP
PHP_VERSION=$(php -v | head -1 | cut -d' ' -f2 | cut -d'.' -f1,2)
echo "PHP version: $PHP_VERSION"

# 4. Включение модуля PHP
sudo a2enmod php${PHP_VERSION//./}

# 5. Проверка загруженных модулей
apache2ctl -M | grep php

# 6. Редактирование конфигурации
sudo nano /etc/apache2/sites-available/botsalebestwebstudio.conf

# 7. Проверка конфигурации
sudo apache2ctl configtest

# 8. Перезапуск Apache
sudo systemctl restart apache2

# 9. Проверка статуса
sudo systemctl status apache2

# 10. Тест PHP
echo "<?php phpinfo(); ?>" > /ssd/www/bots/botsalebestwebstudio/test.php
# Откройте в браузере: http://botsale.1tlt.ru/test.php
# После проверки удалите: rm /ssd/www/bots/botsalebestwebstudio/test.php
```

## ✅ Проверка после исправления

```bash
# Проверка логов на ошибки
sudo tail -n 50 /var/log/apache2/botsalebestwebstudio_error.log

# Проверка работы админ-панели
curl -I http://botsale.1tlt.ru/admin/

# Проверка обработки PHP
curl http://botsale.1tlt.ru/index.php
# Должен быть редирект, а не вывод PHP кода
```

## 📝 Важные данные проекта

- **Домен:** `botsale.1tlt.ru`
- **Путь на VPS:** `/ssd/www/bots/botsalebestwebstudio`
- **Конфигурация Apache:** `/etc/apache2/sites-available/botsalebestwebstudio.conf`

---

**✅ После выполнения всех команд PHP должен обрабатываться корректно!**
