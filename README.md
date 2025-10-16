# 📱 SMS Management System

Modern Laravel-based SMS platform for Montana Sky Internet customer communications.

## 🚀 Quick Start

```bash
# Navigate to project
cd /Users/mooseman/Desktop/www/sms-management-system

# Install dependencies (if needed)
composer install

# Configure Twilio credentials in .env
# Add your TWILIO_ACCOUNT_SID and TWILIO_AUTH_TOKEN

# Start development server
php artisan serve --port=8001
```

Visit: **http://localhost:8001**

## 📋 Phase 1: COMPLETE ✅

**Status:** SMS Send/Receive with Logging Only (No Database)

### What's Working:

- ✅ **Send SMS via Twilio** - Full integration with Twilio API
- ✅ **Receive SMS Webhook** - Handle incoming messages
- ✅ **Beautiful Test UI** - `/send` route with 3 test sections
- ✅ **Logging System** - All messages logged to `storage/logs/laravel.log`
- ✅ **Phone Validation** - Auto-format to E.164 standard
- ✅ **Status Callbacks** - Track delivery status

### Key Files:

- `app/Services/TwilioService.php` - Twilio integration service
- `app/Http/Controllers/API/SmsController.php` - Send SMS endpoints
- `app/Http/Controllers/API/WebhookController.php` - Receive SMS webhooks
- `routes/api.php` - API routes
- `resources/views/sms-test.blade.php` - Test interface

### API Endpoints:

```
POST   /api/sms/send              Send custom SMS
POST   /api/sms/send-test         Send test SMS
GET    /api/sms/test-connection   Test Twilio credentials
POST   /webhook/twilio            Receive incoming SMS
POST   /webhook/twilio/status     SMS delivery status updates
```

## 🧪 Testing Phase 1

### 1. Setup Twilio Credentials

Edit `.env` and add your credentials:

```env
TWILIO_ACCOUNT_SID=ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
TWILIO_AUTH_TOKEN=xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
TWILIO_FROM_NUMBER=+14062152048
TWILIO_VALIDATE_SIGNATURE=false
```

### 2. Test via Web Interface

1. Start server: `php artisan serve --port=8001`
2. Visit: http://localhost:8001/send
3. Click "Test Connection"
4. Send test SMS to your phone
5. Watch logs: `tail -f storage/logs/laravel.log`

### 3. Test Incoming SMS (with ngrok)

```bash
# Install ngrok
brew install ngrok

# Start ngrok
ngrok http 8001

# Configure webhook in Twilio Console:
# https://abc123.ngrok.io/webhook/twilio
```

**Send SMS to your Twilio number** - You'll see it appear in your terminal logs!

## 📖 Documentation

- **[PHASE1_TESTING.md](./PHASE1_TESTING.md)** - Complete testing guide
- **[PROJECT_OVERVIEW.md](./PROJECT_OVERVIEW.md)** - Full system analysis
- **[DEPLOYMENT_SECURITY.md](./DEPLOYMENT_SECURITY.md)** - Security best practices
- **[NEXT_STEPS.md](./NEXT_STEPS.md)** - Development roadmap

## 🗺️ Next Steps (Phase 2)

- [ ] Database migration for SMS storage
- [ ] SmsMessage Eloquent model
- [ ] Save sent/received messages
- [ ] Conversation history view
- [ ] Search & filter messages

**Estimated time:** 30 minutes

## 🛠️ Tech Stack

- **Framework:** Laravel 12
- **PHP:** 8.2+
- **Database:** SQLite (local), MySQL/PostgreSQL (production)
- **SMS Provider:** Twilio
- **Frontend:** Blade templates (Vue.js coming later)

## 📁 Project Structure

```
sms-management-system/
├── app/
│   ├── Http/Controllers/API/
│   │   ├── SmsController.php       # Send SMS endpoints
│   │   └── WebhookController.php   # Receive webhooks
│   └── Services/
│       └── TwilioService.php       # Twilio integration
├── routes/
│   ├── web.php                     # Web routes
│   └── api.php                     # API routes
├── resources/views/
│   ├── welcome.blade.php           # Landing page
│   └── sms-test.blade.php          # Test interface
├── config/services.php             # Twilio config
└── storage/logs/                   # Message logs
```

## 🔒 Security Notes

- **Self-contained:** All project files in this directory
- **No external data:** SQLite database stored locally
- **.env excluded:** Credentials never committed to git
- **Production ready:** Easy to deploy entire folder

## 💡 Tips

### Watch Logs in Real-time:

```bash
tail -f storage/logs/laravel.log
```

### Test API Directly:

```bash
# Test connection
curl http://localhost:8001/api/sms/test-connection

# Send SMS
curl -X POST http://localhost:8001/api/sms/send \
  -d "to=+14065551234" \
  -d "body=Hello from Laravel!"
```

### Clear Cache:

```bash
php artisan config:clear
php artisan route:clear
php artisan cache:clear
```

## 🐛 Troubleshooting

### Twilio credentials not found

- Check `.env` has `TWILIO_ACCOUNT_SID` and `TWILIO_AUTH_TOKEN`
- Run `php artisan config:clear`
- Restart server

### API routes not found

- API routes are prefixed with `/api/`
- Check `bootstrap/app.php` includes `api.php`

### Webhook not receiving

- Use ngrok to expose local server
- Set webhook URL in Twilio Console
- Check logs for errors

## 📞 Support

For questions or issues:
- Check documentation in `/docs` route
- Review `PHASE1_TESTING.md` for detailed testing
- Check Laravel logs: `storage/logs/laravel.log`

---

**Version:** 0.1.0 - Phase 1 Complete  
**Last Updated:** October 16, 2025  
**Montana Sky Internet** © 2025
