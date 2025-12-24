@echo off
echo ========================================
echo OPTIMIZING MARKETPLACE PROJECT
echo ========================================

echo.
echo [1/5] Optimizing Backend...
cd BackEnd\marketplace-backend
call php artisan config:clear
call php artisan cache:clear
call php artisan route:clear
call php artisan view:clear
call php artisan optimize
call php artisan config:cache
call php artisan route:cache
call php artisan view:cache
call php artisan migrate --force
echo Backend optimized!

echo.
echo [2/5] Building Frontend Production...
cd ..\..\FrontEnd
call npm run build
echo Frontend built!

echo.
echo ========================================
echo OPTIMIZATION COMPLETE!
echo ========================================
echo.
echo Next steps:
echo 1. Stop current servers (Ctrl+C)
echo 2. Backend: cd BackEnd\marketplace-backend ^&^& php artisan serve
echo 3. Frontend: cd FrontEnd ^&^& npm start
echo.
pause
