<?php

/**
 * Communication Factory
 * Creates and manages communication providers
 */

namespace App\Communication;

use App\Communication\Providers\CommunicationProviderInterface;
use App\Communication\Providers\SocketIOProvider;
use App\Communication\Providers\PusherProvider;
use App\Communication\Providers\AblyProvider;
use App\Controllers\Admin\AdminSettings;

class CommunicationFactory
{
    private static $instance = null;
    private $provider = null;
    
    /**
     * Get singleton instance
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Get the configured communication provider
     * @return CommunicationProviderInterface
     */
    public function getProvider()
    {
        if ($this->provider === null) {
            $this->provider = $this->createProvider();
        }
        return $this->provider;
    }
    
    /**
     * Create a communication provider based on configuration
     * @return CommunicationProviderInterface
     */
    private function createProvider()
    {
        $config = $this->getConfig();
        $providerName = $config['communication_provider'] ?? 'socketio';
        
        switch ($providerName) {
            case 'pusher':
                return new PusherProvider($config);
                
            case 'ably':
                return new AblyProvider($config);
                
            case 'socketio':
            default:
                return new SocketIOProvider($config);
        }
    }
    
    /**
     * Get configuration from app config and database
     * @return array
     */
    private function getConfig()
    {
        // Load configuration from app.php
        $appConfig = require base_path() . '/config/app.php';
        
        // Try to get additional config from database
        try {
            $dbConfig = AdminSettings::getConfigFileValues(true);
            $config = (array) $dbConfig;
        } catch (\Exception $e) {
            $config = [];
        }
        
        // Merge with app config, prioritizing app config
        return array_merge($config, $appConfig);
    }
    
    /**
     * Get all available providers
     * @return array
     */
    public function getAvailableProviders()
    {
        $config = $this->getConfig();
        $providers = [];
        
        // Socket.IO
        $socketIO = new SocketIOProvider($config);
        $providers['socketio'] = [
            'name' => $socketIO->getProviderName(),
            'available' => true, // Always available as fallback
            'configured' => $socketIO->isAvailable()
        ];
        
        // Pusher
        $pusher = new PusherProvider($config);
        $providers['pusher'] = [
            'name' => $pusher->getProviderName(),
            'available' => class_exists('\Pusher\Pusher'),
            'configured' => $pusher->isAvailable()
        ];
        
        // Ably
        $ably = new AblyProvider($config);
        $providers['ably'] = [
            'name' => $ably->getProviderName(),
            'available' => class_exists('\Ably\AblyRest'),
            'configured' => $ably->isAvailable()
        ];
        
        return $providers;
    }
    
    /**
     * Set the current provider (for testing or runtime switching)
     * @param CommunicationProviderInterface $provider
     */
    public function setProvider(CommunicationProviderInterface $provider)
    {
        $this->provider = $provider;
    }
}