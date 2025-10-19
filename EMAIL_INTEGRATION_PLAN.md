# 📧 Email Integration Implementation Plan

## Overview
Implement "Send to Support" email forwarding when the toggle is enabled for a conversation.

---

## 🎯 **Functionality**

### **When Enabled:**
- Customer sends SMS → Twilio → Laravel → Database
- Laravel checks if "Send to Support" is ON for this number
- If ON: Send formatted email to `support@montanasky.net`
- Email includes: Customer info, message text, link to conversation

### **When Disabled:**
- SMS is received and saved normally
- No email sent

---

## 🔧 **Implementation Steps**

### **Step 1: Configure Laravel Mail (15 minutes)**

**Update `.env` (local and production):**
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.montanasky.net
MAIL_PORT=587
MAIL_USERNAME=your-smtp-username
MAIL_PASSWORD=your-smtp-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=sms-system@montanasky.net
MAIL_FROM_NAME="Montana Sky SMS System"
```

**Test configuration:**
```bash
php artisan tinker
Mail::raw('Test email', function($msg) {
    $msg->to('support@montanasky.net')->subject('Test');
});
```

---

### **Step 2: Create Email Template (30 minutes)**

**Create:** `app/Mail/InboundSmsNotification.php`
```php
<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InboundSmsNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $customerName;
    public $customerPhone;
    public $customerSku;
    public $messageBody;
    public $conversationLink;
    public $receivedAt;

    public function __construct($data)
    {
        $this->customerName = $data['customerName'];
        $this->customerPhone = $data['customerPhone'];
        $this->customerSku = $data['customerSku'];
        $this->messageBody = $data['messageBody'];
        $this->conversationLink = $data['conversationLink'];
        $this->receivedAt = $data['receivedAt'];
    }

    public function build()
    {
        return $this->subject('New SMS from ' . $this->customerName)
                    ->view('emails.inbound-sms');
    }
}
```

**Create:** `resources/views/emails/inbound-sms.blade.php`
```html
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
                  color: white; padding: 20px; border-radius: 8px 8px 0 0; }
        .content { background: #f9f9f9; padding: 20px; border: 1px solid #ddd; }
        .message-box { background: white; padding: 15px; border-left: 4px solid #667eea; 
                       margin: 15px 0; border-radius: 4px; }
        .customer-info { background: #e7f3ff; padding: 15px; border-radius: 4px; margin: 15px 0; }
        .button { display: inline-block; background: #667eea; color: white; 
                  padding: 12px 24px; text-decoration: none; border-radius: 6px; 
                  margin: 15px 0; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📱 New SMS Message</h1>
            <p>Received at {{ $receivedAt }}</p>
        </div>
        
        <div class="content">
            <div class="customer-info">
                <h3>👤 Customer Information</h3>
                <p><strong>Name:</strong> {{ $customerName }}</p>
                <p><strong>Phone:</strong> {{ $customerPhone }}</p>
                @if($customerSku)
                    <p><strong>SKU:</strong> {{ $customerSku }}</p>
                    <p><strong>Account:</strong> 
                        <a href="http://www.montanasky.net/MyAccount/AdminEdit.tpl?sku={{ $customerSku }}&findnet=y">
                            View Account
                        </a>
                    </p>
                @endif
            </div>

            <div class="message-box">
                <h3>💬 Message</h3>
                <p>{{ $messageBody }}</p>
            </div>

            <a href="{{ $conversationLink }}" class="button">
                View Full Conversation →
            </a>
        </div>

        <div class="footer">
            <p>This message was automatically forwarded because "Send to Support" is enabled for this conversation.</p>
            <p>Montana Sky SMS Management System</p>
        </div>
    </div>
</body>
</html>
```

---

### **Step 3: Update WebhookController (30 minutes)**

**Modify:** `app/Http/Controllers/API/WebhookController.php`

Add to the top:
```php
use App\Mail\InboundSmsNotification;
use Illuminate\Support\Facades\Mail;
```

Update `receiveSms()` method (after saving to database):
```php
// Check if "Send to Support" is enabled for this conversation
$sendToSupport = DB::table('conversation_preferences')
    ->where('phone_number', $from)
    ->value('send_to_support');

if ($sendToSupport) {
    try {
        // Get customer info
        $customerInfo = SmsMessage::where('FROM', $from)
            ->orWhere('TO', $from)
            ->first()
            ?->getCustomerInfo();

        // Send email
        Mail::to('support@montanasky.net')->send(
            new InboundSmsNotification([
                'customerName' => $customerInfo?->NAME ?? 'Unknown',
                'customerPhone' => $from,
                'customerSku' => $customerInfo?->SKU ?? null,
                'messageBody' => $body,
                'conversationLink' => route('conversations.show', [
                    'phoneNumber' => ltrim($from, '+')
                ]),
                'receivedAt' => now()->format('M d, Y g:i A'),
            ])
        );

        Log::info('Email sent to support', [
            'from' => $from,
            'message_sid' => $messageSid
        ]);
    } catch (\Exception $e) {
        Log::error('Failed to send support email', [
            'error' => $e->getMessage(),
            'from' => $from
        ]);
        // Don't fail the webhook if email fails
    }
}
```

---

### **Step 4: Testing (1 hour)**

**Local Testing:**
```bash
# Use Mailtrap or similar for testing
# Update .env with test SMTP settings
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your-mailtrap-username
MAIL_PASSWORD=your-mailtrap-password
```

**Test Checklist:**
1. [ ] Enable "Send to Support" for a test number
2. [ ] Send SMS to that number (use test number if needed)
3. [ ] Verify email arrives in Mailtrap
4. [ ] Check email formatting
5. [ ] Click "View Full Conversation" link
6. [ ] Verify all customer info displayed correctly
7. [ ] Test with customer that has SKU
8. [ ] Test with customer without SKU
9. [ ] Disable toggle and verify no email sent

**Production Testing:**
```bash
# Update production .env with real SMTP settings
ssh mooseweb@208.123.195.10
cd ~/sms-management-system
nano .env  # Add MAIL_* settings
```

---

### **Step 5: Monitor & Optimize (Ongoing)**

**Add to production monitoring:**
```bash
# Check for email errors in logs
tail -f storage/logs/laravel.log | grep "Failed to send support email"
```

**Considerations:**
- Add rate limiting if emails become excessive
- Consider queueing emails for better performance
- Add option to configure email recipient per conversation
- Add email template customization

---

## 🔄 **Alternative: Queue Emails (Recommended for Production)**

**Why Queue?**
- Don't slow down webhook response
- Handle email failures gracefully
- Retry automatically on failure

**Implementation:**
```bash
# Install queue worker
sudo apt-get install supervisor

# Configure supervisor
sudo nano /etc/supervisor/conf.d/laravel-worker.conf
```

**Supervisor config:**
```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /home/mooseweb/sms-management-system/artisan queue:work --sleep=3 --tries=3
autostart=true
autorestart=true
user=mooseweb
numprocs=1
redirect_stderr=true
stdout_logfile=/home/mooseweb/sms-management-system/storage/logs/worker.log
```

**Update `.env`:**
```env
QUEUE_CONNECTION=database
```

**Run migration:**
```bash
php artisan queue:table
php artisan migrate
```

**Change email send to:**
```php
Mail::to('support@montanasky.net')->queue(
    new InboundSmsNotification([...])
);
```

---

## 📊 **Success Metrics**

- Email delivery rate: >99%
- Average email send time: <100ms (queued) or <500ms (sync)
- Email open rate (if tracking enabled)
- Support team satisfaction with notifications

---

## 🛠️ **Troubleshooting**

### **Emails not sending:**
1. Check SMTP credentials in `.env`
2. Check Laravel logs: `storage/logs/laravel.log`
3. Test SMTP connection: `php artisan tinker` then test send
4. Check firewall rules (port 587 or 465)

### **Emails in spam:**
1. Add SPF record for sending domain
2. Add DKIM signature
3. Use authenticated SMTP
4. Use proper FROM address

### **Slow webhook response:**
1. Implement email queueing
2. Check SMTP server response time
3. Consider using AWS SES or SendGrid

---

**Estimated Implementation Time:** 2-3 hours
**Testing Time:** 1 hour
**Ready for:** After webhook goes live and system is stable

---

**Last Updated:** October 19, 2025

