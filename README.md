# FreePOS - Open Source Point Of Sale
![logo](./public/git/free_pos_600.png)
## An intuitive & modern web based POS system for retail businesses

FreePOS uses the power of the modern web to provide an easy to use & extensible POS system.

It supports standard POS hardware including receipt printers, cashdraws and barcode scanners. Runs on any device with a web browser.

With a rich administration dashboard and reporting features, FreePOS brings benefits to managers and staff alike.

Take your business into the cloud with FreePOS!

## ⚠️ Laravel Migration Notice

**FreePOS has been migrated to Laravel 11!**

This version uses the Laravel framework instead of the previous custom PHP framework. This provides:
- Better maintainability and structure
- Access to Laravel's extensive ecosystem
- Improved security and performance
- Modern PHP development practices
- Built-in artisan commands for common tasks

All existing functionality is preserved. The migration is transparent to end users.

## 🚀 Quick Start

1. Configure your web server to serve from the `public/` directory
2. Set up your environment:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
3. Configure your database in `.env`
4. Access different applications:
   - Main POS: `/` or `/pos`
   - Admin: `/admin`


## Server Prerequisites

FreePOS requires:

1. **PHP 8.0 or higher** with the following extensions:
   - cURL
   - GD
   - PDO
   - Mbstring
   - OpenSSL
   - Tokenizer
   - XML
   - JSON

2. **Composer** - Dependency manager for PHP

3. **Node.js** (optional) - For socket.io real-time features

    For a Debian distro:

    ```bash
    sudo apt-get update
    sudo apt-get install php8.0 php8.0-curl php8.0-gd php8.0-mysql
    sudo apt-get install composer
    # Optional for real-time features:
    sudo apt-get install nodejs npm
    cd %/your_install_dir%/socket
    sudo npm install
    ```

## Installation & Startup

1. Clone the latest FreePOS release to your installation directory
   
2. Install PHP dependencies:
   ```bash
   composer install
   ```

3. Configure environment:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. Set up file permissions:
   ```bash
   chmod -R 775 storage bootstrap/cache
   ```

5. Visit `/installer` in your browser and follow the installation wizard

6. Login to the admin dashboard at `/admin`, from the menu go to Settings -> General Settings to configure your store details and other settings

## Laravel Artisan Commands

FreePOS now includes Laravel's powerful artisan CLI:

```bash
# View all available commands
php artisan list

# Cache routes for better performance
php artisan route:cache

# Clear cache
php artisan cache:clear

# View application information
php artisan about
```

## Development

To run the development server:

```bash
php artisan serve
```

Then access the application at `http://localhost:8000`
