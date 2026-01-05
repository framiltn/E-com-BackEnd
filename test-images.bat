@echo off
echo Testing Product Images...
echo.

echo Testing iPhone image:
curl -I "http://localhost:8000/images/products/electronics_iphone_1767595208932.png" | findstr "200 OK"

echo.
echo Testing Samsung image:
curl -I "http://localhost:8000/images/products/electronics_samsung_1767595225967.png" | findstr "200 OK"

echo.
echo Testing API response with images:
curl -s "http://localhost:8000/api/products/1" | findstr "images"

echo.
echo All images should be accessible now!
pause