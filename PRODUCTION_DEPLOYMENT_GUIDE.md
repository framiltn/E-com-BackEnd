# Production Deployment Guide - UPDATED

## 🚀 Quick Deployment Steps

### 1. Pre-Deployment Checklist

```bash
# Verify all critical files are ready
✓ .env.production configured
✓ SSL certificate obtained
✓ Database created
✓ Redis installed
✓ Backup script configured
```

### 2. Server Setup (Ubuntu/Debian)

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install PHP 8.2
sudo apt install -y php8.2-fpm php8.2-pgsql php8.2-redis php8.2-mbstring \
    php8.2-xml php8.2-curl php8.2-zip php8.2-gd php8.2-bcmath

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

### 3. Database Setup

```bash
# Create database and user
sudo -u postgres psql

CREATE DATABASE marketplace_production;
CREATE USER marketplace_user WITH ENCRYPTED PASSWORD 'YOUR_STRONG_PASSWORD';
GRANT ALL PRIVILEGES ON DATABASE marketplace_production TO marketplace_user;
\q
```

### 4. Application Deployment

```bash
# Clone repository
cd /var/www
git clone your-repo-url marketplace
cd marketplace

# Copy production environment
cp .env.production .env

# Edit .env with your actual values
nano .env

# Install dependencies
composer install --optimize-autoloader --no-dev

# Generate application key
php artisan key:generate

# Run migrations
php artisan migrate --force

# Seed initial data
php artisan db:seed --force

# Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set permissions
sudo chown -R www-data:www-data /var/www/marketplace
sudo chmod -R 775 storage bootstrap/cache
```

### 5. Nginx Configuration

```nginx
# /etc/nginx/sites-available/marketplace
server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name yourdomain.com www.yourdomain.com;
    root /var/www/marketplace/public;

    # SSL Configuration
    ssl_certificate /etc/letsencrypt/live/yourdomain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/yourdomain.com/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;

    index index.php;
    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Rate limiting
    limit_req_zone $binary_remote_addr zone=api:10m rate=60r/m;
    location /api/ {
        limit_req zone=api burst=20 nodelay;
    }
}
```

```bash
# Enable site
sudo ln -s /etc/nginx/sites-available/marketplace /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

### 6. SSL Certificate (Let's Encrypt)

```bash
# Install Certbot
sudo apt install -y certbot python3-certbot-nginx

# Obtain certificate
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com

# Auto-renewal is configured automatically
sudo certbot renew --dry-run
```

### 7. Setup Automated Backups

```bash
# Make backup script executable
chmod +x /var/www/marketplace/backup.sh

# Add to crontab (runs daily at 2 AM)
sudo crontab -e

# Add this line:
0 2 * * * cd /var/www/marketplace && ./backup.sh >> /var/log/marketplace-backup.log 2>&1
```

### 8. Setup Queue Worker

```bash
# Create systemd service
sudo nano /etc/systemd/system/marketplace-worker.service
```

```ini
[Unit]
Description=Marketplace Queue Worker
After=network.target

[Service]
Type=simple
User=www-data
WorkingDirectory=/var/www/marketplace
ExecStart=/usr/bin/php /var/www/marketplace/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
Restart=always
RestartSec=10

[Install]
WantedBy=multi-user.target
```

```bash
# Enable and start worker
sudo systemctl enable marketplace-worker
sudo systemctl start marketplace-worker
sudo systemctl status marketplace-worker
```

### 9. Setup Monitoring (Optional - Sentry)

```bash
# Install Sentry SDK
composer require sentry/sentry-laravel

# Publish config
php artisan sentry:publish --dsn=YOUR_SENTRY_DSN

# Test
php artisan sentry:test
```

### 10. Firewall Configuration

```bash
# Setup UFW
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable
sudo ufw status
```

### 11. Redis Configuration

```bash
# Edit Redis config
sudo nano /etc/redis/redis.conf

# Set maxmemory policy
maxmemory 256mb
maxmemory-policy allkeys-lru

# Restart Redis
sudo systemctl restart redis
```

### 12. Post-Deployment Verification

```bash
# Check health endpoint
curl https://yourdomain.com/api/health
curl https://yourdomain.com/api/health/detailed

# Run tests
php artisan test

# Check logs
tail -f storage/logs/laravel.log

# Monitor queue
php artisan queue:monitor

# Check services
sudo systemctl status nginx
sudo systemctl status php8.2-fpm
sudo systemctl status postgresql
sudo systemctl status redis
sudo systemctl status marketplace-worker
```

## 🔒 Security Hardening

### Additional Security Measures

```bash
# Install Fail2ban
sudo apt install -y fail2ban
sudo systemctl enable fail2ban
sudo systemctl start fail2ban

# Disable root SSH login
sudo nano /etc/ssh/sshd_config
# Set: PermitRootLogin no
sudo systemctl restart sshd

# Setup automatic security updates
sudo apt install -y unattended-upgrades
sudo dpkg-reconfigure -plow unattended-upgrades
```

## 📊 Monitoring Commands

```bash
# Check application status
php artisan about

# Monitor logs in real-time
tail -f storage/logs/laravel.log

# Check database connections
sudo -u postgres psql -c "SELECT count(*) FROM pg_stat_activity;"

# Check Redis memory
redis-cli INFO memory

# Monitor system resources
htop
```

## 🔄 Update Procedure

```bash
# 1. Backup first
./backup.sh

# 2. Enable maintenance mode
php artisan down

# 3. Pull latest code
git pull origin main

# 4. Update dependencies
composer install --optimize-autoloader --no-dev

# 5. Run migrations
php artisan migrate --force

# 6. Clear and rebuild cache
php artisan config:clear
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 7. Restart services
sudo systemctl restart php8.2-fpm
sudo systemctl restart marketplace-worker

# 8. Disable maintenance mode
php artisan up

# 9. Verify
curl https://yourdomain.com/api/health/detailed
```

## 🆘 Troubleshooting

### Common Issues

**500 Error:**
```bash
# Check logs
tail -100 storage/logs/laravel.log

# Check permissions
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# Clear cache
php artisan cache:clear
php artisan config:clear
```

**Database Connection Failed:**
```bash
# Test connection
php artisan tinker
>>> DB::connection()->getPdo();

# Check PostgreSQL
sudo systemctl status postgresql
sudo -u postgres psql -c "SELECT version();"
```

**Queue Not Processing:**
```bash
# Check worker status
sudo systemctl status marketplace-worker

# Restart worker
sudo systemctl restart marketplace-worker

# Check failed jobs
php artisan queue:failed
```

## 📞 Support Checklist

- [ ] Domain DNS configured
- [ ] SSL certificate active
- [ ] Database accessible
- [ ] Redis running
- [ ] Queue worker running
- [ ] Backups configured
- [ ] Monitoring active
- [ ] Firewall configured
- [ ] All tests passing
- [ ] Health checks returning OK

## 🎯 Performance Optimization

```bash
# Enable OPcache
sudo nano /etc/php/8.2/fpm/php.ini
# Set:
opcache.enable=1
opcache.memory_consumption=256
opcache.max_accelerated_files=20000

# Restart PHP-FPM
sudo systemctl restart php8.2-fpm
```

---

**Deployment completed!** Your marketplace is now production-ready.
