# Production Deployment Checklist

## ✅ COMPLETED

- [x] Debug mode disabled (APP_DEBUG=false)
- [x] Environment set to production (APP_ENV=production)
- [x] Rate limiting added (60 req/min)
- [x] Logging configured (daily rotation)
- [x] Database indexes created
- [x] Code optimized (eager loading, select)
- [x] CORS configured
- [x] File storage configured

## ❌ TODO BEFORE DEPLOYMENT

### Critical (Must Do)

- [ ] **Get SSL Certificate** (HTTPS)
  - Use Let's Encrypt (free)
  - Configure Nginx/Apache for HTTPS

- [ ] **Change Razorpay Keys**
  ```env
  RAZORPAY_KEY_ID=rzp_live_XXXXXXXX
  RAZORPAY_KEY_SECRET=live_secret_XXXXXXXX
  ```

- [ ] **Set Strong APP_KEY**
  ```bash
  php artisan key:generate
  ```

- [ ] **Configure Production Database**
  ```env
  DB_HOST=your-production-db-host
  DB_DATABASE=production_db_name
  DB_USERNAME=production_user
  DB_PASSWORD=strong_password_here
  ```

- [ ] **Set Production URL**
  ```env
  APP_URL=https://yourdomain.com
  ```

- [ ] **Configure Email Service**
  ```env
  MAIL_MAILER=smtp
  MAIL_HOST=smtp.gmail.com
  MAIL_PORT=587
  MAIL_USERNAME=your-email@gmail.com
  MAIL_PASSWORD=your-app-password
  ```

- [ ] **Setup Backup System**
  - Daily database backups
  - Store backups off-site (AWS S3, Google Cloud)

- [ ] **Install Error Monitoring**
  - Sentry (recommended)
  - Bugsnag
  - Or Laravel Telescope

### Important (Should Do)

- [ ] **Setup Queue System**
  ```bash
  # Install Redis
  # Update .env:
  QUEUE_CONNECTION=redis
  
  # Run queue worker:
  php artisan queue:work --daemon
  ```

- [ ] **Enable Redis Caching**
  ```bash
  # Install Redis
  # Update .env:
  CACHE_STORE=redis
  SESSION_DRIVER=redis
  ```

- [ ] **Setup CDN for Images**
  - AWS CloudFront
  - Cloudflare
  - Or DigitalOcean Spaces

- [ ] **Configure PostgreSQL for Production**
  - Apply settings from postgresql_tuning.conf
  - Enable connection pooling (PgBouncer)

- [ ] **Add Health Check Endpoint**
  ```php
  Route::get('/health', function() {
      return response()->json(['status' => 'ok']);
  });
  ```

### Nice to Have

- [ ] **Setup Monitoring**
  - Server monitoring (New Relic, DataDog)
  - Uptime monitoring (UptimeRobot)

- [ ] **Add API Documentation**
  - Swagger UI already configured
  - Access at: /api/documentation

- [ ] **Setup CI/CD Pipeline**
  - GitHub Actions
  - GitLab CI
  - Jenkins

- [ ] **Load Testing**
  - Test with Apache Bench
  - Or use Loader.io

## Deployment Commands

```bash
# 1. Pull latest code
git pull origin main

# 2. Install dependencies
composer install --optimize-autoloader --no-dev

# 3. Run migrations
php artisan migrate --force

# 4. Clear and cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 5. Set permissions
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# 6. Restart services
sudo systemctl restart php8.2-fpm
sudo systemctl restart nginx
```

## Server Requirements

### Minimum
- PHP 8.2+
- PostgreSQL 14+
- 2GB RAM
- 20GB Storage
- SSL Certificate

### Recommended
- PHP 8.2+
- PostgreSQL 15+
- 4GB RAM
- 50GB SSD Storage
- Redis
- SSL Certificate
- CDN

## Security Checklist

- [ ] Firewall configured (only 80, 443, 22 open)
- [ ] SSH key-based authentication
- [ ] Fail2ban installed
- [ ] Regular security updates
- [ ] Database not publicly accessible
- [ ] .env file not in git
- [ ] Strong passwords everywhere

## Post-Deployment Testing

- [ ] Test user registration
- [ ] Test login/logout
- [ ] Test product listing
- [ ] Test add to cart
- [ ] Test checkout process
- [ ] Test payment (small amount)
- [ ] Test seller dashboard
- [ ] Test admin panel
- [ ] Test image uploads
- [ ] Check error logs

## Rollback Plan

If deployment fails:
```bash
# 1. Revert code
git reset --hard previous_commit

# 2. Rollback database
php artisan migrate:rollback

# 3. Clear cache
php artisan cache:clear

# 4. Restart services
sudo systemctl restart php8.2-fpm nginx
```

## Support Contacts

- Hosting Provider: _______________
- Domain Registrar: _______________
- Payment Gateway: _______________
- Email Service: _______________

## Notes

- Keep .env file secure (never commit to git)
- Document all configuration changes
- Test in staging environment first
- Have rollback plan ready
- Monitor logs after deployment
