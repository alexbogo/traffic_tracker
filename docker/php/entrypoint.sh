#!/bin/sh
set -e

echo "🚀 Starting Traffic Tracker PHP Container..."

# Wait a bit for MySQL to be fully ready
echo "⏳ Waiting for MySQL to be ready..."
sleep 5

# Install Composer dependencies if vendor folder doesn't exist
if [ ! -d "vendor" ]; then
    echo "📦 Installing Composer dependencies..."
    composer install --no-interaction --optimize-autoloader
else
    echo "✅ Composer dependencies already installed"
fi

# Create cache and log directories if they don't exist
mkdir -p var/cache var/log
chmod -R 775 var/cache var/log

# Run database migrations
echo "🗄️  Running database migrations..."
php bin/console doctrine:migrations:migrate --no-interaction || echo "⚠️  No migrations to run or migration failed"

# Generate JWT keys if they don't exist
if [ ! -f "config/jwt/private.pem" ]; then
    echo "🔐 Generating JWT keys..."
    php bin/console lexik:jwt:generate-keypair --skip-if-exists
    chmod 644 config/jwt/private.pem config/jwt/public.pem
else
    echo "✅ JWT keys already exist"
fi

echo "✅ PHP container ready!"

# Start PHP-FPM
exec php-fpm
