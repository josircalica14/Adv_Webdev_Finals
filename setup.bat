@echo off
title Portfolio Platform - Setup
color 0A
cls

echo ============================================
echo   Portfolio Platform - Auto Setup
echo ============================================
echo.

:: ── 1. Find PHP ──────────────────────────────
set PHP=
for %%P in (
    "C:\xampp\php\php.exe"
    "C:\wamp64\bin\php\php8.2.0\php.exe"
    "C:\wamp\bin\php\php8.2.0\php.exe"
    "C:\laragon\bin\php\php-8.2\php.exe"
) do (
    if exist %%P (
        set PHP=%%P
        goto :found_php
    )
)
:: fallback: try php from PATH
where php >nul 2>&1
if %errorlevel%==0 (
    set PHP=php
    goto :found_php
)
echo [ERROR] PHP not found. Make sure XAMPP/Laragon is installed.
pause & exit /b 1
:found_php
echo [OK] PHP found: %PHP%

:: ── 2. Find MySQL ────────────────────────────
set MYSQL=
set MYSQLDUMP=
for %%M in (
    "C:\xampp\mysql\bin\mysql.exe"
    "C:\wamp64\bin\mysql\mysql8.0.31\bin\mysql.exe"
    "C:\wamp\bin\mysql\mysql8.0.31\bin\mysql.exe"
    "C:\laragon\bin\mysql\mysql-8.0\bin\mysql.exe"
) do (
    if exist %%M (
        set MYSQL=%%M
        goto :found_mysql
    )
)
where mysql >nul 2>&1
if %errorlevel%==0 (
    set MYSQL=mysql
    goto :found_mysql
)
echo [ERROR] MySQL not found. Make sure XAMPP/Laragon is installed and running.
pause & exit /b 1
:found_mysql
echo [OK] MySQL found: %MYSQL%

:: ── 3. Ask for MySQL credentials ─────────────
echo.
set /p DB_USER=MySQL username (default: root): 
if "%DB_USER%"=="" set DB_USER=root

set /p DB_PASS=MySQL password (leave blank if none): 

set /p DB_NAME=Database name (default: portfolio_laravel): 
if "%DB_NAME%"=="" set DB_NAME=portfolio_laravel

:: Build mysql auth args
set MYSQL_ARGS=-u %DB_USER% -h 127.0.0.1
if not "%DB_PASS%"=="" set MYSQL_ARGS=%MYSQL_ARGS% -p%DB_PASS%

:: ── 4. Create database ───────────────────────
echo.
echo [*] Creating database "%DB_NAME%"...
%MYSQL% %MYSQL_ARGS% -e "CREATE DATABASE IF NOT EXISTS %DB_NAME% CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>nul
if %errorlevel% neq 0 (
    echo [ERROR] Could not connect to MySQL. Check credentials and make sure MySQL is running.
    pause & exit /b 1
)
echo [OK] Database ready.

:: ── 5. Import SQL dump ───────────────────────
echo [*] Looking for SQL dump...
set SQL_FILE=
for %%F in ("database\exports\*.sql") do set SQL_FILE=%%F

if "%SQL_FILE%"=="" (
    echo [WARN] No SQL dump found in database\exports\ - skipping import.
    echo        Run migrations manually: php artisan migrate --seed
    goto :skip_import
)

echo [*] Importing %SQL_FILE%...
%MYSQL% %MYSQL_ARGS% %DB_NAME% < %SQL_FILE%
if %errorlevel% neq 0 (
    echo [ERROR] Import failed. Check the SQL file and MySQL connection.
    pause & exit /b 1
)
echo [OK] Database imported.
:skip_import

:: ── 6. Set up .env ───────────────────────────
echo.
echo [*] Setting up .env...
if not exist ".env" (
    copy ".env.example" ".env" >nul
    echo [OK] Created .env from .env.example
) else (
    echo [OK] .env already exists, skipping copy.
)

:: Patch DB settings in .env using PowerShell (reliable find+replace)
powershell -Command "(Get-Content .env) -replace 'DB_CONNECTION=.*','DB_CONNECTION=mysql' | Set-Content .env"
powershell -Command "(Get-Content .env) -replace '# DB_HOST=.*','DB_HOST=127.0.0.1' | Set-Content .env"
powershell -Command "(Get-Content .env) -replace 'DB_HOST=.*','DB_HOST=127.0.0.1' | Set-Content .env"
powershell -Command "(Get-Content .env) -replace '# DB_PORT=.*','DB_PORT=3306' | Set-Content .env"
powershell -Command "(Get-Content .env) -replace 'DB_PORT=.*','DB_PORT=3306' | Set-Content .env"
powershell -Command "(Get-Content .env) -replace '# DB_DATABASE=.*','DB_DATABASE=%DB_NAME%' | Set-Content .env"
powershell -Command "(Get-Content .env) -replace 'DB_DATABASE=.*','DB_DATABASE=%DB_NAME%' | Set-Content .env"
powershell -Command "(Get-Content .env) -replace '# DB_USERNAME=.*','DB_USERNAME=%DB_USER%' | Set-Content .env"
powershell -Command "(Get-Content .env) -replace 'DB_USERNAME=.*','DB_USERNAME=%DB_USER%' | Set-Content .env"
powershell -Command "(Get-Content .env) -replace '# DB_PASSWORD=.*','DB_PASSWORD=%DB_PASS%' | Set-Content .env"
powershell -Command "(Get-Content .env) -replace 'DB_PASSWORD=.*','DB_PASSWORD=%DB_PASS%' | Set-Content .env"
echo [OK] .env configured.

:: ── 6b. API Keys ─────────────────────────────
echo.
echo [*] AI API Keys (press Enter to skip if already set in .env)
set /p GROQ_KEY=Groq API Key: 
set /p CHROME_PATH=Chrome path for PDF export (e.g. C:/Program Files/Google/Chrome/Application/chrome.exe): 

if not "%GROQ_KEY%"=="" (
    powershell -Command "(Get-Content .env) -replace 'GROQ_API_KEY=.*','GROQ_API_KEY=%GROQ_KEY%' | Set-Content .env"
    echo [OK] Groq API key set.
)
if not "%CHROME_PATH%"=="" (
    powershell -Command "(Get-Content .env) -replace 'BROWSERSHOT_CHROME_PATH=.*','BROWSERSHOT_CHROME_PATH=%CHROME_PATH%' | Set-Content .env"
    echo [OK] Chrome path set.
)

:: ── 7. Composer install ──────────────────────
echo.
echo [*] Installing PHP dependencies...
if not exist "vendor" (
    where composer >nul 2>&1
    if %errorlevel% neq 0 (
        echo [WARN] Composer not found in PATH. Trying composer.phar...
        if exist "composer.phar" (
            %PHP% composer.phar install --no-interaction --prefer-dist
        ) else (
            echo [ERROR] Composer not found. Download from https://getcomposer.org
            pause & exit /b 1
        )
    ) else (
        composer install --no-interaction --prefer-dist
    )
) else (
    echo [OK] vendor/ already exists, skipping composer install.
)

:: ── 8. App key ───────────────────────────────
echo.
echo [*] Generating app key...
%PHP% artisan key:generate --force
echo [OK] App key set.

:: ── 8b. npm install ──────────────────────────
echo [*] Installing Node dependencies (Puppeteer for PDF)...
where npm >nul 2>&1
if %errorlevel%==0 (
    npm install --silent
    echo [OK] Node dependencies installed.
) else (
    echo [WARN] npm not found - PDF export may not work. Install Node.js from https://nodejs.org
)

:: ── 9. Storage link ──────────────────────────
echo [*] Creating storage symlink...
%PHP% artisan storage:link 2>nul
echo [OK] Storage linked.

:: ── 10. Clear caches ─────────────────────────
echo [*] Clearing caches...
%PHP% artisan config:clear >nul 2>&1
%PHP% artisan cache:clear >nul 2>&1
echo [OK] Caches cleared.

:: ── 11. Launch ───────────────────────────────
echo.
echo ============================================
echo   Setup complete!
echo   Opening http://localhost:8000 ...
echo   Press Ctrl+C in this window to stop.
echo ============================================
echo.
start "" "http://localhost:8000"
%PHP% artisan serve
