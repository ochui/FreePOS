/**
 * FreePOS Communication Manager
 * Provides unified interface for Socket.IO, Pusher, and Ably
 */
function POSCommunicationManager() {
    var self = this;

    this.provider = null;
    this.providerType = 'socketio';
    this.connected = false;
    this.deviceId = null;
    this.username = null;
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
            console.log('Connecting to Socket.IO server:', socketPath);
            self.provider = io.connect(socketPath);

            // use self for callbacks to avoid losing context
            self.provider.on('connect', function() { 
                console.log('Socket.IO connected successfully');
                self.onConnect(); 
            });
            self.provider.on('reconnect', function() { 
                console.log('Socket.IO reconnected');
                self.onConnect(); 
            });
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
            self.provider.on('disconnect', function() { 
                console.log('Socket.IO disconnected');
                self.onDisconnect(); 
            });
            self.provider.on('updates', function(data) { 
                console.log('Socket.IO received update:', data);
                self.onUpdates(data); 
            });
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
            channel.bind('pusher:subscription_succeeded', function() { 
                console.log('Pusher channel subscribed successfully');
                self.onConnect();
            });
            channel.bind('pusher:subscription_error', function(error) { 
                console.error('Pusher subscription error:', error);
                self.onError(error); 
            });
            
            // Handle all WebSocket-like events
            channel.bind('updates', function(data) { 
                console.log('Pusher received update:', data);
                self.handleIncomingMessage(data);
            });
            
            // Handle specific events that might come directly 
            channel.bind('reg', function(data) { self.handleIncomingMessage({a: 'reg', data: data}); });
            channel.bind('send', function(data) { self.handleIncomingMessage({a: 'send', data: data}); });
            channel.bind('broadcast', function(data) { self.handleIncomingMessage({a: 'broadcast', data: data}); });
            channel.bind('session', function(data) { self.handleIncomingMessage({a: 'session', data: data}); });
            channel.bind('regreq', function(data) { self.handleIncomingMessage({a: 'regreq', data: data}); });
            
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

            self.provider.connection.on('connected', function() { 
                console.log('Ably connected successfully');
                self.onConnect();
            });
            self.provider.connection.on('disconnected', function() { self.onDisconnect(); });
            self.provider.connection.on('failed', function(error) { 
                console.error('Ably connection failed:', error);
                self.onError(error); 
            });

            var channel = self.provider.channels.get('pos-updates');
            
            // Handle all WebSocket-like events
            channel.subscribe('updates', function(message) { 
                console.log('Ably received update:', message);
                self.handleIncomingMessage(message.data);
            });
            
            // Handle specific events that might come directly
            channel.subscribe('reg', function(message) { self.handleIncomingMessage({a: 'reg', data: message.data}); });
            channel.subscribe('send', function(message) { self.handleIncomingMessage({a: 'send', data: message.data}); });
            channel.subscribe('broadcast', function(message) { self.handleIncomingMessage({a: 'broadcast', data: message.data}); });
            channel.subscribe('session', function(message) { self.handleIncomingMessage({a: 'session', data: message.data}); });
            channel.subscribe('regreq', function(message) { self.handleIncomingMessage({a: 'regreq', data: message.data}); });
            
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
        self.deviceId = deviceInfo.deviceid;
        self.username = deviceInfo.username;
        
        if (self.providerType === 'socketio' && self.provider) {
            self.provider.emit('reg', deviceInfo);
        } else {
            // For Pusher/Ably, register device via API
            console.log('Registering device via API for ' + self.providerType + ':', deviceInfo);
            
            // Use POS.sendJsonDataAsync if available, otherwise use fetch
            if (typeof POS !== 'undefined' && POS.sendJsonDataAsync) {
                POS.sendJsonDataAsync("devices/register", JSON.stringify(deviceInfo), function(result) {
                    if (result !== false) {
                        console.log('Device registration successful:', result);
                    } else {
                        console.warn('Device registration failed');
                    }
                });
            } else {
                // Fallback for environments where POS object is not available
                fetch('/api/devices/register', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(deviceInfo)
                }).then(response => response.json())
                  .then(data => console.log('Device registration result:', data))
                  .catch(error => console.error('Device registration error:', error));
            }
        }
    };

    /**
     * Send broadcast message
     * @param {Object} data 
     */
    this.broadcast = function(data) {
        if (self.providerType === 'socketio' && self.provider) {
            self.provider.emit('broadcast', data);
        }
        // For Pusher/Ably, broadcasts would be handled server-side via API calls
    };

    /**
     * Handle incoming messages with WebSocket-like filtering
     * @param {Object} data 
     */
    this.handleIncomingMessage = function(data) {
        console.log('Processing incoming message:', data);
        
        // Handle the data structure - it might be nested
        var messageData = data;
        if (data && data.data && data.data.data) {
            messageData = data.data;
        } else if (data && data.data) {
            messageData = data.data;
        }
        
        // For Pusher/Ably, check if message is targeted to this device
        if (self.providerType !== 'socketio' && messageData && messageData.include) {
            // Check if this device should receive the message
            var shouldReceive = false;
            
            if (messageData.include === null || messageData.include === undefined) {
                // Broadcast to all devices
                shouldReceive = true;
            } else if (typeof messageData.include === 'object') {
                // Check if our device ID is in the include list
                shouldReceive = messageData.include.hasOwnProperty(self.deviceId);
            }
            
            if (!shouldReceive) {
                console.log('Message not targeted for this device (ID: ' + self.deviceId + ')');
                return;
            }
        }
        
        // Extract the actual message content
        var finalData;
        if (messageData && messageData.data && messageData.data.a) {
            finalData = messageData.data;
        } else if (messageData && messageData.a) {
            finalData = messageData;
        } else {
            finalData = data;
        }
        
        console.log('Final processed data:', finalData);
        self.onUpdates(finalData);
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
        
        // For Pusher/Ably, trigger registration request after connection
        if (self.providerType !== 'socketio') {
            setTimeout(function() {
                console.log('Triggering registration request for ' + self.providerType);
                self.onUpdates({ a: "regreq", data: "" });
            }, 100);
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