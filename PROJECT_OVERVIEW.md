# SMS Management System

**Modern PHP/Laravel replacement for legacy ColdFusion SMS system**

## 🎯 Purpose

Customer service SMS platform that handles bidirectional text messaging with customers via Twilio, includes an automated chatbot for common support issues, and integrates with ticketing and customer management systems.

## 🚀 Quick Start

```bash
# Initial setup
composer setup

# Start development servers (runs: server, queue worker, logs, and Vite)
composer dev

# Run tests
composer test
```

## 📋 Features

### Core Functionality
- ✅ Send/receive SMS via Twilio
- ✅ Automated chatbot with menu system
- ✅ Customer phone number linking
- ✅ Ticket system integration (create/link)
- ✅ Email notifications to staff
- ✅ Search/filter message history
- ✅ Media attachments (MMS)
- ✅ Conversation view

### Admin Features
- Staff message routing
- Bulk operations
- Analytics dashboard
- Template management
- Chatbot menu editor

## 🗄️ Database Schema

### Primary Tables

**sms_messages** - Main message log
- Stores all inbound/outbound messages
- Links to customers and tickets
- Tracks delivery status
- Media attachments

**customers** - Customer master data
- Name, email, SKU
- Links to legacy system

**customer_phones** - Phone number mapping
- Links phone numbers to customers
- Priority flagging for multiple numbers
- SMS capability tracking

**chatbot_sessions** - Chatbot state
- Tracks menu navigation
- 30-minute timeout
- Conversation context

## 🏗️ Architecture

```
Frontend (Vue.js 3)
    ↓
API Routes (Laravel)
    ↓
Controllers
    ↓
Services (Business Logic)
    ↓
Models (Eloquent ORM)
    ↓
Database (SQLite/MySQL)

External APIs:
- Twilio (SMS gateway)
- Ticket System (integration)
- Customer Portal (integration)
```

## 📁 Project Structure

```
app/
├── Http/
│   ├── Controllers/        # HTTP request handlers
│   │   ├── SmsController.php
│   │   ├── ConversationController.php
│   │   ├── WebhookController.php
│   │   ├── CustomerController.php
│   │   └── ChatbotController.php
│   └── Middleware/         # Auth, validation, logging
│       └── ValidateTwilioSignature.php
├── Models/                 # Eloquent models
│   ├── SmsMessage.php
│   ├── Customer.php
│   ├── CustomerPhone.php
│   └── ChatbotSession.php
├── Services/               # Business logic
│   ├── SmsService.php
│   ├── TwilioService.php
│   ├── ChatbotService.php
│   ├── CustomerService.php
│   └── NotificationService.php
├── Jobs/                   # Async queue jobs
│   ├── SendSmsJob.php
│   ├── ProcessInboundSmsJob.php
│   └── SendEmailNotificationJob.php
└── Events/                 # Event-driven
    ├── SmsReceived.php
    ├── SmsSent.php
    └── SmsDelivered.php

database/
├── migrations/             # Schema version control
└── seeders/                # Test data

resources/
├── js/                     # Vue.js components
│   └── components/
│       ├── ConversationView.vue
│       ├── MessageList.vue
│       └── ChatbotEditor.vue
└── views/                  # Blade templates
    └── layouts/
        └── app.blade.php

routes/
├── api.php                 # API routes
└── web.php                 # Web routes

tests/
├── Feature/                # Integration tests
└── Unit/                   # Unit tests
```

## 🔧 Configuration

### Environment Variables

```env
# App
APP_NAME="SMS Management"
APP_ENV=local
APP_URL=http://localhost

# Database (SQLite for local dev, MySQL for production)
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/database.sqlite

# Or MySQL
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sms_system
DB_USERNAME=root
DB_PASSWORD=

# Twilio
TWILIO_ACCOUNT_SID=your_account_sid
TWILIO_AUTH_TOKEN=your_auth_token
TWILIO_FROM_NUMBER=+14062152048
TWILIO_WEBHOOK_URL=https://yourdomain.com/webhook/twilio

# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_FROM_ADDRESS="support@example.com"
MAIL_FROM_NAME="${APP_NAME}"

# External Integrations
TICKET_SYSTEM_URL=https://www.montanasky.net/MyAccount/TicketTracker/
CUSTOMER_PORTAL_URL=https://www.montanasky.net/MyAccount/

# Chatbot
CHATBOT_SESSION_TIMEOUT=30 # minutes
```

## 🛠️ Development

### Creating New Features

1. **Database Changes**
```bash
php artisan make:migration create_feature_table
php artisan migrate
```

2. **Models**
```bash
php artisan make:model FeatureName
```

3. **Controllers**
```bash
php artisan make:controller FeatureController
```

4. **Services** (manual creation in `app/Services/`)
```php
<?php
namespace App\Services;

class FeatureService {
    public function doSomething() {
        // Business logic here
    }
}
```

5. **Tests**
```bash
php artisan make:test FeatureTest
```

### Coding Standards

- Follow PSR-12
- Use type hints
- Document public methods
- Write tests for business logic
- Use constants for statuses
- Accessors for computed properties (like campaign-system)

Example from campaign-system:
```php
// Status constants
const STATUS_PENDING = 'pending';
const STATUS_SENT = 'sent';

// Accessor for computed property
public function getDeliveredCountAttribute()
{
    return $this->messages()->where('status', 'delivered')->count();
}
```

## 📊 Migration from Legacy System

### Phase 1: Foundation (✅ Complete)
- Laravel 12 project setup
- Twilio SDK installed
- Composer scripts configured
- Project structure documented

### Phase 2: Database & Models (🚧 In Progress)
- [ ] Create migrations
- [ ] Create Eloquent models
- [ ] Define relationships
- [ ] Add seeders

### Phase 3: Core Services (⏳ Next)
- [ ] TwilioService (send/receive)
- [ ] SmsService (business logic)
- [ ] ChatbotService (menu system)
- [ ] CustomerService (lookups)

### Phase 4: API & Routes (⏳ Pending)
- [ ] Webhook endpoint (Twilio callbacks)
- [ ] REST API for frontend
- [ ] Web routes for views

### Phase 5: Frontend (⏳ Pending)
- [ ] Vue.js conversation view
- [ ] Message list component
- [ ] Search/filter UI
- [ ] Admin panels

### Phase 6: Integration (⏳ Pending)
- [ ] Ticket system API
- [ ] Customer portal integration
- [ ] Email notifications

### Phase 7: Testing & Deployment (⏳ Pending)
- [ ] Unit tests
- [ ] Integration tests
- [ ] Load testing
- [ ] Production deployment

## 🔐 Security

- JWT authentication for API
- Twilio signature validation on webhooks
- SQL injection prevention (Eloquent ORM)
- XSS protection (Vue.js escaping)
- CSRF tokens
- Rate limiting

## 📝 API Documentation

### Webhook Endpoints (Public)
```
POST /webhook/twilio        # Receive SMS from Twilio
```

### API Endpoints (Authenticated)
```
GET    /api/conversations            # List all conversations
GET    /api/conversations/{phone}    # Get conversation by phone
POST   /api/sms/send                 # Send outbound SMS
POST   /api/customers/link-phone     # Link phone to customer
GET    /api/chatbot/menu             # Get chatbot menu config
```

## 🧪 Testing

```bash
# Run all tests
php artisan test

# Run specific test
php artisan test --filter=SmsServiceTest

# With coverage
php artisan test --coverage
```

## 📦 Dependencies

### PHP Packages
- **laravel/framework** (^12.0) - Core framework
- **twilio/sdk** (^8.8) - Twilio API client
- **guzzlehttp/guzzle** (^7.0) - HTTP client for API integrations

### Development
- **phpunit/phpunit** - Testing framework
- **laravel/pint** - Code style fixer
- **laravel/pail** - Log viewer

### Frontend (to be installed)
- **vue** (^3.0) - Frontend framework
- **axios** - HTTP client
- **pinia** - State management
- **vue-router** - Routing

## 🚀 Deployment

### Requirements
- PHP 8.2+
- MySQL 8.0+ or PostgreSQL 13+
- Redis (for queues and caching)
- Nginx or Apache
- Supervisor (for queue workers)

### Steps
1. Clone repository
2. `composer install --no-dev`
3. `npm install && npm run build`
4. Configure `.env`
5. `php artisan migrate --force`
6. Set up supervisor for queue workers
7. Configure web server
8. Set up SSL certificate
9. Configure Twilio webhook URL

## 📚 Additional Resources

- [Laravel Documentation](https://laravel.com/docs)
- [Twilio SMS API](https://www.twilio.com/docs/sms)
- [Vue.js 3 Guide](https://vuejs.org/guide/)

## 🤝 Contributing

1. Create feature branch
2. Make changes
3. Write/update tests
4. Run `composer test` and `composer pint`
5. Submit pull request

## 📄 License

Proprietary - Montana Sky Internet

---

**Status:** 🚧 Active Development  
**Version:** 0.1.0  
**Last Updated:** October 16, 2025

