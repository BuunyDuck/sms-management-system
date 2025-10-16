# 🚀 Local Testing Guide

## Server is Running!

Your SMS Management System is now live at:

### 🌐 Main URLs

| URL | Description |
|-----|-------------|
| **http://localhost:8001** | Landing page with system status |
| **http://localhost:8001/test-routes** | View all registered routes |
| **http://localhost:8001/phpinfo** | PHP configuration info |
| **http://localhost:8001/health** | API health check (JSON) |
| **http://localhost:8001/test-db** | Database connection test (JSON) |

### 🔗 Coming Soon Pages (Placeholders)

| URL | Feature |
|-----|---------|
| http://localhost:8001/conversations | Conversations View |
| http://localhost:8001/send | Send SMS |
| http://localhost:8001/chatbot | Chatbot Manager |

## 🎯 What You'll See

### Landing Page Features:
- ✅ System status indicators
- ✅ Laravel & PHP version info
- ✅ Environment details
- ✅ Database connection status
- ✅ Local server path testing
- ✅ Feature checklist (what's ready vs pending)
- ✅ Quick action buttons

### Test Routes Page:
- Complete list of all registered routes
- HTTP methods (GET, POST, PUT, DELETE)
- Route names
- Direct test links for GET routes

## 🔧 Server Commands

### Start Server (already running)
```bash
cd /Users/mooseman/Desktop/www/sms-management-system
php artisan serve --host=0.0.0.0 --port=8001
```

### Stop Server
```bash
# Find the process
lsof -i :8001

# Kill it (use the PID from above)
kill -9 <PID>
```

### Start with Full Dev Stack
```bash
composer dev
# This runs: server + queue worker + logs + vite
```

## 📱 Testing on Other Devices

Your server is accessible from other devices on your network:

```
http://YOUR_MAC_IP:8001
```

To find your Mac's IP:
```bash
ifconfig | grep "inet " | grep -v 127.0.0.1
```

## 🧪 API Testing with curl

### Health Check
```bash
curl http://localhost:8001/health
```

### Database Test
```bash
curl http://localhost:8001/test-db
```

### Expected Response (Health Check)
```json
{
  "status": "ok",
  "timestamp": "2025-10-16T...",
  "laravel": "12.34.0",
  "php": "8.4.8",
  "environment": "local"
}
```

## 🎨 Customization

### Landing Page
File: `resources/views/welcome.blade.php`
- Modern gradient design
- Responsive layout
- Real-time system info
- Feature status indicators

### Routes
File: `routes/web.php`
- All web routes defined here
- Easy to add new endpoints
- Named routes for easy linking

### Styling
- Pure CSS (no build step needed)
- Tailwind-inspired utility classes
- Mobile-responsive design

## 🚀 Next Steps

Now that paths are working, you can:

1. **Build the database** - Create migrations and models
2. **Add Twilio integration** - Connect to SMS gateway
3. **Create API endpoints** - RESTful API for frontend
4. **Build Vue components** - Interactive UI

## 🐛 Troubleshooting

### Server won't start
```bash
# Check if port is in use
lsof -i :8001

# Kill existing process
kill -9 <PID>

# Try different port
php artisan serve --port=8002
```

### White screen / Errors
```bash
# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Check logs
tail -f storage/logs/laravel.log
```

### Database errors
```bash
# Create SQLite file if missing
touch database/database.sqlite

# Run migrations
php artisan migrate
```

## 📊 Performance

### Page Load Times (Expected)
- Landing page: < 50ms
- Test routes: < 100ms
- PHP info: < 200ms

### Memory Usage
- Idle: ~10-15 MB
- Under load: ~30-50 MB

---

**Server Status:** 🟢 Running on port 8001  
**Last Updated:** October 16, 2025  
**Ready for development!** ✨

