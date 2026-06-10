@echo off
echo ========================================
echo   Dynamic URL Setup - Laravel
echo ========================================
echo.

echo [1/5] Clearing Laravel caches...
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
echo ✓ Caches cleared
echo.

echo [2/5] Checking storage symlink...
if exist "public\storage" (
    echo   Storage symlink already exists
    echo   To recreate it, run: rmdir public\storage
) else (
    echo   Creating storage symlink...
    php artisan storage:link
    echo   ✓ Storage symlink created
)
echo.

echo [3/5] Verifying .env configuration...
findstr /C:"APP_URL=http://localhost:8000" .env >nul
if %errorlevel% equ 0 (
    echo   ✓ APP_URL is correctly set to localhost:8000
) else (
    echo   ⚠ WARNING: APP_URL might not be set correctly
    echo   Please ensure .env has: APP_URL=http://localhost:8000
)
echo.

echo [4/5] Checking middleware registration...
findstr /C:"DynamicApiUrl" bootstrap\app.php >nul
if %errorlevel% equ 0 (
    echo   ✓ DynamicApiUrl middleware is registered
) else (
    echo   ⚠ WARNING: DynamicApiUrl middleware not found in bootstrap\app.php
)
echo.

echo [5/5] Getting your machine's IP address...
for /f "tokens=2 delims=:" %%a in ('ipconfig ^| findstr /c:"IPv4 Address"') do (
    echo   Your IP: %%a
)
echo.

echo ========================================
echo   Setup Complete!
echo ========================================
echo.
echo Next Steps:
echo 1. Start Laravel server with: php artisan serve --host=0.0.0.0 --port=8000
echo 2. Update your Flutter app's baseUrl to your machine's IP
echo 3. Test web dashboard at: http://localhost:8000/admin
echo 4. Test mobile app with your IP address
echo.
echo For detailed instructions, see: DYNAMIC_URL_SOLUTION.md
echo.
pause
