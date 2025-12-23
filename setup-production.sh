#!/bin/bash

# Quick Production Setup Script
# Run this after cloning the repository on production server

set -e

echo "=========================================="
echo "Marketplace Production Setup"
echo "=========================================="
echo ""

# Check if running as root
if [ "$EUID" -eq 0 ]; then 
   echo "❌ Please do not run as root"
   exit 1
fi

# 1. Environment Setup
echo "📝 Step 1: Setting up environment..."
if [ ! -f .env ]; then
    cp .env.production .env
    echo "✅ Created .env file from template"
    echo "⚠️  IMPORTANT: Edit .env with your actual credentials!"
    read -p "Press enter to continue after editing .env..."
else
    echo "✅ .env file already exists"
fi

# 2. Install Dependencies
echo ""
echo "📦 Step 2: Installing dependencies..."
composer install --optimize-autoloader --no-dev
echo "✅ Dependencies installed"

# 3. Generate Application Key
echo ""
echo "🔑 Step 3: Generating application key..."
php artisan key:generate --force
echo "✅ Application key generated"

# 4. Run Migrations
echo ""
echo "🗄️  Step 4: Running database migrations..."
read -p "Run migrations? (y/n) " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    php artisan migrate --force
    echo "✅ Migrations completed"
fi

# 5. Seed Database
echo ""
echo "🌱 Step 5: Seeding database..."
read -p "Seed database? (y/n) " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    php artisan db:seed --force
    echo "✅ Database seeded"
fi

# 6. Cache Configuration
echo ""
echo "⚡ Step 6: Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
echo "✅ Configuration cached"

# 7. Set Permissions
echo ""
echo "🔒 Step 7: Setting permissions..."
chmod -R 775 storage bootstrap/cache
echo "✅ Permissions set"

# 8. Setup Backup Cron
echo ""
echo "⏰ Step 8: Setting up backup cron job..."
chmod +x backup.sh
echo "✅ Backup script is executable"
echo "⚠️  Add to crontab: 0 2 * * * cd $(pwd) && ./backup.sh"

# 9. Test Installation
echo ""
echo "🧪 Step 9: Testing installation..."
php artisan about
echo ""

# 10. Health Check
echo "🏥 Step 10: Running health check..."
php artisan tinker --execute="echo 'Database: ' . (DB::connection()->getPdo() ? 'Connected' : 'Failed') . PHP_EOL;"

echo ""
echo "=========================================="
echo "✅ Setup Complete!"
echo "=========================================="
echo ""
echo "Next steps:"
echo "1. Configure Nginx (see PRODUCTION_DEPLOYMENT_GUIDE.md)"
echo "2. Setup SSL certificate"
echo "3. Start queue worker: sudo systemctl start marketplace-worker"
echo "4. Add backup cron job"
echo "5. Test: curl http://localhost/api/health"
echo ""
echo "📚 Full guide: PRODUCTION_DEPLOYMENT_GUIDE.md"
echo ""
