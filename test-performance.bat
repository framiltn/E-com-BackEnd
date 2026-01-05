@echo off
echo Testing API Performance...
echo.

echo Testing Products API...
curl -w "Time: %%{time_total}s\n" -s -o nul "http://localhost:8000/api/products"

echo.
echo Testing Single Product API...
curl -w "Time: %%{time_total}s\n" -s -o nul "http://localhost:8000/api/products/1"

echo.
echo Performance test complete!
pause