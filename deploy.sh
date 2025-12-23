#!/bin/bash

# Production Deployment Script
# Run this on your server after uploading code

echo "🚀 Starting deployment..."

# 1. Install dependencies
echo "📦 Installing dependencies..."
composer install --optimize-autoloader --no-dev

# 2. Run migrations
echo "🗄️ Running database migrations..."
php artisan migrate --force

# 3. Clear all caches
echo "🧹 Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# 4. Rebuild caches
echo "⚡ Building caches..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 5. Set permissions
echo "🔐 Setting permissions..."
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# 6. Link storage
echo "🔗 Linking storage..."
php artisan storage:link

# 7. Restart services
echo "🔄 Restarting services..."
sudo systemctl restart php8.2-fpm
sudo systemctl restart nginx

echo "✅ Deployment complete!"
echo "🌐 Visit your site to verify"
