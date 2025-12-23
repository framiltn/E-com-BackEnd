#!/bin/bash

# Quick Production Deployment Script
# Usage: ./deploy-production.sh

set -e

echo "🚀 Starting production deployment..."

# 1. Validate environment
echo "📋 Validating configuration..."
php artisan production:validate || exit 1

# 2. Maintenance mode
echo "🔧 Enabling maintenance mode..."
php artisan down --retry=60

# 3. Pull latest code
echo "📥 Pulling latest code..."
git pull origin main

# 4. Install dependencies
echo "📦 Installing dependencies..."
composer install --optimize-autoloader --no-dev

# 5. Run migrations
echo "🗄️ Running migrations..."
php artisan migrate --force

# 6. Optimize
echo "⚡ Optimizing application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# 7. Restart services
echo "🔄 Restarting services..."
sudo systemctl restart php8.2-fpm 2>/dev/null || echo "Skipping PHP-FPM restart"
sudo systemctl restart marketplace-worker 2>/dev/null || echo "Skipping worker restart"

# 8. Disable maintenance mode
echo "✅ Disabling maintenance mode..."
php artisan up

# 9. Health check
echo "🏥 Running health check..."
sleep 2
curl -f http://localhost/api/health || echo "Warning: Health check failed"

echo "✨ Deployment completed successfully!"
