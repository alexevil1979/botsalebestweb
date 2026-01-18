# 🔧 Исправление проблемы с PHP-FPM (код выводится как текст)

## ❌ Проблема

Конфигурация выглядит правильной, но PHP код все еще выводится как текст. Также есть ошибка в логах:
```
AH00124: Request exceeded the limit of 10 internal redirects
```

## ✅ РЕШЕНИЕ - ВЫПОЛНИТЕ ВСЕ КОМАНДЫ:

```bash
# 1. Проверка, что модули действительно загружены
apache2ctl -M | grep -E "proxy|fcgi"

# 2. Проверка статуса PHP-FPM
sudo service php8.1-fpm status

# 3. Проверка подключения к PHP-FPM
sudo ss -tulpn | grep 9000

# 4. Проверка конфигурации Apache на ошибки
sudo apache2ctl configtest

# 5. Просмотр полной конфигурации HTTPS
sudo cat /etc/apache2/sites-available/botsalebestwebstudio-le-ssl.conf
```

## 🔧 Исправление конфигурации

Проблема может быть в порядке директив или в конфликте с другими настройками. Попробуйте следующую конфигурацию:

```bash
# Редактирование конфигурации
sudo nano /etc/apache2/sites-available/botsalebestwebstudio-le-ssl.conf
```

**Замените на:**

```apache
<IfModule mod_ssl.c>
<VirtualHost *:443>
    ServerName botsale.1tlt.ru
    ServerAlias www.botsale.1tlt.ru
    DocumentRoot /ssd/www/bots/botsalebestwebstudio

    ErrorLog ${APACHE_LOG_DIR}/botsalebestwebstudio_error.log
    CustomLog ${APACHE_LOG_DIR}/botsalebestwebstudio_access.log combined

    # SSL настройки
    Include /etc/letsencrypt/options-ssl-apache.conf
    SSLCertificateFile /etc/letsencrypt/live/botsale.1tlt.ru/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/botsale.1tlt.ru/privkey.pem

    # Директории
    <Directory /ssd/www/bots/botsalebestwebstudio>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

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

    # PHP-FPM обработка - ВАЖНО! Должно быть после директив Directory
    <FilesMatch \.php$>
        SetHandler "proxy:fcgi://127.0.0.1:9000"
    </FilesMatch>

    # Защита файлов
    <FilesMatch "^\.env">
        Require all denied
    </FilesMatch>

    <FilesMatch "^\.">
        Require all denied
    </FilesMatch>
</VirtualHost>
</IfModule>
```

**ВАЖНО:** 
- SSL настройки должны быть в начале VirtualHost
- PHP-FPM обработчик должен быть после всех директив `<Directory>`
- Убедитесь, что нет дублирующих директив

**Сохраните:** `Ctrl+O`, `Enter`, `Ctrl+X`

```bash
# 6. Проверка конфигурации
sudo apache2ctl configtest

# 7. Перезапуск сервисов
sudo service php8.1-fpm restart
sudo systemctl restart apache2

# 8. Проверка статуса
sudo systemctl status apache2
sudo service php8.1-fpm status

# 9. Проверка HTTPS версии
curl -v https://botsale.1tlt.ru/test.php

# 10. Проверка логов
sudo tail -n 30 /var/log/apache2/botsalebestwebstudio_error.log
```

## 🔍 Альтернативное решение: использование ProxyPassMatch

Если SetHandler не работает, попробуйте ProxyPassMatch:

```apache
    # Альтернативный вариант обработки PHP
    ProxyPassMatch ^/(.*\.php)$ fcgi://127.0.0.1:9000/ssd/www/bots/botsalebestwebstudio/$1
```

## 🔍 Проверка работы PHP-FPM напрямую

```bash
# Создание тестового скрипта для PHP-FPM
echo '<?php
header("Content-Type: text/plain");
echo "PHP-FPM работает!\n";
echo "PHP Version: " . phpversion() . "\n";
?>' > /ssd/www/bots/botsalebestwebstudio/test-fpm.php

# Проверка через curl
curl https://botsale.1tlt.ru/test-fpm.php

# Удаление тестового файла
rm /ssd/www/bots/botsalebestwebstudio/test-fpm.php
```

## 🔍 Проверка прав доступа

```bash
# Проверка прав на файлы
ls -la /ssd/www/bots/botsalebestwebstudio/test.php

# Исправление прав (если нужно)
sudo chown -R www-data:www-data /ssd/www/bots/botsalebestwebstudio
sudo chmod -R 755 /ssd/www/bots/botsalebestwebstudio
```

## 🔍 Проверка .htaccess

```bash
# Проверка .htaccess на конфликты
cat /ssd/www/bots/botsalebestwebstudio/.htaccess
```

Если в `.htaccess` есть обработчик PHP, который конфликтует, временно переименуйте файл:

```bash
# Временное переименование .htaccess
sudo mv /ssd/www/bots/botsalebestwebstudio/.htaccess /ssd/www/bots/botsalebestwebstudio/.htaccess.bak

# Перезапуск Apache
sudo systemctl restart apache2

# Проверка
curl https://botsale.1tlt.ru/test.php

# Если работает, верните .htaccess и исправьте его
sudo mv /ssd/www/bots/botsalebestwebstudio/.htaccess.bak /ssd/www/bots/botsalebestwebstudio/.htaccess
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

**✅ После выполнения всех команд PHP должен обрабатываться через PHP-FPM!**
