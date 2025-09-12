/**
 * FreePOS Communication Manager
 * Provides unified interface for Socket.IO, Pusher, and Ably
 */
function POSCommunicationManager() {
    var self = this;

    this.provider = null;
    this.providerType = 'socketio';
    this.connected = false;
    this.callbacks = {
        connect: [],
        disconnect: [],
        error: [],
        updates: []
    };

    /**
     * Initialize communication provider based on configuration
     * @param {Object} config - Configuration object
     */
    this.init = function(config) {
        self.providerType = config.provider || 'socketio';

        switch (self.providerType) {
            case 'pusher':
                self.initPusher(config);
                break;
            case 'ably':
                self.initAbly(config);
                break;
            case 'socketio':
            default:
                self.initSocketIO(config);
                break;
        }
    };

    /**
     * Initialize Socket.IO provider
     * @param {Object} config 
     */
    this.initSocketIO = function(config) {
        if (typeof io === 'undefined') {
            var error = 'Socket.IO library not loaded. Make sure to include Socket.IO JavaScript library.';
            console.error(error);
            self.onError(new Error(error));
            return;
        }

        try {
            var socketPath = config.host + ':' + config.port;
            self.provider = io.connect(socketPath);

            // use self for callbacks to avoid losing context
            self.provider.on('connection', function() { self.onConnect(); });
            self.provider.on('reconnect', function() { self.onConnect(); });
            self.provider.on('connect_error', function(error) { 
                console.error('Socket.IO connection error:', error);
                self.onError(error); 
            });
            self.provider.on('reconnect_error', function(error) { 
                console.error('Socket.IO reconnection error:', error);
                self.onError(error); 
            });
            self.provider.on('error', function(error) { 
                console.error('Socket.IO error:', error);
                self.onError(error); 
            });
            self.provider.on('disconnect', function() { self.onDisconnect(); });
            self.provider.on('updates', function(data) { self.onUpdates(data); });
        } catch (error) {
            console.error('Failed to initialize Socket.IO:', error);
            self.onError(error);
        }
    };

    /**
     * Initialize Pusher provider
     * @param {Object} config 
     */
    this.initPusher = function(config) {
        if (typeof Pusher === 'undefined') {
            var error = 'Pusher library not loaded. Make sure to include Pusher JavaScript library.';
            console.error(error);
            self.onError(new Error(error));
            return;
        }

        if (!config.key) {
            var error = 'Pusher API key not configured. Please set pusher_app_key in settings.';
            console.error(error);
            self.onError(new Error(error));
            return;
        }

        try {
            self.provider = new Pusher(config.key, {
                cluster: config.cluster || 'us2'
            });

            var channel = self.provider.subscribe('pos-updates');
            channel.bind('pusher:subscription_succeeded', function() { self.onConnect(); });
            channel.bind('pusher:subscription_error', function(error) { 
                console.error('Pusher subscription error:', error);
                self.onError(error); 
            });
            channel.bind('updates', function(data) { self.onUpdates(data.data); });
            channel.bind('session-update', function(data) { self.handleSessionUpdate(data); });
        } catch (error) {
            console.error('Failed to initialize Pusher:', error);
            self.onError(error);
        }
    };

    /**
     * Initialize Ably provider
     * @param {Object} config 
     */
    this.initAbly = function(config) {
        if (typeof Ably === 'undefined') {
            var error = 'Ably library not loaded. Make sure to include Ably JavaScript library.';
            console.error(error);
            self.onError(new Error(error));
            return;
        }

        if (!config.key) {
            var error = 'Ably API key not configured. Please set ably_api_key in settings.';
            console.error(error);
            self.onError(new Error(error));
            return;
        }

        try {
            self.provider = new Ably.Realtime(config.key);

            self.provider.connection.on('connected', function() { self.onConnect(); });
            self.provider.connection.on('disconnected', function() { self.onDisconnect(); });
            self.provider.connection.on('failed', function(error) { 
                console.error('Ably connection failed:', error);
                self.onError(error); 
            });

            var channel = self.provider.channels.get('pos-updates');
            channel.subscribe('updates', function(message) { self.onUpdates(message.data.data); });
            channel.subscribe('session-update', function(message) { self.handleSessionUpdate(message.data); });
        } catch (error) {
            console.error('Failed to initialize Ably:', error);
            self.onError(error);
        }
    };

    /**
     * Send device registration
     * @param {Object} deviceInfo 
     */
    this.registerDevice = function(deviceInfo) {
        if (self.providerType === 'socketio' && self.provider) {
            self.provider.emit('reg', deviceInfo);
        }
        // For Pusher/Ably, device registration would be handled server-side
    };

    /**
     * Send broadcast message
     * @param {Object} data 
     */
    this.broadcast = function(data) {
        if (self.providerType === 'socketio' && self.provider) {
            self.provider.emit('broadcast', data);
        }
        // For Pusher/Ably, broadcasts would be handled server-side
    };

    /**
     * Disconnect from the communication service
     */
    this.disconnect = function() {
        if (self.provider) {
            switch (self.providerType) {
                case 'pusher':
                    self.provider.disconnect();
                    break;
                case 'ably':
                    self.provider.close();
                    break;
                case 'socketio':
                default:
                    if (typeof self.provider.disconnect === 'function') self.provider.disconnect();
                    break;
            }
            self.provider = null;
        }
        self.connected = false;
    };

    /**
     * Add event callback
     * @param {string} event 
     * @param {function} callback 
     */
    this.on = function(event, callback) {
        if (self.callbacks[event]) {
            self.callbacks[event].push(callback);
        }
    };

    /**
     * Remove event callback
     * @param {string} event 
     * @param {function} callback 
     */
    this.off = function(event, callback) {
        if (self.callbacks[event]) {
            var index = self.callbacks[event].indexOf(callback);
            if (index > -1) {
                self.callbacks[event].splice(index, 1);
            }
        }
    };

    // Event handlers
    this.onConnect = function() {
        self.connected = true;
        for (var i = 0; i < self.callbacks.connect.length; i++) {
            try { self.callbacks.connect[i](); } catch (e) { console.error(e); }
        }
    };

    this.onDisconnect = function() {
        self.connected = false;
        for (var i = 0; i < self.callbacks.disconnect.length; i++) {
            try { self.callbacks.disconnect[i](); } catch (e) { console.error(e); }
        }
    };

    this.onError = function(error) {
        for (var i = 0; i < self.callbacks.error.length; i++) {
            try { self.callbacks.error[i](error); } catch (e) { console.error(e); }
        }
    };

    this.onUpdates = function(data) {
        for (var i = 0; i < self.callbacks.updates.length; i++) {
            try { self.callbacks.updates[i](data); } catch (e) { console.error(e); }
        }
    };

    this.handleSessionUpdate = function(data) {
        // Handle session updates for Pusher/Ably
        console.log('Session update:', data);
    };

    /**
     * Check if connected
     * @returns {boolean}
     */
    this.isConnected = function() {
        return self.connected;
    };

    /**
     * Get current provider type
     * @returns {string}
     */
    this.getProviderType = function() {
        return self.providerType;
    };
}