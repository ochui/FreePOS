<?php

/**
 * Communication Provider Interface
 * Defines the contract for all real-time communication providers
 */

namespace App\Communication\Providers;

interface CommunicationProviderInterface
{
    /**
     * Initialize the communication provider
     * @param array $config Configuration options
     */
    public function __construct(array $config = []);
    
    /**
     * Send session data to the communication service
     * @param string $data Session data
     * @param bool $remove Whether to remove the session
     * @return bool|string Success status or error message
     */
    public function sendSessionData($data, $remove = false);
    
    /**
     * Send data to specific devices
     * @param array $data Data to send
     * @param array|null $devices Target devices (null for all)
     * @return bool|string Success status or error message
     */
    public function sendDataToDevices($data, $devices = null);
    
    /**
     * Send item update
     * @param mixed $item Item data
     * @return bool|string Success status or error message
     */
    public function sendItemUpdate($item);
    
    /**
     * Send customer update
     * @param mixed $customer Customer data
     * @return bool|string Success status or error message
     */
    public function sendCustomerUpdate($customer);
    
    /**
     * Send sale update
     * @param mixed $sale Sale data
     * @param array|null $devices Target devices
     * @return bool|string Success status or error message
     */
    public function sendSaleUpdate($sale, $devices = null);
    
    /**
     * Send configuration update
     * @param mixed $config Configuration data
     * @param string $type Configuration type
     * @return bool|string Success status or error message
     */
    public function sendConfigUpdate($config, $type);
    
    /**
     * Send reset command
     * @param array|null $devices Target devices
     * @return bool|string Success status or error message
     */
    public function sendResetCommand($devices = null);
    
    /**
     * Check if the provider is available/configured
     * @return bool
     */
    public function isAvailable();
    
    /**
     * Get provider name
     * @return string
     */
    public function getProviderName();
}