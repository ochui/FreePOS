<?php

/**
 * Ably Communication Provider
 * Provides real-time communication via Ably service
 */

namespace App\Communication\Providers;

class AblyProvider implements CommunicationProviderInterface
{
    private $ably = null;
    private $config = [];
    private $channel = 'pos-updates';
    
    public function __construct(array $config = [])
    {
        $this->config = $config;
        
        if ($this->isAvailable()) {
            // Initialize Ably if API key is available
            if (class_exists('\Ably\AblyRest')) {
                $this->ably = new \Ably\AblyRest($config['ably_api_key']);
            }
        }
    }
    
    public function sendSessionData($data, $remove = false)
    {
        if (!$this->ably) {
            return 'Ably not configured';
        }
        
        try {
            $channel = $this->ably->channel($this->channel);
            $channel->publish('session-update', [
                'action' => $remove ? 'remove' : 'add',
                'session_id' => $data
            ]);
            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
    
    public function sendDataToDevices($data, $devices = null)
    {
        if (!$this->ably) {
            return 'Ably not configured';
        }
        
        try {
            $channel = $this->ably->channel($this->channel);
            
            // Send in WebSocket-like format
            $eventData = [
                'a' => $data['a'] ?? 'update',
                'data' => $data['data'] ?? $data,
                'include' => $devices  // Device targeting like WebSocket server
            ];
            
            $channel->publish('updates', $eventData);
            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
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
        return !empty($this->config['ably_api_key']);
    }
    
    public function sendDeviceListUpdate($devices)
    {
        if (!$this->ably) {
            return 'Ably not configured';
        }
        
        try {
            $channel = $this->ably->channel($this->channel);
            
            // Send device list in WebSocket format
            $eventData = [
                'a' => 'devices',
                'data' => json_encode($devices),
                'include' => null  // Broadcast to all
            ];
            
            $channel->publish('updates', $eventData);
            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
    
    public function sendRegreqUpdate()
    {
        if (!$this->ably) {
            return 'Ably not configured';
        }
        
        try {
            $channel = $this->ably->channel($this->channel);
            
            // Send registration request in WebSocket format
            $eventData = [
                'a' => 'regreq',
                'data' => '',
                'include' => null  // Broadcast to all
            ];
            
            $channel->publish('updates', $eventData);
            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
    
    public function getProviderName()
    {
        return 'Ably';
    }
}