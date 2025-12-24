@echo off
echo ========================================
echo STARTING OPTIMIZED MARKETPLACE
echo ========================================

echo.
echo Starting Backend (Production Mode)...
start cmd /k "cd BackEnd\marketplace-backend && php artisan serve"

timeout /t 3

echo.
echo Starting Frontend (Production Build)...
start cmd /k "cd FrontEnd && npm start"

echo.
echo ========================================
echo SERVERS STARTED!
echo ========================================
echo Backend: http://localhost:8000
echo Frontend: http://localhost:3000
echo.
pause
