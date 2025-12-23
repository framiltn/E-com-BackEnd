# Final Production Deployment Checklist

## ✅ COMPLETED AUTOMATICALLY

### Security
- [x] Strong password validation (8+ chars, complexity)
- [x] Login rate limiting (5 attempts/min)
- [x] Input sanitization middleware
- [x] Security headers middleware
- [x] CORS configuration
- [x] XSS protection
- [x] Role-based access control

### Configuration
- [x] Production .env template created
- [x] Security configuration file
- [x] Logging channels (performance, security)
- [x] Rate limiting configuration

### Monitoring
- [x] Health check endpoints
- [x] Performance monitoring service
- [x] Database query tracking
- [x] System metrics collection

### Testing
- [x] Authentication test suite
- [x] Security test suite
- [x] API endpoint tests

### DevOps
- [x] CI/CD pipeline (GitHub Actions)
- [x] Automated backup script
- [x] Deployment script
- [x] Database optimization command
- [x] Production validation command

### Infrastructure
- [x] Nginx configuration template
- [x] Systemd service for queue worker
- [x] Performance indexes migration

### Documentation
- [x] Production deployment guide
- [x] Production readiness summary
- [x] API documentation

---

## 🔧 MANUAL TASKS REQUIRED

### 1. Environment Configuration (30 minutes)

```bash
cd /var/www/marketplace
cp .env.production .env
nano .env
```

Update these values:
- [ ] `APP_KEY` - Run: `php artisan key:generate`
- [ ] `APP_URL` - Your production domain
- [ ] `DB_HOST` - Database server IP
- [ ] `DB_DATABASE` - Production database name
- [ ] `DB_USERNAME` - Database user
- [ ] `DB_PASSWORD` - Strong database password
- [ ] `RAZORPAY_KEY_ID` - Live Razorpay key
- [ ] `RAZORPAY_KEY_SECRET` - Live Razorpay secret
- [ ] `MAIL_HOST` - SMTP server
- [ ] `MAIL_USERNAME` - Email address
- [ ] `MAIL_PASSWORD` - Email password
- [ ] `AWS_ACCESS_KEY_ID` - AWS key (if using S3)
- [ ] `AWS_SECRET_ACCESS_KEY` - AWS secret
- [ ] `AWS_BUCKET` - S3 bucket name
- [ ] `FRONTEND_URL` - Frontend domain
- [ ] `SENTRY_LARAVEL_DSN` - Sentry DSN (optional)

### 2. Server Setup (2 hours)

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install PHP 8.2
sudo apt install -y php8.2-fpm php8.2-pgsql php8.2-redis \
    php8.2-mbstring php8.2-xml php8.2-curl php8.2-zip \
    php8.2-gd php8.2-bcmath

# Install PostgreSQL
sudo apt install -y postgresql postgresql-contrib

# Install Redis
sudo apt install -y redis-server

# Install Nginx
sudo apt install -y nginx

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

- [ ] PHP 8.2 installed
- [ ] PostgreSQL installed
- [ ] Redis installed
- [ ] Nginx installed
- [ ] Composer installed

### 3. Database Setup (15 minutes)

```bash
sudo -u postgres psql
```

```sql
CREATE DATABASE marketplace_production;
CREATE USER marketplace_user WITH ENCRYPTED PASSWORD 'YOUR_STRONG_PASSWORD';
GRANT ALL PRIVILEGES ON DATABASE marketplace_production TO marketplace_user;
\q
```

- [ ] Database created
- [ ] User created with strong password
- [ ] Privileges granted

### 4. SSL Certificate (20 minutes)

```bash
# Install Certbot
sudo apt install -y certbot python3-certbot-nginx

# Get certificate
sudo certbot --nginx -d api.yourdomain.com

# Test auto-renewal
sudo certbot renew --dry-run
```

- [ ] Certbot installed
- [ ] SSL certificate obtained
- [ ] Auto-renewal configured

### 5. Application Deployment (30 minutes)

```bash
# Clone repository
cd /var/www
sudo git clone YOUR_REPO_URL marketplace
cd marketplace

# Set ownership
sudo chown -R www-data:www-data /var/www/marketplace

# Install dependencies
composer install --optimize-autoloader --no-dev

# Run migrations
php artisan migrate --force

# Add performance indexes
php artisan migrate --path=database/migrations/2024_01_01_000001_add_performance_indexes.php --force

# Seed initial data
php artisan db:seed --force

# Cache everything
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# Set permissions
sudo chmod -R 775 storage bootstrap/cache
```

- [ ] Code deployed
- [ ] Dependencies installed
- [ ] Migrations run
- [ ] Performance indexes added
- [ ] Initial data seeded
- [ ] Caches built
- [ ] Permissions set

### 6. Nginx Configuration (15 minutes)

```bash
# Copy configuration
sudo cp nginx.conf /etc/nginx/sites-available/marketplace

# Update domain in config
sudo nano /etc/nginx/sites-available/marketplace

# Enable site
sudo ln -s /etc/nginx/sites-available/marketplace /etc/nginx/sites-enabled/

# Test configuration
sudo nginx -t

# Restart Nginx
sudo systemctl restart nginx
```

- [ ] Nginx configured
- [ ] Configuration tested
- [ ] Nginx restarted

### 7. Queue Worker Setup (10 minutes)

```bash
# Copy service file
sudo cp marketplace-worker.service /etc/systemd/system/

# Reload systemd
sudo systemctl daemon-reload

# Enable and start worker
sudo systemctl enable marketplace-worker
sudo systemctl start marketplace-worker

# Check status
sudo systemctl status marketplace-worker
```

- [ ] Service file installed
- [ ] Worker enabled
- [ ] Worker running

### 8. Backup Configuration (15 minutes)

```bash
# Make backup script executable
chmod +x backup.sh

# Test backup
./backup.sh

# Add to crontab
sudo crontab -e
```

Add this line:
```
0 2 * * * cd /var/www/marketplace && ./backup.sh >> /var/log/marketplace-backup.log 2>&1
```

- [ ] Backup script tested
- [ ] Cron job configured

### 9. Firewall Setup (10 minutes)

```bash
# Configure UFW
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable

# Check status
sudo ufw status
```

- [ ] Firewall configured
- [ ] Ports opened
- [ ] Firewall enabled

### 10. Monitoring Setup (30 minutes)

```bash
# Install Sentry (optional)
composer require sentry/sentry-laravel
php artisan sentry:publish --dsn=YOUR_SENTRY_DSN

# Test Sentry
php artisan sentry:test
```

- [ ] Sentry installed (optional)
- [ ] Error tracking configured
- [ ] Uptime monitoring configured (external service)

---

## 🧪 TESTING & VERIFICATION

### 1. Run Tests

```bash
php artisan test
```

- [ ] All tests passing

### 2. Validate Configuration

```bash
php artisan production:validate
```

- [ ] No critical errors
- [ ] Warnings addressed

### 3. Health Checks

```bash
# Basic health
curl https://api.yourdomain.com/api/health

# Detailed health
curl https://api.yourdomain.com/api/health/detailed
```

- [ ] Health endpoints responding
- [ ] All components healthy

### 4. API Testing

```bash
# API info
curl https://api.yourdomain.com/api/

# Test registration
curl -X POST https://api.yourdomain.com/api/register \
  -H "Content-Type: application/json" \
  -d '{"name":"Test","email":"test@example.com","password":"Test@123"}'
```

- [ ] API responding
- [ ] Authentication working
- [ ] Rate limiting working

### 5. Performance Testing

```bash
# Run database optimization
php artisan db:optimize

# Check slow queries
cat storage/logs/performance.log | grep "Slow query"
```

- [ ] Database optimized
- [ ] No slow queries

---

## 🔒 SECURITY VERIFICATION

```bash
# Check file permissions
ls -la storage/
ls -la bootstrap/cache/

# Verify .env not accessible
curl https://api.yourdomain.com/.env

# Check security headers
curl -I https://api.yourdomain.com/api/health
```

- [ ] Correct file permissions
- [ ] .env not accessible
- [ ] Security headers present
- [ ] HTTPS working
- [ ] SSL certificate valid

---

## 📊 POST-DEPLOYMENT MONITORING (First 24 Hours)

### Monitor Logs
```bash
# Application logs
tail -f storage/logs/laravel.log

# Performance logs
tail -f storage/logs/performance.log

# Security logs
tail -f storage/logs/security.log

# Nginx logs
sudo tail -f /var/log/nginx/marketplace-error.log
```

### Monitor Services
```bash
# Check all services
sudo systemctl status nginx
sudo systemctl status php8.2-fpm
sudo systemctl status postgresql
sudo systemctl status redis
sudo systemctl status marketplace-worker
```

### Monitor Resources
```bash
# System resources
htop

# Database connections
sudo -u postgres psql -c "SELECT count(*) FROM pg_stat_activity;"

# Redis memory
redis-cli INFO memory

# Disk space
df -h
```

---

## 🎯 SUCCESS CRITERIA

- [ ] All manual tasks completed
- [ ] All tests passing
- [ ] Health checks returning OK
- [ ] SSL certificate valid
- [ ] No errors in logs
- [ ] Services running
- [ ] Backups working
- [ ] Monitoring active
- [ ] Performance acceptable
- [ ] Security verified

---

## 📞 ROLLBACK PROCEDURE

If something goes wrong:

```bash
# 1. Enable maintenance mode
php artisan down

# 2. Revert code
git reset --hard PREVIOUS_COMMIT

# 3. Rollback migrations
php artisan migrate:rollback

# 4. Clear caches
php artisan cache:clear
php artisan config:clear

# 5. Restart services
sudo systemctl restart php8.2-fpm
sudo systemctl restart marketplace-worker

# 6. Disable maintenance mode
php artisan up
```

---

## 🎉 DEPLOYMENT COMPLETE!

Once all checkboxes are marked:

1. Monitor for 24-48 hours
2. Test all critical user flows
3. Verify payment processing
4. Check email delivery
5. Monitor error rates
6. Review performance metrics

**Estimated Total Time: 4-5 hours**

**Production Readiness: 88% → 100%**
