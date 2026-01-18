# ⚡ Быстрый старт для VPS

## Путь проекта
```
/ssd/www/bots/botsalebestwebstudio
```

## Команды для первого запуска

```bash
# 1. Перейти в директорию
cd /ssd/www/bots/botsalebestwebstudio

# 2. Настроить окружение
cp env.example.txt .env
nano .env  # Заполните все параметры

# 3. Установить зависимости
composer install --no-dev --optimize-autoloader

# 4. Создать БД (если нужно)
mysql -u root -p
# CREATE DATABASE telegram_bot CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
# CREATE USER 'telegram_bot'@'localhost' IDENTIFIED BY 'password';
# GRANT ALL PRIVILEGES ON telegram_bot.* TO 'telegram_bot'@'localhost';
# FLUSH PRIVILEGES;
# EXIT;

# 5. Запустить миграции
php migrations/migrate.php

# 6. Настроить права
sudo chown -R www-data:www-data /ssd/www/bots/botsalebestwebstudio
sudo chmod -R 755 /ssd/www/bots/botsalebestwebstudio
sudo chmod -R 777 /ssd/www/bots/botsalebestwebstudio/logs

# 7. Настроить webhook
php bot/setup-webhook.php
```

## Обновление

```bash
cd /ssd/www/bots/botsalebestwebstudio
git pull
composer install --no-dev
php migrations/migrate.php
php migrations/add_preferred_language.php
sudo systemctl reload apache2
```

## Или используйте скрипт деплоя

```bash
cd /ssd/www/bots/botsalebestwebstudio
bash deploy/deploy.sh
```

## Настройка GitHub Actions

В Secrets репозитория добавьте:
- `VPS_HOST` - IP или домен
- `VPS_USER` - пользователь SSH
- `VPS_SSH_KEY` - приватный SSH ключ
- `VPS_PATH` - `/ssd/www/bots/botsalebestwebstudio`
