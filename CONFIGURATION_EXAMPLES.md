# FreePOS Communication Provider Configuration Examples

## Socket.IO (Default)
```bash
# .env configuration
COMMUNICATION_PROVIDER=socketio
FEED_SERVER_HOST=127.0.0.1
FEED_SERVER_PORT=3000
FEED_SERVER_KEY=your-secret-key
```

## Pusher Configuration
```bash
# .env configuration
COMMUNICATION_PROVIDER=pusher
PUSHER_APP_ID=123456
PUSHER_APP_KEY=your-app-key
PUSHER_APP_SECRET=your-app-secret  
PUSHER_APP_CLUSTER=us2
```

## Ably Configuration
```bash
# .env configuration
COMMUNICATION_PROVIDER=ably
ABLY_API_KEY=your-api-key
```

## Testing Your Setup

1. **Socket.IO Setup:**
   ```bash
   cd api/
   npm install
   npm start
   ```

2. **Check Admin Interface:**
   - Navigate to Admin → General Settings → Communication
   - Select your preferred provider
   - Enter credentials
   - Save settings

3. **Verify Connection:**
   - Check browser console for connection messages
   - Test real-time updates between POS terminals
   - Monitor provider dashboards (for Pusher/Ably)

## Switching Providers

You can switch between providers at any time:
1. Update provider in Admin interface
2. Or modify `.env` file and restart
3. Frontend will automatically adapt to new provider

## Production Recommendations

- **Small deployments (1-10 devices):** Socket.IO
- **Medium deployments (10-100 devices):** Pusher
- **Large deployments (100+ devices):** Ably
- **High availability requirements:** Pusher or Ably

## Troubleshooting

- Check provider status in Admin → General Settings → Communication  
- Verify credentials in provider dashboards
- Test with Socket.IO first to isolate connection issues
- Check browser network tab for failed requests