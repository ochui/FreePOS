<?php

/**
 * Pusher Communication Provider
 * Provides real-time communication via Pusher service
 */

namespace App\Communication\Providers;

class PusherProvider implements CommunicationProviderInterface
{
    private $pusher = null;
    private $config = [];
    private $channel = 'pos-updates';
    
    public function __construct(array $config = [])
    {
        $this->config = $config;
        
        if ($this->isAvailable()) {
            // Initialize Pusher if credentials are available
            if (class_exists('\Pusher\Pusher')) {
                $this->pusher = new \Pusher\Pusher(
                    $config['pusher_app_key'],
                    $config['pusher_app_secret'],
                    $config['pusher_app_id'],
                    [
                        'cluster' => $config['pusher_app_cluster'] ?? 'us2',
                        'useTLS' => true
                    ]
                );
            }
        }
    }
    
    public function sendSessionData($data, $remove = false)
    {
        if (!$this->pusher) {
            return 'Pusher not configured';
        }
        
        try {
            $this->pusher->trigger($this->channel, 'session-update', [
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
        if (!$this->pusher) {
            return 'Pusher not configured';
        }
        
        try {
            // Send in WebSocket-like format
            $eventData = [
                'a' => $data['a'] ?? 'update',
                'data' => $data['data'] ?? $data,
                'type' => $data['type'] ?? null,
                'include' => $devices  // Device targeting like WebSocket server
            ];
            
            $this->pusher->trigger($this->channel, 'updates', $eventData);
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
        return !empty($this->config['pusher_app_id']) && 
               !empty($this->config['pusher_app_key']) && 
               !empty($this->config['pusher_app_secret']);
    }
    
    public function sendDeviceListUpdate($devices)
    {
        if (!$this->pusher) {
            return 'Pusher not configured';
        }
        
        try {
            // Send device list in WebSocket format
            $eventData = [
                'a' => 'devices',
                'data' => json_encode($devices),
                'include' => null  // Broadcast to all
            ];
            
            $this->pusher->trigger($this->channel, 'updates', $eventData);
            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
    
    public function sendRegreqUpdate()
    {
        if (!$this->pusher) {
            return 'Pusher not configured';
        }
        
        try {
            // Send registration request in WebSocket format
            $eventData = [
                'a' => 'regreq',
                'data' => '',
                'include' => null  // Broadcast to all
            ];
            
            $this->pusher->trigger($this->channel, 'updates', $eventData);
            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
    
    public function getProviderName()
    {
        return 'Pusher';
    }
}