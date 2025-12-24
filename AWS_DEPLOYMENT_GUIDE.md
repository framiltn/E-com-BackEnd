# AWS Deployment Guide - Multi-Vendor Marketplace

## Architecture Overview

```
Internet → CloudFront (CDN) → Load Balancer → EC2 Instances
                                              ↓
                                         RDS PostgreSQL
                                              ↓
                                         S3 (File Storage)
```

---

## Cost Estimate (Monthly)

| Service | Configuration | Cost |
|---------|--------------|------|
| EC2 (t3.medium) | 2 vCPU, 4GB RAM | $30 |
| RDS PostgreSQL (db.t3.micro) | 2GB RAM | $15 |
| S3 Storage | 10GB | $0.23 |
| CloudFront | 50GB transfer | $4 |
| Route 53 | Domain | $0.50 |
| **Total** | | **~$50/month** |

---

## Step-by-Step Deployment

### 1. Create AWS Account
- Go to aws.amazon.com
- Sign up (requires credit card)
- Verify email

### 2. Launch EC2 Instance

**Console → EC2 → Launch Instance:**
- Name: `marketplace-server`
- AMI: Ubuntu Server 22.04 LTS
- Instance type: `t3.medium` (2 vCPU, 4GB RAM)
- Key pair: Create new → Download `.pem` file
- Security group: Allow ports 22, 80, 443, 8000, 3000
- Storage: 30GB gp3
- Launch instance

### 3. Setup RDS PostgreSQL

**Console → RDS → Create Database:**
- Engine: PostgreSQL 16
- Template: Free tier (or Production)
- DB instance: `db.t3.micro`
- DB name: `marketplace`
- Master username: `postgres`
- Master password: (save this!)
- Public access: Yes (for now)
- Create database

**Note the endpoint:** `marketplace.xxxxx.us-east-1.rds.amazonaws.com`

### 4. Connect to EC2

**Windows (PowerShell):**
```bash
ssh -i "your-key.pem" ubuntu@your-ec2-ip
```

### 5. Install Dependencies on EC2

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install PHP 8.2
sudo apt install -y software-properties-common
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install -y php8.2 php8.2-cli php8.2-fpm php8.2-pgsql php8.2-mbstring php8.2-xml php8.2-curl php8.2-zip php8.2-gd

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install Node.js 20
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs

# Install Nginx
sudo apt install -y nginx

# Install Git
sudo apt install -y git
```

### 6. Clone & Setup Backend

```bash
# Clone repository (or upload via SCP)
cd /var/www
sudo git clone YOUR_REPO_URL marketplace
cd marketplace/BackEnd/marketplace-backend

# Install dependencies
sudo composer install --optimize-autoloader --no-dev

# Setup environment
sudo cp .env.example .env
sudo nano .env
```

**Edit .env:**
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=pgsql
DB_HOST=marketplace.xxxxx.us-east-1.rds.amazonaws.com
DB_PORT=5432
DB_DATABASE=marketplace
DB_USERNAME=postgres
DB_PASSWORD=your_rds_password

CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=database
```

**Run migrations:**
```bash
sudo php artisan key:generate
sudo php artisan migrate --force
sudo php artisan db:seed --class=RoleSeeder
sudo php artisan db:seed --class=AdminSeeder
sudo php artisan optimize
sudo php artisan storage:link
```

**Set permissions:**
```bash
sudo chown -R www-data:www-data /var/www/marketplace
sudo chmod -R 755 /var/www/marketplace
sudo chmod -R 775 /var/www/marketplace/BackEnd/marketplace-backend/storage
sudo chmod -R 775 /var/www/marketplace/BackEnd/marketplace-backend/bootstrap/cache
```

### 7. Setup Frontend

```bash
cd /var/www/marketplace/FrontEnd

# Install dependencies
sudo npm install

# Create .env.local
sudo nano .env.local
```

**Add:**
```env
NEXT_PUBLIC_API_URL=https://api.yourdomain.com/api
```

**Build:**
```bash
sudo npm run build
```

### 8. Configure Nginx

```bash
sudo nano /etc/nginx/sites-available/marketplace
```

**Add:**
```nginx
# Backend API
server {
    listen 80;
    server_name api.yourdomain.com;
    root /var/www/marketplace/BackEnd/marketplace-backend/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}

# Frontend
server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;

    location / {
        proxy_pass http://localhost:3000;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host $host;
        proxy_cache_bypass $http_upgrade;
    }
}
```

**Enable site:**
```bash
sudo ln -s /etc/nginx/sites-available/marketplace /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

### 9. Setup SSL (Let's Encrypt)

```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com -d api.yourdomain.com
```

### 10. Setup PM2 (Keep Frontend Running)

```bash
sudo npm install -g pm2
cd /var/www/marketplace/FrontEnd
pm2 start npm --name "marketplace-frontend" -- start
pm2 startup
pm2 save
```

### 11. Setup Queue Worker

```bash
cd /var/www/marketplace/BackEnd/marketplace-backend
pm2 start php --name "marketplace-queue" -- artisan queue:work --tries=3
pm2 save
```

### 12. Setup Cron Jobs

```bash
sudo crontab -e
```

**Add:**
```cron
* * * * * cd /var/www/marketplace/BackEnd/marketplace-backend && php artisan schedule:run >> /dev/null 2>&1
```

---

## Domain Setup

### Route 53 (AWS DNS)

1. **Console → Route 53 → Hosted Zones**
2. Create hosted zone: `yourdomain.com`
3. Create records:
   - `A` record: `yourdomain.com` → EC2 IP
   - `A` record: `api.yourdomain.com` → EC2 IP
   - `CNAME` record: `www.yourdomain.com` → `yourdomain.com`

4. Update nameservers at your domain registrar

---

## S3 Setup (File Storage)

```bash
# Console → S3 → Create Bucket
# Bucket name: marketplace-files
# Region: us-east-1
# Block public access: OFF (for product images)
```

**Update .env:**
```env
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=your_key
AWS_SECRET_ACCESS_KEY=your_secret
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=marketplace-files
```

---

## Monitoring & Logs

**View Laravel logs:**
```bash
tail -f /var/www/marketplace/BackEnd/marketplace-backend/storage/logs/laravel.log
```

**View Nginx logs:**
```bash
tail -f /var/log/nginx/error.log
```

**View PM2 logs:**
```bash
pm2 logs
```

---

## Backup Strategy

**Database backup (daily):**
```bash
sudo crontab -e
```

**Add:**
```cron
0 2 * * * pg_dump -h marketplace.xxxxx.rds.amazonaws.com -U postgres marketplace > /backups/db-$(date +\%Y\%m\%d).sql
```

---

## Security Checklist

- [ ] Change default admin password
- [ ] Enable AWS WAF (Web Application Firewall)
- [ ] Setup CloudWatch alarms
- [ ] Enable RDS automated backups
- [ ] Restrict security group rules
- [ ] Enable MFA on AWS account
- [ ] Setup AWS Secrets Manager for credentials
- [ ] Enable CloudTrail for audit logs

---

## Performance Optimization

**Enable Redis (optional):**
```bash
sudo apt install -y redis-server
```

**Update .env:**
```env
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
```

---

## Testing Deployment

1. Visit `https://yourdomain.com` - Frontend should load
2. Visit `https://api.yourdomain.com/api/products` - Should return JSON
3. Register a user
4. Login
5. Test all features

---

## Estimated Deployment Time

- AWS setup: 30 minutes
- Server configuration: 1 hour
- Application deployment: 30 minutes
- Testing: 30 minutes
- **Total: 2-3 hours**

---

## Support & Troubleshooting

**Common Issues:**

1. **502 Bad Gateway** → Check PHP-FPM: `sudo systemctl status php8.2-fpm`
2. **Database connection failed** → Check RDS security group
3. **Frontend not loading** → Check PM2: `pm2 status`
4. **Slow performance** → Enable Redis caching

---

## Cost Optimization

**For lower costs:**
- Use `t3.micro` instead of `t3.medium` ($10/month)
- Use RDS free tier
- Use CloudFront only for production traffic

**Estimated minimum:** $15-20/month

---

## Next Steps After Deployment

1. Configure Razorpay production keys
2. Configure Shiprocket production credentials
3. Setup email service (AWS SES)
4. Configure domain email
5. Setup monitoring (AWS CloudWatch)
6. Create backup strategy
7. Load test the application

---

**Your marketplace will be live and fast on AWS! 🚀**
