<?php

/**
 *  Application Bootstrap
 * 
 * This file sets up the application environment, loads dependencies,
 * and returns an application instance that can handle requests.
 */

// Define application base path
if (!defined('APP_BASE_PATH')) {
    define('APP_BASE_PATH', realpath(__DIR__ . '/..'));
}

// Helper function for base path
if (!function_exists('base_path')) {
    function base_path($path = '')
    {
        return APP_BASE_PATH . ($path ? DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : '');
    }
}

// Helper function for storage path
if (!function_exists('storage_path')) {
    function storage_path($path = '')
    {
        return base_path('storage' . ($path ? DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : ''));
    }
}

// Helper function for resource path
if (!function_exists('resource_path')) {
    function resource_path($path = '')
    {
        return base_path('resources' . ($path ? DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : ''));
    }
}

// Helper function for asset path
if (!function_exists('asset_path')) {
    function asset_path($path = '')
    {
        return base_path('public/assets' . ($path ? DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : ''));
    }
}

if (!function_exists('asset_url')) {
    function asset_url($path = '')
    {
        return '/assets' . ($path ? '/' . ltrim(str_replace('\\', '/', $path), '/') : '');
    }
}

// Helper function for config path
if (!function_exists('config_path')) {
    function config_path($path = '')
    {
        return base_path('config' . ($path ? DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : ''));
    }
}

// Helper function for configuration access
if (!function_exists('config')) {
    function config($key = null, $default = null)
    {
        if (is_null($key)) {
            return App\Core\Config::all();
        }
        return App\Core\Config::get($key, $default);
    }
}

if (!function_exists('env')) {
    function env($key, $default = null)
    {
        $value = $_ENV[$key] ?? $_SERVER[$key];
        if ($value === false) {
            return $default;
        }
        return $value;
    }
}

if (!function_exists('loadJsonFile')) {
    function loadJsonFile($path)
    {
        if (!file_exists($path)) {
            return [];
        }

        $fp = @fopen($path, 'r');
        if (!$fp) {
            return [];
        }

        // Shared lock for reading
        if (flock($fp, LOCK_SH)) {
            $contents = stream_get_contents($fp);
            flock($fp, LOCK_UN);
            fclose($fp);
            $data = json_decode($contents, true);
            return is_array($data) ? $data : [];
        }

        fclose($fp);
        return [];
    }
}

if (!function_exists('saveJsonFile')) {
    function saveJsonFile($path, $data)
    {
        $dir = dirname($path);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        $fp = @fopen($path, 'c+');
        if (!$fp) {
            return false;
        }

        // Exclusive lock for writing
        if (!flock($fp, LOCK_EX)) {
            fclose($fp);
            return false;
        }

        // Truncate and write
        ftruncate($fp, 0);
        rewind($fp);
        $json = json_encode($data, JSON_PRETTY_PRINT);
        fwrite($fp, $json === false ? '' : $json);
        fflush($fp);
        flock($fp, LOCK_UN);
        fclose($fp);
        return true;
    }
}


// Load environment variables
use Dotenv\Dotenv;

if (file_exists(base_path('.env'))) {
    $dotenv = Dotenv::createImmutable(base_path());
    $dotenv->load();
}

// Load configuration
$config = require_once config_path('app.php');

// Store config globally for compatibility
$GLOBALS['app_config'] = $config;

// Return the application instance
return new App\Core\Application();
