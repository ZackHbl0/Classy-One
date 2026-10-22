@echo off
setlocal enabledelayedexpansion

echo ================================================================
echo    CLASSY-ONE ACADEMY - EMERGENCY BACKUP RESTORATION SCRIPT
echo ================================================================
echo.

:: 1. Ensure we are in project root
set "SCRIPT_DIR=%~dp0"
cd /d "%SCRIPT_DIR%"

:: 2. Check if PHP is available
where php >nul 2>nul
if %errorlevel% neq 0 (
    echo [ERROR] PHP executable was not found in your system PATH!
    echo Attempting Laragon PHP fallback...
    if exist "C:\laragon\bin\php\php-8.3.16-Win32-vs16-x64\php.exe" (
        set "PHP_CMD=C:\laragon\bin\php\php-8.3.16-Win32-vs16-x64\php.exe"
    ) else (
        echo [CRITICAL ERROR] PHP could not be located. Aborting restoration.
        pause
        exit /b 1
    )
) else (
    set "PHP_CMD=php"
)

echo Using PHP: %PHP_CMD%
echo.

:: 3. Check arguments or default to interactive prompt
if "%1"=="" (
    echo Options:
    echo  [1] Restore LATEST backup (Database + Uploaded Files)
    echo  [2] Restore LATEST backup (Database ONLY)
    echo  [3] Restore LATEST backup (Files ONLY)
    echo  [4] Specify a custom backup zip file
    echo  [5] Exit
    echo.
    set /p CHOICE="Enter choice [1-5]: "

    if "!CHOICE!"=="1" (
        %PHP_CMD% artisan backup:restore --latest
    ) else if "!CHOICE!"=="2" (
        %PHP_CMD% artisan backup:restore --latest --db-only
    ) else if "!CHOICE!"=="3" (
        %PHP_CMD% artisan backup:restore --latest --files-only
    ) else if "!CHOICE!"=="4" (
        set /p CUSTOM_FILE="Enter full path or filename of backup zip: "
        %PHP_CMD% artisan backup:restore --file="!CUSTOM_FILE!"
    ) else (
        echo Operation cancelled.
        exit /b 0
    )
) else (
    :: Run with arguments passed directly
    %PHP_CMD% artisan backup:restore %*
)

echo.
echo ================================================================
echo    Restoration process finished.
echo ================================================================
echo.
pause
