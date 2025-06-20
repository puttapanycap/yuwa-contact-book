#!/bin/bash

echo "========================================"
echo "Corporate Phonebook Installation Script"
echo "========================================"
echo

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if PHP is installed
if ! command -v php &> /dev/null; then
    print_error "PHP is not installed or not in PATH"
    print_error "Please install PHP 8.0 or higher"
    exit 1
fi

# Check PHP version
PHP_VERSION=$(php -r "echo PHP_VERSION;")
print_status "Found PHP version: $PHP_VERSION"

# Check if MySQL is available
if ! command -v mysql &> /dev/null; then
    print_warning "MySQL client not found in PATH"
    print_warning "Please make sure MySQL is installed and accessible"
fi

echo
print_status "Step 1: Creating environment file..."
if [ ! -f .env ]; then
    cp .env-example .env
    print_status "Environment file created from .env-example"
else
    print_status "Environment file already exists"
fi

echo
print_status "Step 2: Setting up directory permissions..."

# Create directories if they don't exist
mkdir -p assets/css assets/js admin api config database logs backups

# Set appropriate permissions
chmod 755 assets admin api config database
chmod 766 logs backups
chmod 644 .env-example
if [ -f .env ]; then
    chmod 600 .env
fi

print_status "Directories created and permissions set successfully"

echo
print_status "Step 3: Database setup..."
echo "Please configure your database settings in .env file"
echo "Then run the following command to create the database:"
echo "mysql -u [username] -p [database_name] < database/schema.sql"
echo

print_status "Step 4: Web server setup..."
echo
echo "Option 1 - Using PHP built-in server (Development):"
echo "  php -S localhost:8000"
echo
echo "Option 2 - Using Apache/Nginx (Production):"
echo "  - Copy files to your web server document root"
echo "  - Configure virtual host"
echo "  - Ensure mod_rewrite is enabled (Apache)"
echo

print_status "Step 5: Final configuration..."
echo "1. Edit .env file with your database credentials"
echo "2. Import database/schema.sql to your MySQL database"
echo "3. Set appropriate file permissions for logs and backups directories"
echo "4. Configure your web server to point to this directory"
echo

echo "========================================"
print_status "Installation completed!"
echo "========================================"
echo
echo "Next steps:"
echo "1. Configure database settings in .env"
echo "2. Import database schema"
echo "3. Start your web server"
echo "4. Access the application in your browser"
echo

# Ask if user wants to start development server
read -p "Do you want to start PHP development server now? (y/n): " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    print_status "Starting PHP development server on http://localhost:8000"
    print_status "Press Ctrl+C to stop the server"
    php -S localhost:8000
fi
