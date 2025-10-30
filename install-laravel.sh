#!/bin/bash

# FreePOS Laravel Installation Script
# This script helps set up the Laravel-based FreePOS application

echo "======================================"
echo "FreePOS Laravel Installation"
echo "======================================"
echo ""

# Check PHP version
echo "Checking PHP version..."
PHP_VERSION=$(php -r "echo PHP_VERSION;")
echo "PHP version: $PHP_VERSION"

if ! php -r "exit(version_compare(PHP_VERSION, '8.0.0', '>=') ? 0 : 1);"; then
    echo "ERROR: PHP 8.0 or higher is required"
    exit 1
fi

echo "✓ PHP version OK"
echo ""

# Check if composer is installed
echo "Checking Composer..."
if ! command -v composer &> /dev/null; then
    echo "ERROR: Composer is not installed"
    echo "Please install Composer from https://getcomposer.org/"
    exit 1
fi

echo "✓ Composer found"
echo ""

# Install dependencies
echo "Installing Composer dependencies..."
composer install --optimize-autoloader

if [ $? -ne 0 ]; then
    echo "ERROR: Failed to install Composer dependencies"
    exit 1
fi

echo "✓ Dependencies installed"
echo ""

# Copy .env file if it doesn't exist
if [ ! -f .env ]; then
    echo "Creating .env file..."
    if [ -f .env.example ]; then
        cp .env.example .env
        echo "✓ .env file created from .env.example"
    else
        echo "WARNING: .env.example not found, creating basic .env"
        cat > .env << 'EOF'
APP_NAME=FreePOS
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

LOG_CHANNEL=stack
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=freepos
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=file
SESSION_LIFETIME=120

CACHE_DRIVER=file
QUEUE_CONNECTION=sync
EOF
    fi
else
    echo "✓ .env file already exists"
fi

echo ""

# Generate application key
echo "Generating application key..."
php artisan key:generate

if [ $? -ne 0 ]; then
    echo "ERROR: Failed to generate application key"
    exit 1
fi

echo "✓ Application key generated"
echo ""

# Create storage directories
echo "Setting up storage directories..."
mkdir -p storage/framework/{sessions,views,cache}
mkdir -p storage/logs
mkdir -p storage/app/public

echo "✓ Storage directories created"
echo ""

# Set permissions
echo "Setting permissions..."
chmod -R 775 storage
chmod -R 775 bootstrap/cache

echo "✓ Permissions set"
echo ""

# Database setup
echo "======================================"
echo "Database Setup"
echo "======================================"
echo ""
echo "Please configure your database settings in the .env file"
echo "Then run the following command to create database tables:"
echo ""
echo "    php artisan migrate"
echo ""

# Completion message
echo "======================================"
echo "Installation Complete!"
echo "======================================"
echo ""
echo "Next steps:"
echo "1. Configure your database in .env file"
echo "2. Run: php artisan migrate"
echo "3. Start the server: php artisan serve"
echo "4. Visit http://localhost:8000"
echo ""
echo "For more information, see:"
echo "- README.md"
echo "- LARAVEL_MIGRATION.md"
echo ""
echo "Admin panel: http://localhost:8000/admin"
echo "Installer: http://localhost:8000/install"
echo ""
