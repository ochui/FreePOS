<?php

/**
 * 
 * SocketIO is used to send data to the node.js socket.io (websocket) server
 * This class now acts as a compatibility layer for the new unified communication system
 *
 */

namespace App\Communication;

use App\Communication\Communication;
use App\Controllers\Admin\AdminSettings;
use App\Controllers\Admin\AdminUtilities;

class SocketIO
{
    private $communication;

    /**
     * Initialise the communication system
     */
    function __construct()
    {
        $this->communication = new Communication();
    }

    /**
     * Sends session updates to the communication service
     * @param $data
     * @param bool $remove
     * @return bool
     */
    public function sendSessionData($data, $remove = false)
    {
        return $this->communication->sendSessionData($data, $remove);
    }

    /**
     * Generate a random hashkey for php -> node.js authentication
     * @return bool
     */
    public function generateHashKey()
    {
        return $this->communication->generateHashKey();
    }

    /**
     * Send a reset request to all pos devices or the device specified
     * @param null $devices
     * @return bool
     */
    public function sendResetCommand($devices = null)
    {
        return $this->communication->sendResetCommand($devices);
    }

    /**
     * Send a message to the specified devices, if no devices specified then all receive it. Admin dash excluded
     * @param $devices
     * @param $message
     * @return bool
     */
    public function sendMessageToDevices($devices, $message)
    {
        return $this->communication->sendMessageToDevices($devices, $message);
    }

    /**
     * Broadcast a stored item addition/update/delete to all connected devices.
     * @param $item
     * @return bool
     */
    public function sendItemUpdate($item)
    {
        return $this->communication->sendItemUpdate($item);
    }

    /**
     * Broadcast a customer addition/update/delete to all connected devices.
     * @param $customer
     * @return bool
     */
    public function sendCustomerUpdate($customer)
    {
        return $this->communication->sendCustomerUpdate($customer);
    }

    /**
     * Send a sale update to the specified devices, if no devices specified, all receive.
     * @param $sale
     * @param null $devices
     * @return bool
     */
    public function sendSaleUpdate($sale, $devices = null)
    {
        return $this->communication->sendSaleUpdate($sale, $devices);
    }

    /**
     * Broadcast a configuration update to all connected devices.
     * @param $newconfig
     * @param $configset; the set name for the values
     * @return bool
     */
    public function sendConfigUpdate($newconfig, $configset)
    {
        return $this->communication->sendConfigUpdate($newconfig, $configset);
    }

    /**
     * Send updated device specific config
     * @param $newconfig
     * @return bool
     */
    public function sendDeviceConfigUpdate($newconfig)
    {
        return $this->communication->sendDeviceConfigUpdate($newconfig);
    }
}
