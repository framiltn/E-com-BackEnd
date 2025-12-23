# Production Environment Setup Guide

## Step 1: Update .env File

Replace these values in your .env file:

```env
# Application
APP_NAME="Your Marketplace Name"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Database (Production)
DB_CONNECTION=pgsql
DB_HOST=your-db-host.com
DB_PORT=5432
DB_DATABASE=production_db_name
DB_USERNAME=production_user
DB_PASSWORD=strong_password_here

# Email (Gmail Example)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@yourdomain.com"
MAIL_FROM_NAME="${APP_NAME}"

# Razorpay (Production Keys)
RAZORPAY_KEY_ID=rzp_live_XXXXXXXXXXXXXXXX
RAZORPAY_KEY_SECRET=XXXXXXXXXXXXXXXXXXXXXXXX

# Queue
QUEUE_CONNECTION=database

# Cache (if using Redis)
CACHE_STORE=redis
SESSION_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

## Step 2: Get Gmail App Password

1. Go to: https://myaccount.google.com/security
2. Enable 2-Step Verification
3. Go to: https://myaccount.google.com/apppasswords
4. Create app password for "Mail"
5. Copy the 16-character password
6. Use it in MAIL_PASSWORD

## Step 3: Get Razorpay Production Keys

1. Login to: https://dashboard.razorpay.com
2. Switch to "Live Mode" (top left)
3. Go to Settings → API Keys
4. Generate Live Keys
5. Copy Key ID and Key Secret
6. Update .env file

## Step 4: Test Email

```bash
php artisan tinker

# Send test email
Mail::raw('Test email', function($message) {
    $message->to('your-email@gmail.com')
            ->subject('Test Email');
});

# Check if sent successfully
```

## Step 5: Start Queue Worker

```bash
# Run in background
nohup php artisan queue:work --daemon > /dev/null 2>&1 &

# Or use supervisor (recommended)
sudo apt install supervisor
sudo nano /etc/supervisor/conf.d/laravel-worker.conf
```

Supervisor config:
```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/your/project/artisan queue:work --sleep=3 --tries=3
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/your/project/storage/logs/worker.log
```

Then:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-worker:*
```

## Step 6: Setup Cron for Scheduled Tasks

```bash
crontab -e
```

Add:
```
* * * * * cd /path/to/your/project && php artisan schedule:run >> /dev/null 2>&1
```

## Step 7: Verify Everything Works

Test these endpoints:
```bash
# Health check
curl https://yourdomain.com/api/health

# Products
curl https://yourdomain.com/api/products

# Login
curl -X POST https://yourdomain.com/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@test.com","password":"password"}'
```

## Troubleshooting

### Email not sending?
```bash
# Check logs
tail -f storage/logs/laravel.log

# Test SMTP connection
telnet smtp.gmail.com 587
```

### Queue not processing?
```bash
# Check if worker is running
ps aux | grep queue:work

# Check failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all
```

### Database connection error?
```bash
# Test connection
php artisan tinker
DB::connection()->getPdo();
```

## Security Checklist

- [ ] APP_DEBUG=false
- [ ] Strong APP_KEY generated
- [ ] Production database credentials
- [ ] Production Razorpay keys
- [ ] HTTPS enabled
- [ ] Firewall configured
- [ ] .env file not in git
- [ ] File permissions set correctly
- [ ] Queue worker running
- [ ] Cron job configured
- [ ] Backups scheduled

## Monitoring

Add these to your monitoring:
- Health check: /api/health
- Queue status: Check worker process
- Error logs: storage/logs/laravel.log
- Database connections
- Disk space
- Memory usage

## Support

If issues persist:
1. Check storage/logs/laravel.log
2. Enable debug temporarily (APP_DEBUG=true)
3. Check server error logs
4. Verify all services running
