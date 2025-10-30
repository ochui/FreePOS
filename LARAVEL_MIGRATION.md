# Laravel Migration Documentation

## Overview

FreePOS has been successfully migrated from a custom PHP framework to Laravel 11. This document details the changes made and provides guidance for developers and administrators.

## What Changed

### Framework
- **Before**: Custom PHP framework with FastRoute for routing
- **After**: Laravel 11 Framework

### Key Improvements
1. **Modern Architecture**: Laravel provides a robust, well-tested foundation
2. **Developer Experience**: Access to Laravel's extensive documentation and community
3. **Tooling**: Artisan CLI for common tasks and code generation
4. **Security**: Laravel's built-in security features and regular updates
5. **Ecosystem**: Access to thousands of Laravel packages
6. **Maintainability**: Standardized structure and best practices

## Technical Changes

### Directory Structure

New Laravel directories added:
```
routes/              # Route definitions (api.php, web.php, console.php)
storage/
  ├── framework/     # Laravel framework storage
  │   ├── cache/
  │   ├── sessions/
  │   └── views/
  └── logs/          # Application logs
bootstrap/
  ├── cache/         # Framework bootstrap cache
  └── helpers.php    # Custom helper functions
app/
  ├── Http/          # HTTP layer
  │   └── Controllers/
  └── Providers/     # Service providers
config/              # Enhanced configuration files
```

### Configuration Files

New Laravel configuration files:
- `config/app.php` - Application configuration (Laravel format)
- `config/database.php` - Database connections
- `config/cache.php` - Cache configuration
- `config/session.php` - Session configuration
- `config/logging.php` - Logging configuration
- `config/filesystems.php` - Filesystem configuration

### Routing

**Before**: Routes defined in `app/Core/Application.php` using FastRoute
**After**: Routes defined in `routes/api.php` using Laravel routing

All 240+ API routes have been converted to Laravel route definitions.

### Entry Points

**Before**: `public/api/index.php` bootstrapped custom Application class
**After**: 
- `public/index.php` - Main Laravel entry point
- `public/api/index.php` - Updated to use Laravel kernel for backward compatibility

### Controllers

Controllers remain in `app/Controllers/*` but can now optionally extend Laravel's base controller for additional features.

### Environment Configuration

Enhanced `.env` file with Laravel-specific variables:
- `APP_KEY` - Application encryption key (generate with `php artisan key:generate`)
- `APP_ENV` - Environment (local, production, etc.)
- `APP_DEBUG` - Debug mode toggle
- `SESSION_DRIVER` - Session storage driver
- `CACHE_DRIVER` - Cache storage driver
- And more...

### Helper Functions

Custom helper functions preserved in `bootstrap/helpers.php`:
- `asset_path()`
- `asset_url()`
- `loadJsonFile()`
- `saveJsonFile()`

Laravel's built-in helpers are also available:
- `config()` - Access configuration
- `env()` - Access environment variables
- `base_path()` - Get base path
- `storage_path()` - Get storage path
- `resource_path()` - Get resources path
- And many more...

## Backward Compatibility

### Preserved Features
✅ All existing API routes work identically
✅ Database structure unchanged
✅ Custom helper functions maintained
✅ Configuration values preserved
✅ Authentication system intact
✅ All controllers maintain existing signatures

### What's Fully Compatible
- All API endpoints
- Database queries
- Authentication flows
- File storage
- Custom business logic
- Third-party integrations (Pusher, Ably, Socket.io)

## For Developers

### Getting Started

1. **Fresh Installation**:
   ```bash
   composer install
   cp .env.example .env
   php artisan key:generate
   chmod -R 775 storage bootstrap/cache
   ```

2. **Existing Installation**:
   ```bash
   git pull
   composer install
   php artisan key:generate
   php artisan route:cache
   ```

### Common Artisan Commands

```bash
# Development server
php artisan serve

# View all routes
php artisan route:list

# Cache routes for production
php artisan route:cache

# Clear various caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# View application info
php artisan about

# Generate application key
php artisan key:generate
```

### Adding New Routes

Edit `routes/api.php`:
```php
Route::match(['GET', 'POST'], '/my-endpoint', [MyController::class, 'myMethod']);
```

### Creating New Controllers

```bash
php artisan make:controller MyController
```

### Accessing Laravel Features

Controllers can now use Laravel features:
```php
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

// In your controller methods:
$results = DB::select('SELECT * FROM users');
Cache::put('key', 'value', 3600);
Log::info('User logged in', ['user_id' => $userId]);
```

## For System Administrators

### Deployment

1. **Install dependencies**:
   ```bash
   composer install --no-dev --optimize-autoloader
   ```

2. **Configure environment**:
   ```bash
   cp .env.example .env
   # Edit .env with your settings
   php artisan key:generate
   ```

3. **Optimize for production**:
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

4. **Set permissions**:
   ```bash
   chmod -R 775 storage bootstrap/cache
   chown -R www-data:www-data storage bootstrap/cache
   ```

### Web Server Configuration

The `public/` directory should be your document root. Laravel includes `.htaccess` for Apache and works with Nginx using standard PHP-FPM configuration.

**Apache**: Ensure `mod_rewrite` is enabled
**Nginx**: Use standard Laravel Nginx configuration

### Performance

Laravel includes several optimization options:
- Route caching (`php artisan route:cache`)
- Config caching (`php artisan config:cache`)
- View caching (`php artisan view:cache`)
- OPcache (enable in PHP)

### Monitoring & Logs

Logs are now stored in `storage/logs/laravel.log` using Laravel's logging system.

Configure logging in `config/logging.php` or via environment variables:
```env
LOG_CHANNEL=daily
LOG_LEVEL=info
```

## Troubleshooting

### Common Issues

1. **"The stream or file could not be opened"**
   - Fix: `chmod -R 775 storage bootstrap/cache`

2. **"No application encryption key has been specified"**
   - Fix: `php artisan key:generate`

3. **Routes not working**
   - Fix: `php artisan route:clear && php artisan route:cache`

4. **Configuration changes not reflecting**
   - Fix: `php artisan config:clear`

### Getting Help

- Laravel Documentation: https://laravel.com/docs/11.x
- Laravel Community: https://laracasts.com/discuss
- FreePOS Issues: https://github.com/ochui/FreePOS/issues

## Testing

The migration has been designed to maintain 100% backward compatibility with existing functionality. All API endpoints, authentication flows, and business logic remain unchanged.

### What to Test

1. ✅ Installation wizard
2. ✅ User authentication
3. ✅ POS operations (sales, refunds, voids)
4. ✅ Admin dashboard
5. ✅ Reports generation
6. ✅ Stock management
7. ✅ Customer management
8. ✅ Settings configuration
9. ✅ Real-time features (if enabled)
10. ✅ Receipt printing

## Future Enhancements

With Laravel now as the foundation, future enhancements can include:
- Laravel Sanctum for API authentication
- Laravel Queue for background jobs
- Laravel Broadcasting for real-time events
- Laravel Horizon for queue monitoring
- Laravel Telescope for debugging
- Database migrations for easier updates
- PHPUnit tests for better quality assurance

## Migration Credits

This migration maintains all existing functionality while providing a modern foundation for future development. The migration was performed with minimal changes to preserve stability while gaining Laravel's benefits.

**Laravel Version**: 11.46.1
**Migration Date**: October 2025
**PHP Version Required**: 8.0+
