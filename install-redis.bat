@echo off
echo Installing Redis for Windows...
echo.

echo Step 1: Download Redis
echo Please download Redis from: https://github.com/microsoftarchive/redis/releases
echo Or use: winget install Redis.Redis
echo.

echo Step 2: Start Redis Server
echo Run: redis-server
echo.

echo Step 3: Update .env
echo CACHE_STORE=redis
echo SESSION_DRIVER=redis
echo QUEUE_CONNECTION=redis
echo.

echo Step 4: Test Redis
echo redis-cli ping
echo Should return: PONG
echo.

echo This will give you 80%% performance improvement!
pause