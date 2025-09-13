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
use App\Database\ConfigModel;
use App\Utility\Logger;

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
        $providerName = $config['communication_provider'] ?? 'pusher';

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
        $confMdl = new ConfigModel();
        $conf = $confMdl->get('general');
        if (empty($conf)) {
            return [];
        }
        return json_decode($conf[0]['data'], true);
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
        try {
            $socketIO = new SocketIOProvider($config);
            $providers['socketio'] = [
                'name' => $socketIO->getProviderName(),
                'available' => true, // Always available as fallback
                'configured' => $socketIO->isAvailable()
            ];
        } catch (\Exception $e) {
            $providers['socketio'] = [
                'name' => 'Socket.IO',
                'available' => true,
                'configured' => false
            ];
        }

        // Pusher
        try {
            $pusher = new PusherProvider($config);
            $providers['pusher'] = [
                'name' => $pusher->getProviderName(),
                'available' => class_exists('\Pusher\Pusher'),
                'configured' => $pusher->isAvailable()
            ];
        } catch (\Exception $e) {
            $providers['pusher'] = [
                'name' => 'Pusher',
                'available' => class_exists('\Pusher\Pusher'),
                'configured' => false
            ];
        }

        // Ably
        try {
            $ably = new AblyProvider($config);
            $providers['ably'] = [
                'name' => $ably->getProviderName(),
                'available' => class_exists('\Ably\AblyRest'),
                'configured' => $ably->isAvailable()
            ];
        } catch (\Exception $e) {
            $providers['ably'] = [
                'name' => 'Ably',
                'available' => class_exists('\Ably\AblyRest'),
                'configured' => false
            ];
        }

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
