# 🚀 БЫСТРОЕ ИСПРАВЛЕНИЕ ОБРАБОТКИ PHP НА VPS

## ❌ Проблема
PHP код выводится как текст вместо выполнения.

## ✅ РЕШЕНИЕ - ВЫПОЛНИТЕ ВСЕ КОМАНДЫ:

```bash
# 1. Переход в директорию проекта
cd /ssd/www/bots/botsalebestwebstudio

# 2. Получение изменений из GitHub
git pull origin main

# 3. Проверка версии PHP
php -v

# 4. Определение версии PHP для модуля
PHP_VERSION=$(php -v | head -1 | cut -d' ' -f2 | cut -d'.' -f1,2)
echo "PHP version: $PHP_VERSION"

# 5. Включение модуля PHP в Apache
# Для PHP 8.1:
sudo a2enmod php8.1

# ИЛИ для PHP 8.2:
# sudo a2enmod php8.2

# ИЛИ для PHP 8.3:
# sudo a2enmod php8.3

# 6. Проверка, что модуль загружен
apache2ctl -M | grep php

# 7. Редактирование конфигурации Apache
sudo nano /etc/apache2/sites-available/botsalebestwebstudio.conf
```

**В конфигурации убедитесь, что есть обработчик PHP:**

```apache
    # PHP обработка - ВАЖНО!
    <FilesMatch \.php$>
        SetHandler application/x-httpd-php
    </FilesMatch>
```

**Сохраните:** `Ctrl+O`, `Enter`, `Ctrl+X`

```bash
# 8. Проверка конфигурации
sudo apache2ctl configtest

# 9. Перезапуск Apache
sudo systemctl restart apache2

# 10. Проверка статуса
sudo systemctl status apache2

# 11. Тест PHP (создайте тестовый файл)
echo "<?php phpinfo(); ?>" > /ssd/www/bots/botsalebestwebstudio/test.php

# 12. Откройте в браузере: http://botsale.1tlt.ru/test.php
# Если видите страницу с информацией о PHP - всё работает!

# 13. Удалите тестовый файл
rm /ssd/www/bots/botsalebestwebstudio/test.php
```

## 🔍 Если модуль PHP не найден

Если команда `apache2ctl -M | grep php` ничего не выводит:

```bash
# Установка PHP и модуля для Apache
sudo apt update
sudo apt install -y php php-cli libapache2-mod-php php-mysql php-redis php-curl php-mbstring php-xml

# Определение версии
PHP_VERSION=$(php -v | head -1 | cut -d' ' -f2 | cut -d'.' -f1,2)
echo "PHP version: $PHP_VERSION"

# Включение модуля
sudo a2enmod php${PHP_VERSION//./}

# Перезапуск Apache
sudo systemctl restart apache2
```

## ✅ Проверка после исправления

```bash
# Проверка логов
sudo tail -n 50 /var/log/apache2/botsalebestwebstudio_error.log

# Проверка работы index.php (должен быть редирект, а не код)
curl -I http://botsale.1tlt.ru/index.php

# Проверка админ-панели
curl -I http://botsale.1tlt.ru/admin/
```

---

**✅ После выполнения всех команд PHP должен обрабатываться корректно!**
