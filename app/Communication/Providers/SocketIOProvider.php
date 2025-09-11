<?php

/**
 * Socket.IO Communication Provider
 * Wraps the existing SocketIO functionality
 */

namespace App\Communication\Providers;

use ElephantIO\Client as Client;
use ElephantIO\Engine\SocketIO\Version4X as Version4X;
use App\Controllers\Admin\AdminSettings;

class SocketIOProvider implements CommunicationProviderInterface
{
    private $elephant = null;
    private $hashkey = "supersecretkey";
    private $config = [];
    
    public function __construct(array $config = [])
    {
        $this->config = $config;
        
        // Try to get config from AdminSettings if available
        try {
            $conf = AdminSettings::getConfigFileValues(true);
            if (isset($conf->feedserver_key)) {
                $this->hashkey = $conf->feedserver_key;
            }
            $host = $conf->feedserver_host ?? ($config['feedserver_host'] ?? '127.0.0.1');
            $port = $conf->feedserver_port ?? ($config['feedserver_port'] ?? 3000);
        } catch (\Exception | \Error $e) {
            // Use config array if AdminSettings not available
            $this->hashkey = $config['feedserver_key'] ?? 'supersecretkey';
            $host = $config['feedserver_host'] ?? '127.0.0.1';
            $port = $config['feedserver_port'] ?? 3000;
        }
        
        $this->elephant = new Client(new Version4X($host . ':' . $port . '/?hashkey=' . $this->hashkey));
    }
    
    public function sendSessionData($data, $remove = false)
    {
        return $this->sendData('session', ['hashkey' => $this->hashkey, 'data' => $data, 'remove' => $remove]);
    }
    
    public function sendDataToDevices($data, $devices = null)
    {
        return $this->sendData('send', ['hashkey' => $this->hashkey, 'include' => $devices, 'data' => $data]);
    }
    
    public function sendItemUpdate($item)
    {
        return $this->sendDataToDevices(['a' => 'item', 'data' => $item], null);
    }
    
    public function sendCustomerUpdate($customer)
    {
        return $this->sendDataToDevices(['a' => 'customer', 'data' => $customer], null);
    }
    
    public function sendSaleUpdate($sale, $devices = null)
    {
        return $this->sendDataToDevices(['a' => 'sale', 'data' => $sale], $devices);
    }
    
    public function sendConfigUpdate($config, $type)
    {
        return $this->sendDataToDevices(['a' => 'config', 'data' => $config, 'type' => $type], null);
    }
    
    public function sendResetCommand($devices = null)
    {
        return $this->sendDataToDevices(['a' => 'reset'], $devices);
    }
    
    public function isAvailable()
    {
        return !empty($this->config['feedserver_host']) && !empty($this->config['feedserver_port']);
    }
    
    public function getProviderName()
    {
        return 'Socket.IO';
    }
    
    /**
     * Send data to the node.js socket server
     * @param string $event Event name
     * @param array $data Data to send
     * @return bool|string Success status or error message
     */
    private function sendData($event, $data)
    {
        set_error_handler(function () { /* ignore warnings */ }, E_WARNING);
        try {
            $this->elephant->connect();
            $this->elephant->emit($event, $data);
        } catch (\Exception $e) {
            restore_error_handler();
            return $e->getMessage();
        }
        restore_error_handler();
        return true;
    }
}