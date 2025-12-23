@echo off
echo Starting Laravel Backend Server...
echo.
echo Backend will run on: http://localhost:8000
echo Press Ctrl+C to stop
echo.
php artisan serve --host=127.0.0.1 --port=8000
