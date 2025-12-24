# Performance Optimization Guide

## Quick Fixes (Immediate Impact)

### 1. Backend Optimization

#### Enable Query Caching
Add to `.env`:
```env
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
```

#### Optimize Database Queries
Add eager loading to controllers:

**ProductController.php:**
```php
// Instead of:
Product::all();

// Use:
Product::with(['seller', 'images', 'category'])->get();
```

#### Add Database Indexes
Run:
```bash
php artisan make:migration add_indexes_to_tables
```

Add indexes:
```php
$table->index('user_id');
$table->index('status');
$table->index('created_at');
```

#### Enable OPcache
In `php.ini`:
```ini
opcache.enable=1
opcache.memory_consumption=256
opcache.max_accelerated_files=20000
```

### 2. Frontend Optimization

#### Enable Production Build
```bash
npm run build
npm start
```

#### Add Image Optimization
Install:
```bash
npm install next/image
```

Use in components:
```jsx
import Image from 'next/image'
<Image src={url} width={300} height={300} />
```

#### Enable Static Generation
In `next.config.js`:
```js
module.exports = {
  output: 'standalone',
  images: {
    domains: ['localhost'],
  },
}
```

### 3. Database Optimization

#### Add Indexes
```sql
CREATE INDEX idx_products_status ON products(status);
CREATE INDEX idx_orders_user_id ON orders(user_id);
CREATE INDEX idx_seller_applications_status ON seller_applications(status);
```

#### Optimize Queries
```bash
php artisan optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## Medium-Term Fixes

### 1. Implement Caching

**Install Redis:**
```bash
composer require predis/predis
```

**Cache Products:**
```php
$products = Cache::remember('products', 3600, function () {
    return Product::with('images')->get();
});
```

### 2. Queue Heavy Operations

**Setup Queue:**
```bash
php artisan queue:table
php artisan migrate
```

**Queue emails/notifications:**
```php
dispatch(new SendEmailJob($user));
```

### 3. Optimize Images

**Backend - Add image compression:**
```bash
composer require intervention/image
```

**Frontend - Use WebP format**

### 4. API Response Pagination

Ensure all list endpoints use pagination:
```php
Product::paginate(15); // Instead of get()
```

---

## Long-Term Fixes

### 1. CDN for Static Assets
- Use Cloudflare or AWS CloudFront
- Serve images from CDN

### 2. Database Optimization
- Use read replicas
- Implement database sharding
- Regular VACUUM on PostgreSQL

### 3. Load Balancing
- Multiple server instances
- Nginx load balancer

### 4. Microservices
- Separate payment service
- Separate notification service

---

## Immediate Actions (Do Now)

### Backend:
```bash
cd BackEnd/marketplace-backend
php artisan optimize
php artisan config:cache
php artisan route:cache
```

### Frontend:
```bash
cd FrontEnd
npm run build
npm start
```

### Database:
```sql
VACUUM ANALYZE;
REINDEX DATABASE your_database;
```

---

## Performance Monitoring

### Install Laravel Debugbar:
```bash
composer require barryvdh/laravel-debugbar --dev
```

### Monitor Queries:
Check `storage/logs/laravel.log` for slow queries

### Frontend Performance:
Use Chrome DevTools → Lighthouse

---

## Expected Improvements

| Action | Speed Improvement |
|--------|------------------|
| Production build | 50-70% faster |
| Query optimization | 30-50% faster |
| Caching | 60-80% faster |
| Database indexes | 40-60% faster |
| Image optimization | 30-40% faster |

**Total Expected: 3-5x faster**

---

## Quick Test

### Before:
- Page load: 3-5 seconds
- API response: 500-1000ms

### After Optimization:
- Page load: 0.5-1 second
- API response: 50-200ms
