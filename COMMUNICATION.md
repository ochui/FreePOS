# FreePOS Communication Providers

FreePOS now supports multiple real-time communication providers for real-time updates across devices:

- **Socket.IO** (Default) - Self-hosted WebSocket server
- **Pusher** - Cloud-hosted service  
- **Ably** - Enterprise-grade service

## Quick Setup

### Socket.IO (Default/Self-hosted)
1. Navigate to the `api/` directory
2. Install dependencies: `npm install`
3. Start the server: `npm start` (or `node server.js`)
4. Configure in Admin → General Settings → Communication:
   - Set Host: `127.0.0.1` (or your server IP)
   - Set Port: `3000` (or your preferred port)
   - Enable proxy if using reverse proxy

### Pusher (Cloud Service)
1. Sign up at [pusher.com](https://pusher.com)
2. Create a new app in your Pusher dashboard
3. Get your App ID, Key, Secret, and Cluster
4. Configure in Admin → General Settings → Communication:
   - Select "Pusher" as provider
   - Enter your credentials

### Ably (Enterprise Service)  
1. Sign up at [ably.com](https://ably.com)
2. Create a new app in your Ably dashboard
3. Get your API key
4. Configure in Admin → General Settings → Communication:
   - Select "Ably" as provider
   - Enter your API key

## Environment Configuration

You can also configure providers via environment variables in `.env`:

```bash
# Communication Provider: socketio, pusher, ably
COMMUNICATION_PROVIDER=socketio

# Socket.IO Configuration
FEED_SERVER_HOST=127.0.0.1
FEED_SERVER_PORT=3000
FEED_SERVER_KEY=your-secret-key

# Pusher Configuration
PUSHER_APP_ID=your-app-id
PUSHER_APP_KEY=your-app-key
PUSHER_APP_SECRET=your-app-secret
PUSHER_APP_CLUSTER=us2

# Ably Configuration
ABLY_API_KEY=your-api-key
```

## Architecture

The communication system uses a provider factory pattern:

- `CommunicationFactory` - Creates appropriate provider instances
- `Communication` - Unified interface maintaining backward compatibility
- `CommunicationProviderInterface` - Contract for all providers
- Specific providers: `SocketIOProvider`, `PusherProvider`, `AblyProvider`

Frontend uses `CommunicationManager` class for unified client-side communication.

## Migration from Legacy

Existing installations will continue to work without changes. The new system maintains full backward compatibility with the existing `SocketIO` class.

## Troubleshooting

### Socket.IO Issues
- Ensure Node.js server is running on configured port
- Check firewall settings
- Verify server.js has correct environment variables

### Pusher Issues
- Verify credentials are correct
- Check your Pusher dashboard for connection logs
- Ensure your plan supports the number of connections

### Ably Issues  
- Verify API key is correct
- Check your Ably dashboard for connection logs
- Ensure your account has sufficient quota

### General Issues
- Check browser console for JavaScript errors
- Verify the communication provider is set correctly in settings
- Test with Socket.IO first as it's the most straightforward

## Development

To add a new communication provider:

1. Implement `CommunicationProviderInterface`
2. Add provider to `CommunicationFactory`
3. Update frontend `CommunicationManager` if needed
4. Add configuration options to admin interface

## Dependencies

### PHP Dependencies
- `pusher/pusher-php-server:^7.2` - Pusher PHP SDK
- `ably/ably-php:^1.1` - Ably PHP SDK

### Node.js Dependencies  
- `socket.io:^4.8.1` - Socket.IO server
- `pusher:^5.2.0` - Pusher Node.js SDK
- `ably:^2.2.5` - Ably Node.js SDK

### JavaScript Libraries (Frontend)
- Socket.IO client (included)
- Pusher JavaScript SDK (load from CDN when needed)
- Ably JavaScript SDK (load from CDN when needed)