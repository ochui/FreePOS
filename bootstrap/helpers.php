<?php

/**
 * Custom Helper Functions
 * 
 * This file contains custom helper functions used throughout the application
 * that are not provided by Laravel by default.
 */

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
