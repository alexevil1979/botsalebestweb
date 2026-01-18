# 🚀 БЫСТРОЕ ИСПРАВЛЕНИЕ PHP-FPM НА VPS

## ✅ Текущая ситуация

Используется **PHP-FPM**, нужно настроить Apache для работы с ним.

## 🔧 ВЫПОЛНИТЕ ВСЕ КОМАНДЫ:

```bash
# 1. Поиск сокета PHP-FPM
sudo find /var/run -name "php*.sock" 2>/dev/null
cat /usr/local/php8.1/etc/php-fpm.conf | grep listen

# 2. Включение модулей Apache для PHP-FPM
sudo a2enmod proxy
sudo a2enmod proxy_fcgi
sudo a2enmod rewrite
sudo a2enmod headers

# 3. Редактирование HTTP конфигурации
sudo nano /etc/apache2/sites-available/botsalebestwebstudio.conf
```

**В конфигурации замените обработчик PHP на:**

```apache
    # PHP-FPM обработка - ВАЖНО!
    <FilesMatch \.php$>
        SetHandler "proxy:unix:/var/run/php/php8.1-fpm.sock|fcgi://localhost"
    </FilesMatch>
```

**Или если сокет в другом месте (из команды 1):**
```apache
    <FilesMatch \.php$>
        SetHandler "proxy:unix:/usr/local/php8.1/var/run/php-fpm.sock|fcgi://localhost"
    </FilesMatch>
```

**Сохраните:** `Ctrl+O`, `Enter`, `Ctrl+X`

```bash
# 4. Редактирование HTTPS конфигурации (если используется)
ls -la /etc/apache2/sites-enabled/ | grep botsale
sudo nano /etc/apache2/sites-available/botsalebestwebstudio-le-ssl.conf
# Добавьте тот же обработчик PHP-FPM

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

## 🔍 Если не знаете путь к сокету

```bash
# Проверка конфигурации PHP-FPM
cat /usr/local/php8.1/etc/php-fpm.conf | grep -E "listen|socket"
cat /usr/local/php8.1/etc/php-fpm.d/www.conf | grep -E "listen|socket"
```

**Используйте найденный путь в конфигурации Apache!**

---

**✅ После выполнения всех команд PHP-FPM должен работать!**
