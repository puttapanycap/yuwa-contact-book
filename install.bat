@echo off
echo ========================================
echo Corporate Phonebook Installation Script
echo ========================================
echo.

REM Check if PHP is installed
php --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ERROR: PHP is not installed or not in PATH
    echo Please install PHP 8.0 or higher and add it to your PATH
    pause
    exit /b 1
)

REM Check PHP version
for /f "tokens=2 delims= " %%i in ('php --version ^| findstr /R "^PHP"') do set PHP_VERSION=%%i
echo Found PHP version: %PHP_VERSION%

REM Check if MySQL is available
mysql --version >nul 2>&1
if %errorlevel% neq 0 (
    echo WARNING: MySQL client not found in PATH
    echo Please make sure MySQL is installed and accessible
)

echo.
echo Step 1: Creating environment file...
if not exist .env (
    copy .env-example .env
    echo Environment file created from .env-example
) else (
    echo Environment file already exists
)

echo.
echo Step 2: Setting up directory permissions...
if not exist "assets" mkdir assets
if not exist "assets\css" mkdir assets\css
if not exist "assets\js" mkdir assets\js
if not exist "admin" mkdir admin
if not exist "api" mkdir api
if not exist "config" mkdir config
if not exist "database" mkdir database
if not exist "logs" mkdir logs
if not exist "backups" mkdir backups

echo Directories created successfully

echo.
echo Step 3: Database setup...
echo Please configure your database settings in .env file
echo Then run the following command to create the database:
echo mysql -u [username] -p [database_name] ^< database/schema.sql
echo.

echo Step 4: Web server setup...
echo.
echo Option 1 - Using PHP built-in server (Development):
echo   php -S localhost:8000
echo.
echo Option 2 - Using Apache/Nginx (Production):
echo   - Copy files to your web server document root
echo   - Configure virtual host
echo   - Ensure mod_rewrite is enabled (Apache)
echo.

echo Step 5: Final configuration...
echo 1. Edit .env file with your database credentials
echo 2. Import database/schema.sql to your MySQL database
echo 3. Set appropriate file permissions for logs and backups directories
echo 4. Configure your web server to point to this directory
echo.

echo ========================================
echo Installation completed!
echo ========================================
echo.
echo Next steps:
echo 1. Configure database settings in .env
echo 2. Import database schema
echo 3. Start your web server
echo 4. Access the application in your browser
echo.

set /p start_server="Do you want to start PHP development server now? (y/n): "
if /i "%start_server%"=="y" (
    echo Starting PHP development server on http://localhost:8000
    echo Press Ctrl+C to stop the server
    php -S localhost:8000
)

pause
