@echo off
echo Starting Performance Optimization...

echo Clearing all caches...
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

echo Optimizing configuration...
php artisan config:cache
php artisan route:cache

echo Running database optimizations...
php artisan migrate --force

echo Performance optimization complete!
echo.
echo To start Redis (if available):
echo redis-server
echo.
echo Then update .env:
echo CACHE_STORE=redis
echo SESSION_DRIVER=redis
echo QUEUE_CONNECTION=redis
echo.
pause