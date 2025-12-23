# Quick Reference Guide

## 🚀 Common Commands

### Deployment
```bash
# Quick deploy
./deploy-production.sh

# Manual deploy
php artisan down
git pull origin main
composer install --no-dev
php artisan migrate --force
php artisan optimize
php artisan up
```

### Validation
```bash
# Check production config
php artisan production:validate

# Run tests
php artisan test

# Check health
curl https://api.yourdomain.com/api/health/detailed
```

### Optimization
```bash
# Optimize database
php artisan db:optimize

# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Rebuild caches
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

### Monitoring
```bash
# View logs
tail -f storage/logs/laravel.log
tail -f storage/logs/performance.log
tail -f storage/logs/security.log

# Check queue
php artisan queue:monitor
php artisan queue:failed

# Check services
sudo systemctl status marketplace-worker
sudo systemctl status nginx
sudo systemctl status php8.2-fpm
```

### Backup
```bash
# Manual backup
./backup.sh

# List backups
ls -lh /var/backups/marketplace/

# Restore database
gunzip < /var/backups/marketplace/db_TIMESTAMP.sql.gz | psql -U marketplace_user marketplace_production
```

### Troubleshooting
```bash
# Fix permissions
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# Restart services
sudo systemctl restart php8.2-fpm
sudo systemctl restart marketplace-worker
sudo systemctl restart nginx

# Check errors
tail -100 storage/logs/laravel.log
```

## 📊 Key Endpoints

- **API Info**: `GET /api/`
- **Health Check**: `GET /api/health`
- **Detailed Health**: `GET /api/health/detailed`
- **Register**: `POST /api/register`
- **Login**: `POST /api/login`

## 🔑 Environment Variables

Critical production settings:
```env
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:...
DB_PASSWORD=strong_password
RAZORPAY_KEY_ID=rzp_live_...
SESSION_DRIVER=redis
CACHE_STORE=redis
QUEUE_CONNECTION=redis
```

## 📞 Emergency Contacts

- Hosting: _______________
- Domain: _______________
- Database: _______________
- Payment Gateway: _______________

## 🆘 Quick Fixes

**500 Error**: Clear cache, check logs
**Database Error**: Check connection, restart PostgreSQL
**Queue Not Working**: Restart worker service
**High Memory**: Restart PHP-FPM, check for memory leaks
