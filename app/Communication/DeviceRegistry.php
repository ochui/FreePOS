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
    
    /**
     * Register a device
     * @param int $deviceId Device ID
     * @param string $username Username
     * @param array $metadata Additional device metadata
     */
    public static function registerDevice($deviceId, $username, $metadata = [])
    {
        self::$devices[$deviceId] = [
            'deviceid' => $deviceId,
            'username' => $username,
            'connected_at' => time(),
            'metadata' => $metadata
        ];

        Logger::write("Device registered: ID=$deviceId, Username=$username", "DEVICE");
        return true;
    }
    
    /**
     * Unregister a device
     * @param int $deviceId Device ID
     */
    public static function unregisterDevice($deviceId)
    {
        if (isset(self::$devices[$deviceId])) {
            unset(self::$devices[$deviceId]);
            Logger::write("Device unregistered: ID=$deviceId", "DEVICE");
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
        return self::$devices;
    }
    
    /**
     * Get device by ID
     * @param int $deviceId
     * @return array|null
     */
    public static function getDevice($deviceId)
    {
        return self::$devices[$deviceId] ?? null;
    }
    
    /**
     * Check if device is registered
     * @param int $deviceId
     * @return bool
     */
    public static function isDeviceRegistered($deviceId)
    {
        return isset(self::$devices[$deviceId]);
    }
    
    /**
     * Add session ID
     * @param string $sessionId
     * @return bool
     */
    public static function addSession($sessionId)
    {
        self::$sessions[$sessionId] = true;
        return true;
    }
    
    /**
     * Remove session ID
     * @param string $sessionId
     * @return bool
     */
    public static function removeSession($sessionId)
    {
        if (isset(self::$sessions[$sessionId])) {
            unset(self::$sessions[$sessionId]);
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
        return isset(self::$sessions[$sessionId]);
    }
    
    /**
     * Clear old devices (cleanup)
     * @param int $maxAge Maximum age in seconds
     */
    public static function clearOldDevices($maxAge = 3600)
    {
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
        }
        
        return $removed;
    }
    
    /**
     * Get devices formatted for frontend (like Socket.IO server)
     * @return array
     */
    public static function getDevicesFormatted()
    {
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