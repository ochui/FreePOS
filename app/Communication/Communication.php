<?php

/**
 * Unified Communication Manager
 * Provides a unified interface for all communication providers while maintaining backward compatibility
 */

namespace App\Communication;

use App\Communication\CommunicationFactory;
use App\Controllers\Admin\AdminSettings;
use App\Controllers\Admin\AdminUtilities;

class Communication
{
    private $provider;
    
    public function __construct()
    {
        $factory = CommunicationFactory::getInstance();
        $this->provider = $factory->getProvider();
    }
    
    /**
     * Send session data
     * @param string $data
     * @param bool $remove
     * @return bool|string
     */
    public function sendSessionData($data, $remove = false)
    {
        return $this->provider->sendSessionData($data, $remove);
    }
    
    /**
     * Generate a random hashkey for php -> node.js authentication (Socket.IO only)
     * @return bool
     */
    public function generateHashKey()
    {
        if ($this->provider instanceof \App\Communication\Providers\SocketIOProvider) {
            $key = hash('sha256', AdminUtilities::getToken(256));
            AdminSettings::setConfigFileValue('feedserver_key', $key);
            
            $socket = new SocketControl();
            if ($socket->isServerRunning()) {
                // Note: This would need to be updated for the new provider to handle hashkey updates
                // For now, keeping Socket.IO specific functionality
            }
        }
        return true;
    }
    
    /**
     * Send a reset request to all pos devices or the device specified
     * @param null $devices
     * @return bool|string
     */
    public function sendResetCommand($devices = null)
    {
        return $this->provider->sendResetCommand($devices);
    }
    
    /**
     * Send a message to the specified devices, if no devices specified then all receive it.
     * @param $devices
     * @param $message
     * @return bool|string
     */
    public function sendMessageToDevices($devices, $message)
    {
        return $this->provider->sendDataToDevices(['a' => 'msg', 'data' => $message], $devices);
    }
    
    /**
     * Broadcast a stored item addition/update/delete to all connected devices.
     * @param $item
     * @return bool|string
     */
    public function sendItemUpdate($item)
    {
        return $this->provider->sendItemUpdate($item);
    }
    
    /**
     * Broadcast a customer addition/update/delete to all connected devices.
     * @param $customer
     * @return bool|string
     */
    public function sendCustomerUpdate($customer)
    {
        return $this->provider->sendCustomerUpdate($customer);
    }
    
    /**
     * Send a sale update to the specified devices, if no devices specified, all receive.
     * @param $sale
     * @param null $devices
     * @return bool|string
     */
    public function sendSaleUpdate($sale, $devices = null)
    {
        return $this->provider->sendSaleUpdate($sale, $devices);
    }
    
    /**
     * Broadcast a configuration update to all connected devices.
     * @param $newconfig
     * @param $configset the set name for the values
     * @return bool|string
     */
    public function sendConfigUpdate($newconfig, $configset)
    {
        return $this->provider->sendConfigUpdate($newconfig, $configset);
    }
    
    /**
     * Send updated device specific config
     * @param $newconfig
     * @return bool|string
     */
    public function sendDeviceConfigUpdate($newconfig)
    {
        return $this->provider->sendConfigUpdate($newconfig, 'deviceconfig');
    }
    
    /**
     * Get current provider information
     * @return array
     */
    public function getProviderInfo()
    {
        return [
            'name' => $this->provider->getProviderName(),
            'available' => $this->provider->isAvailable()
        ];
    }
    
    /**
     * Get all available providers
     * @return array
     */
    public function getAvailableProviders()
    {
        $factory = CommunicationFactory::getInstance();
        return $factory->getAvailableProviders();
    }
}