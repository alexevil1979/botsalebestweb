#!/bin/bash

set -e

echo "🚀 Starting deployment..."

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check if .env exists
if [ ! -f .env ]; then
    echo -e "${RED}✗ .env file not found!${NC}"
    exit 1
fi

echo -e "${GREEN}✓ .env file found${NC}"

# Pull latest changes
echo "📥 Pulling latest changes..."
git pull origin main || git pull origin master

echo -e "${GREEN}✓ Code updated${NC}"

# Install/update dependencies
if [ -f composer.json ]; then
    echo "📦 Installing dependencies..."
    composer install --no-dev --optimize-autoloader
    echo -e "${GREEN}✓ Dependencies installed${NC}"
fi

# Run migrations
if [ -f migrations/migrate.php ]; then
    echo "🗄️  Running database migrations..."
    php migrations/migrate.php
    echo -e "${GREEN}✓ Migrations completed${NC}"
fi

# Clear Redis cache (optional)
if command -v redis-cli &> /dev/null; then
    echo "🧹 Clearing Redis cache..."
    redis-cli FLUSHDB || echo -e "${YELLOW}⚠ Redis flush failed (may not be critical)${NC}"
fi

# Reload PHP-FPM (adjust based on your setup)
if command -v systemctl &> /dev/null; then
    echo "🔄 Reloading PHP-FPM..."
    sudo systemctl reload php8.1-fpm || sudo systemctl reload php-fpm || echo -e "${YELLOW}⚠ PHP-FPM reload failed (may need manual restart)${NC}"
fi

# Set proper permissions
echo "🔐 Setting permissions..."
chmod -R 755 .
chmod -R 777 logs/ 2>/dev/null || true

echo -e "${GREEN}✓ Deployment completed successfully!${NC}"
