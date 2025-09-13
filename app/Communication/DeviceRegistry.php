<?php

/**
 * Device Registry for tracking connected devices
 * Used by Pusher and Ably providers to simulate Socket.IO device tracking
 */

namespace App\Communication;

use App\Utility\Logger;


class DeviceRegistry
{
    private static $devices = [];
    private static $sessions = [];

    // Files used for persistence (persist across PHP requests)
    private static $devicesFile;
    private static $sessionsFile;
    private static $initialized = false;

    /**
     * Lazy initialize registry by loading persisted files.
     */
    private static function init()
    {
        if (self::$initialized) {
            return;
        }

        // Set file paths at runtime
        self::$devicesFile = storage_path('devices.json');
        self::$sessionsFile = storage_path('sessions.json');

        // Load files if present
        self::$devices = loadJsonFile(self::$devicesFile);
        self::$sessions = loadJsonFile(self::$sessionsFile);

        self::$initialized = true;
    }


    /**
     * Register a device
     * @param int $deviceId Device ID
     * @param string $username Username
     * @param array $metadata Additional device metadata
     */
    public static function registerDevice($deviceId, $username, $metadata = [])
    {
        self::init();
        self::$devices[$deviceId] = [
            'deviceid' => $deviceId,
            'username' => $username,
            'connected_at' => time(),
            'metadata' => $metadata
        ];

        Logger::write("Device registered: ID=$deviceId, Username=$username", "DEVICE");
        // persist
        saveJsonFile(self::$devicesFile, self::$devices);
        return true;
    }

    /**
     * Unregister a device
     * @param int $deviceId Device ID
     */
    public static function unregisterDevice($deviceId)
    {
        self::init();
        if (isset(self::$devices[$deviceId])) {
            unset(self::$devices[$deviceId]);
            Logger::write("Device unregistered: ID=$deviceId", "DEVICE");
            saveJsonFile(self::$devicesFile, self::$devices);
            return true;
        }
        return false;
    }

    /**
     * Get all registered devices
     * @return array
     */
    public static function getDevices()
    {
        self::init();
        return self::$devices;
    }

    /**
     * Get device by ID
     * @param int $deviceId
     * @return array|null
     */
    public static function getDevice($deviceId)
    {
        self::init();
        return self::$devices[$deviceId] ?? null;
    }

    /**
     * Check if device is registered
     * @param int $deviceId
     * @return bool
     */
    public static function isDeviceRegistered($deviceId)
    {
        self::init();
        return isset(self::$devices[$deviceId]);
    }

    /**
     * Add session ID
     * @param string $sessionId
     * @return bool
     */
    public static function addSession($sessionId)
    {
        self::init();
        self::$sessions[$sessionId] = true;
        saveJsonFile(self::$sessionsFile, self::$sessions);
        return true;
    }

    /**
     * Remove session ID
     * @param string $sessionId
     * @return bool
     */
    public static function removeSession($sessionId)
    {
        self::init();
        if (isset(self::$sessions[$sessionId])) {
            unset(self::$sessions[$sessionId]);
            saveJsonFile(self::$sessionsFile, self::$sessions);
            return true;
        }
        return false;
    }

    /**
     * Check if session is valid
     * @param string $sessionId
     * @return bool
     */
    public static function isSessionValid($sessionId)
    {
        self::init();
        return isset(self::$sessions[$sessionId]);
    }

    /**
     * Clear old devices (cleanup)
     * @param int $maxAge Maximum age in seconds
     */
    public static function clearOldDevices($maxAge = 3600)
    {
        self::init();
        $now = time();
        $removed = 0;

        foreach (self::$devices as $deviceId => $device) {
            if (($now - $device['connected_at']) > $maxAge) {
                unset(self::$devices[$deviceId]);
                $removed++;
            }
        }

        if ($removed > 0) {
            Logger::write("Cleaned up $removed old devices", "DEVICE");
            saveJsonFile(self::$devicesFile, self::$devices);
        }

        return $removed;
    }

    /**
     * Get devices formatted for frontend (like Socket.IO server)
     * @return array
     */
    public static function getDevicesFormatted()
    {
        self::init();
        $formatted = [];
        foreach (self::$devices as $deviceId => $device) {
            $formatted[$deviceId] = [
                'socketid' => 'sim-' . $deviceId . '-' . time(), // Simulated socket ID
                'username' => $device['username'],
                'deviceid' => $device['deviceid']
            ];
        }
        return $formatted;
    }
}
