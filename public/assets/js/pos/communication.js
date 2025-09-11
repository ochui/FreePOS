/**
 * FreePOS Communication Manager
 * Provides unified interface for Socket.IO, Pusher, and Ably
 */
class CommunicationManager {
    constructor() {
        this.provider = null;
        this.providerType = 'socketio';
        this.connected = false;
        this.callbacks = {
            connect: [],
            disconnect: [],
            error: [],
            updates: []
        };
    }

    /**
     * Initialize communication provider based on configuration
     * @param {Object} config - Configuration object
     */
    init(config) {
        this.providerType = config.provider || 'socketio';
        
        switch (this.providerType) {
            case 'pusher':
                this.initPusher(config);
                break;
            case 'ably':
                this.initAbly(config);
                break;
            case 'socketio':
            default:
                this.initSocketIO(config);
                break;
        }
    }

    /**
     * Initialize Socket.IO provider
     * @param {Object} config 
     */
    initSocketIO(config) {
        if (typeof io === 'undefined') {
            console.error('Socket.IO library not loaded');
            return;
        }

        const socketPath = config.host + ':' + config.port;
        this.provider = io.connect(socketPath);
        
        this.provider.on('connection', () => this.onConnect());
        this.provider.on('reconnect', () => this.onConnect());
        this.provider.on('connect_error', (error) => this.onError(error));
        this.provider.on('reconnect_error', (error) => this.onError(error));
        this.provider.on('error', (error) => this.onError(error));
        this.provider.on('disconnect', () => this.onDisconnect());
        this.provider.on('updates', (data) => this.onUpdates(data));
    }

    /**
     * Initialize Pusher provider
     * @param {Object} config 
     */
    initPusher(config) {
        if (typeof Pusher === 'undefined') {
            console.error('Pusher library not loaded');
            return;
        }

        this.provider = new Pusher(config.key, {
            cluster: config.cluster || 'us2'
        });

        const channel = this.provider.subscribe('pos-updates');
        channel.bind('pusher:subscription_succeeded', () => this.onConnect());
        channel.bind('pusher:subscription_error', (error) => this.onError(error));
        channel.bind('updates', (data) => this.onUpdates(data.data));
        channel.bind('session-update', (data) => this.handleSessionUpdate(data));
    }

    /**
     * Initialize Ably provider
     * @param {Object} config 
     */
    initAbly(config) {
        if (typeof Ably === 'undefined') {
            console.error('Ably library not loaded');
            return;
        }

        this.provider = new Ably.Realtime(config.key);
        
        this.provider.connection.on('connected', () => this.onConnect());
        this.provider.connection.on('disconnected', () => this.onDisconnect());
        this.provider.connection.on('failed', (error) => this.onError(error));

        const channel = this.provider.channels.get('pos-updates');
        channel.subscribe('updates', (message) => this.onUpdates(message.data.data));
        channel.subscribe('session-update', (message) => this.handleSessionUpdate(message.data));
    }

    /**
     * Send device registration
     * @param {Object} deviceInfo 
     */
    registerDevice(deviceInfo) {
        if (this.providerType === 'socketio' && this.provider) {
            this.provider.emit('reg', deviceInfo);
        }
        // For Pusher/Ably, device registration would be handled server-side
    }

    /**
     * Send broadcast message
     * @param {Object} data 
     */
    broadcast(data) {
        if (this.providerType === 'socketio' && this.provider) {
            this.provider.emit('broadcast', data);
        }
        // For Pusher/Ably, broadcasts would be handled server-side
    }

    /**
     * Disconnect from the communication service
     */
    disconnect() {
        if (this.provider) {
            switch (this.providerType) {
                case 'pusher':
                    this.provider.disconnect();
                    break;
                case 'ably':
                    this.provider.close();
                    break;
                case 'socketio':
                default:
                    this.provider.disconnect();
                    break;
            }
            this.provider = null;
        }
        this.connected = false;
    }

    /**
     * Add event callback
     * @param {string} event 
     * @param {function} callback 
     */
    on(event, callback) {
        if (this.callbacks[event]) {
            this.callbacks[event].push(callback);
        }
    }

    /**
     * Remove event callback
     * @param {string} event 
     * @param {function} callback 
     */
    off(event, callback) {
        if (this.callbacks[event]) {
            const index = this.callbacks[event].indexOf(callback);
            if (index > -1) {
                this.callbacks[event].splice(index, 1);
            }
        }
    }

    // Event handlers
    onConnect() {
        this.connected = true;
        this.callbacks.connect.forEach(cb => cb());
    }

    onDisconnect() {
        this.connected = false;
        this.callbacks.disconnect.forEach(cb => cb());
    }

    onError(error) {
        this.callbacks.error.forEach(cb => cb(error));
    }

    onUpdates(data) {
        this.callbacks.updates.forEach(cb => cb(data));
    }

    handleSessionUpdate(data) {
        // Handle session updates for Pusher/Ably
        console.log('Session update:', data);
    }

    /**
     * Check if connected
     * @returns {boolean}
     */
    isConnected() {
        return this.connected;
    }

    /**
     * Get current provider type
     * @returns {string}
     */
    getProviderType() {
        return this.providerType;
    }
}