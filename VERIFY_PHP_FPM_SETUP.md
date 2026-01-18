# ✅ Проверка настройки PHP-FPM

## 🔍 Текущая ситуация

- HTTP редиректит на HTTPS (нормально)
- PHP-FPM слушает на `127.0.0.1:9000` (подтверждено через telnet)
- Нужно проверить HTTPS версию и конфигурацию Apache

## 🚀 ВЫПОЛНИТЕ ВСЕ КОМАНДЫ:

```bash
# 1. Проверка HTTPS версии test.php
curl https://botsale.1tlt.ru/test.php

# 2. Проверка включенных модулей Apache
apache2ctl -M | grep -E "proxy|fcgi"

# 3. Проверка конфигурации HTTP
sudo cat /etc/apache2/sites-available/botsalebestwebstudio.conf | grep -A 3 "FilesMatch"

# 4. Проверка конфигурации HTTPS
sudo cat /etc/apache2/sites-available/botsalebestwebstudio-le-ssl.conf | grep -A 3 "FilesMatch"

# 5. Если модули не включены, включите их
sudo a2enmod proxy
sudo a2enmod proxy_fcgi
sudo systemctl restart apache2

# 6. Проверка статуса PHP-FPM
sudo service php8.1-fpm status

# 7. Проверка подключения к PHP-FPM
netstat -tlnp | grep 9000
```

## 📋 Правильная конфигурация для HTTP

Убедитесь, что в `/etc/apache2/sites-available/botsalebestwebstudio.conf` есть:

```apache
    # PHP-FPM обработка через TCP сокет
    <FilesMatch \.php$>
        SetHandler "proxy:fcgi://127.0.0.1:9000"
    </FilesMatch>
```

## 📋 Правильная конфигурация для HTTPS

Убедитесь, что в `/etc/apache2/sites-available/botsalebestwebstudio-le-ssl.conf` есть:

```apache
    # PHP-FPM обработка через TCP сокет
    <FilesMatch \.php$>
        SetHandler "proxy:fcgi://127.0.0.1:9000"
    </FilesMatch>
```

## 🔧 Если конфигурация неправильная

```bash
# Редактирование HTTP конфигурации
sudo nano /etc/apache2/sites-available/botsalebestwebstudio.conf

# Редактирование HTTPS конфигурации
sudo nano /etc/apache2/sites-available/botsalebestwebstudio-le-ssl.conf
```

**Добавьте или замените обработчик PHP на:**

```apache
    # PHP-FPM обработка через TCP сокет
    <FilesMatch \.php$>
        SetHandler "proxy:fcgi://127.0.0.1:9000"
    </FilesMatch>
```

**Сохраните:** `Ctrl+O`, `Enter`, `Ctrl+X`

```bash
# Проверка конфигурации
sudo apache2ctl configtest

# Перезапуск Apache
sudo systemctl restart apache2

# Проверка HTTPS версии
curl https://botsale.1tlt.ru/test.php
```

## ✅ Ожидаемый результат

После правильной настройки:

```bash
curl https://botsale.1tlt.ru/test.php
```

**Должна выводиться HTML страница с информацией о PHP**, а не редирект или код PHP.

## 🧹 Очистка после проверки

```bash
# Удаление тестового файла
rm /ssd/www/bots/botsalebestwebstudio/test.php
```

---

**✅ После выполнения всех команд PHP должен обрабатываться через PHP-FPM!**
