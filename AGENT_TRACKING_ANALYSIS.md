# Agent Tracking Analysis - Multi-Agent SMS System

## 📋 Current ColdFusion Logic

### How Agent Tracking Works in ColdFusion:

1. **Session Management**
   - ColdFusion stores logged-in user in `session.contact`
   - Example: `session.contact = 'arnold bjork'`
   - Used to filter messages by agent

2. **Database Fields in `cat_sms` Table**
   - `fromname` - Shows WHO sent the message (agent name or customer name)
   - `toname` - Shows WHO received (usually "MTSKY" for inbound)
   - `FROM` - Phone number sending
   - `TO` - Phone number receiving

3. **Current Logic**

**When Agent Sends Outbound:**
```sql
-- ColdFusion sets fromname to agent's name from session
fromname = 'Support: Arnold Bjork'  (or just 'Arnold Bjork')
FROM = '+14062152048'  (MontanaSky number)
TO = '+14062616117'  (Customer number)
toname = 'Frederick Weber'  (Customer name, looked up from database)
```

**When Customer Sends Inbound:**
```sql
-- ColdFusion updates toname to show it's to MontanaSky
FROM = '+14062616117'  (Customer number)
fromname = 'Frederick Weber'  (Customer name)
TO = '+14062152048'  (MontanaSky number)
toname = 'MTSKY'  (Set by: update cat_sms set toname='MTSKY' where to IN ('+14062152048','+14067524335'))
```

4. **Filtering by Agent**
   - ColdFusion UI has button: "Filter {session.contact} Only"
   - Filters messages where `fromname` contains the agent's name
   - Used to show only messages sent by a specific agent

---

## 🔧 What Needs to Be Built in Laravel

### Phase 1: Authentication System
1. **Login system** 
   - Laravel auth with session management
   - Store logged-in agent name/email
   - Check: What table stores ColdFusion users?

2. **Database Tables to Check**
   - Need to identify where CF stores user credentials
   - Likely `db_297_netcustomers` or separate staff table?

### Phase 2: Agent Name Tracking

1. **Update `SmsMessage` Model**
   ```php
   // Add to fillable
   'fromname', 'toname'
   
   // Add relationship
   public function agent() {
       // Return agent who sent this message
   }
   ```

2. **Update Controllers to Save Agent Name**
   ```php
   // When agent sends message
   SmsMessage::create([
       'FROM' => config('twilio.from_number'),
       'TO' => $phoneNumber,
       'BODY' => $message,
       'fromname' => auth()->user()->name,  // NEW: Agent name
       'toname' => $customerName,           // Lookup from db_297_netcustomers
       // ... other fields
   ]);
   ```

3. **Update Inbound Webhook**
   ```php
   // When customer sends message
   SmsMessage::create([
       'FROM' => $customerPhone,
       'fromname' => $customerName,  // Lookup from db_297_netcustomers
       'TO' => config('twilio.from_number'),
       'toname' => 'MTSKY',          // Always MTSKY for inbound
       // ... other fields
   ]);
   ```

### Phase 3: UI Features

1. **Conversation List View**
   - Show agent name who last interacted
   - Filter by agent dropdown
   - "My Conversations" vs "All Conversations"

2. **Conversation Detail View**
   - Show agent name on outbound messages
   - Example: "Arnold Bjork • 2:15 PM"

3. **Archive Feature**
   - Include agent names in formatted body
   - Already implemented: "MontanaSkyAB" (initials)

---

## ❓ Questions to Answer

1. **Where are ColdFusion user accounts stored?**
   - Need table name and structure
   - Password hashing method?
   - Email/username field?

2. **How does ColdFusion login work?**
   - Session management?
   - Cookie-based?
   - SSO integration?

3. **Current Agent List?**
   - Who are the active agents?
   - How many agents use the system?

4. **Role/Permission System?**
   - Are there different agent roles?
   - Admin vs regular agent?

---

## 🎯 Proposed Laravel Implementation

### Database Schema Changes (if needed)
```sql
-- Add agent tracking to cat_sms_dev if not exists
ALTER TABLE cat_sms_dev 
  ADD COLUMN IF NOT EXISTS fromname VARCHAR(255) NULL,
  ADD COLUMN IF NOT EXISTS toname VARCHAR(255) NULL;

-- Create staff/agents table (if doesn't exist)
CREATE TABLE IF NOT EXISTS staff_users (
  id INT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(255) NOT NULL,
  email VARCHAR(255) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  is_active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### Laravel Auth Setup
```bash
php artisan make:auth  # If not using Breeze/Fortify
php artisan make:model Staff -m
php artisan make:middleware TrackAgent
```

### Middleware to Track Agent
```php
// app/Http/Middleware/TrackAgent.php
public function handle($request, Closure $next)
{
    if (auth()->check()) {
        session(['agent_name' => auth()->user()->name]);
    }
    return $next($request);
}
```

---

## 🚀 Next Steps

1. **Identify ColdFusion user table** - WHERE does CF store login credentials?
2. **Test authentication** - Can we use same credentials from CF?
3. **Implement Laravel auth** - Build login system
4. **Update message saving** - Add fromname/toname fields
5. **Add agent filtering** - Filter conversations by agent
6. **UI improvements** - Show agent names in conversation view

---

## 📝 Notes

- ColdFusion uses `session.contact` to store logged-in user name
- Messages are filtered by `fromname` field containing agent name
- `toname = 'MTSKY'` is used to mark inbound messages
- Archive feature already extracts agent initials correctly
- Need to maintain compatibility with existing CF system during transition

