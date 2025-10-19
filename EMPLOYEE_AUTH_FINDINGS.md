# Employee Authentication - Database Findings

## ✅ **FOUND: Employee Database Table**

**Table Name:** `db_1257_employees`  
**Database:** `mtsky-webkittens` (same as SMS database)  
**Connection:** Already connected via Laravel config

---

## 📊 **Key Fields for Authentication**

| Field | Type | Purpose |
|-------|------|---------|
| `EmployeeName` | varchar(255) | Full name (e.g., "Frederick Weber") |
| `EmployeeUserName` | varchar(255) | Username (e.g., "frederick") |
| `workemail` | varchar(255) | Email address |
| `EmployeePassword` | varchar(255) | **Existing password** (unknown hash) |
| `EmployeeStatus` | varchar(255) | FullTime, PartTime, Hourly, Suspended, Terminated |
| `IsAdmin` | varchar(255) | "yes" or "no" |
| `department` | varchar(255) | Department assignment |
| `EmployeeTitle` | varchar(255) | Job title |

---

## 👥 **Current Active Employees**

**Total Active:** 35 employees (excluding Terminated/Suspended)

**Sample Active Staff:**
- Frederick Weber (frederick) - Admin
- Mark Wiggins (mark) - Admin  
- Ryan Bowman (rbowman) - Admin
- Todd Janssen (tjanssen) - Admin
- Matt Gann (mgann) - Admin
- Christy Wiggins (christy) - Admin
- Bubba DeBubba (bubba2) - Admin
- + 28 more staff members

---

## 🔐 **Password Situation**

The table has an `EmployeePassword` column, but we DON'T know:
- ❓ Hash algorithm used
- ❓ Salt method
- ❓ Compatibility with PHP password_verify()

**Options:**
1. **Don't touch existing passwords** - Laravel manages its own
2. **Attempt to reverse-engineer** - Risky, probably WebDNA-specific
3. **Ask ServerAdmin** - They might know the format

---

## 🎯 **Recommended Laravel Implementation**

### **Strategy: Hybrid Validation**

**Step 1: Check if user is valid employee**
```php
// On registration/login attempt
$employee = DB::connection('mysql')
    ->table('db_1257_employees')
    ->where('workemail', $email)
    ->whereNotIn('EmployeeStatus', ['Terminated', 'Suspended'])
    ->first();

if (!$employee) {
    return error('Not authorized - contact admin');
}
```

**Step 2: Laravel manages its own passwords**
```php
// First-time setup
User::create([
    'name' => $employee->EmployeeName,
    'email' => $employee->workemail,
    'username' => $employee->EmployeeUserName,
    'employee_id' => $employee->id,
    'is_admin' => $employee->IsAdmin === 'yes',
    'password' => bcrypt($request->password), // NEW Laravel password
]);
```

**Step 3: Regular login uses Laravel auth**
```php
// Standard Laravel login
Auth::attempt(['email' => $email, 'password' => $password]);
```

---

## 🚀 **Implementation Plan**

### **Phase 1: Database Setup**
```bash
php artisan make:migration create_users_table
php artisan make:model User
```

**Laravel `users` table structure:**
```sql
CREATE TABLE users (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(255) NOT NULL,          -- From EmployeeName
  email VARCHAR(255) UNIQUE NOT NULL,  -- From workemail  
  username VARCHAR(255) UNIQUE,        -- From EmployeeUserName
  employee_id INT,                     -- FK to db_1257_employees.id
  is_admin BOOLEAN DEFAULT FALSE,      -- From IsAdmin
  password VARCHAR(255) NOT NULL,      -- Laravel bcrypt
  remember_token VARCHAR(100),
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  last_login_at TIMESTAMP NULL,
  
  INDEX(employee_id),
  INDEX(email),
  INDEX(username)
);
```

### **Phase 2: Auth Middleware**
```php
// app/Http/Middleware/ValidateEmployee.php
public function handle($request, Closure $next)
{
    if (!auth()->check()) {
        return redirect('/login');
    }
    
    // Check if still active in employee DB
    $employee = DB::connection('mysql')
        ->table('db_1257_employees')
        ->where('id', auth()->user()->employee_id)
        ->whereNotIn('EmployeeStatus', ['Terminated', 'Suspended'])
        ->first();
    
    if (!$employee) {
        Auth::logout();
        return redirect('/login')->with('error', 'Account no longer active');
    }
    
    return $next($request);
}
```

### **Phase 3: Registration Controller**
```php
// app/Http/Controllers/Auth/RegisterController.php
public function register(Request $request)
{
    // Validate employee exists and is active
    $employee = DB::connection('mysql')
        ->table('db_1257_employees')
        ->where('workemail', $request->email)
        ->whereNotIn('EmployeeStatus', ['Terminated', 'Suspended'])
        ->first();
    
    if (!$employee) {
        return back()->withErrors([
            'email' => 'Email not found in employee database'
        ]);
    }
    
    // Create Laravel user
    $user = User::create([
        'name' => $employee->EmployeeName,
        'email' => $employee->workemail,
        'username' => $employee->EmployeeUserName,
        'employee_id' => $employee->id,
        'is_admin' => $employee->IsAdmin === 'yes',
        'password' => Hash::make($request->password),
    ]);
    
    Auth::login($user);
    
    return redirect('/conversations');
}
```

---

## 🔧 **Agent Tracking Integration**

**When sending SMS:**
```php
// In ConversationController::send()
SmsMessage::create([
    'FROM' => config('twilio.from_number'),
    'TO' => $phoneNumber,
    'BODY' => $message,
    'fromname' => auth()->user()->name,  // "Frederick Weber"
    'toname' => $customerName,
    // ... other fields
]);
```

**Conversation filtering:**
```php
// Filter by logged-in agent
$myConversations = SmsMessage::where('fromname', auth()->user()->name)
    ->distinct('TO')
    ->get();

// All conversations (admin only)
if (auth()->user()->is_admin) {
    $allConversations = SmsMessage::all();
}
```

---

## 📋 **Questions for ServerAdmin**

1. **Password Format:**
   - What hash algorithm does WebDNA use for `EmployeePassword`?
   - Can we attempt to validate against it?
   - Or should Laravel manage separate passwords?

2. **Security Approval:**
   - OK for Laravel to READ employee table (no writes)?
   - OK to create separate `users` table for Laravel auth?
   - Any SSO requirements?

3. **Email/Username:**
   - Use `workemail` or `EmployeeUserName` for login?
   - Some employees missing email (Tyler Stewart)
   - Should we require email for SMS system?

---

## 🎯 **My Recommendation to ServerAdmin**

**"We want to build a modern authentication system for the SMS platform that:**
1. **Validates** user emails against the existing `db_1257_employees` table (read-only)
2. **Checks** EmployeeStatus to ensure they're active (not Terminated/Suspended)
3. **Stores** Laravel-specific passwords in a new `users` table (separate from WebDNA)
4. **Syncs** on each login to verify employee is still active
5. **No changes** to existing employee database or WebDNA system

**This approach:**
- ✅ Maintains single source of truth (employee table)
- ✅ Modern security (bcrypt/Argon2 passwords)
- ✅ No risk to existing systems
- ✅ Read-only access to employee data
- ✅ Independent password management
- ✅ Easy to audit who accessed what

**Technical Implementation:**
- Laravel 12 authentication
- MySQL connection already established
- First-time registration validates against employee DB
- Subsequent logins use Laravel's built-in auth
- Middleware checks employee status on each request"

---

## 🚀 **Next Steps**

1. ✅ **Database found** - `db_1257_employees` 
2. ✅ **Active employees identified** - 35 staff
3. ✅ **Implementation plan created** - Ready to code
4. ⏳ **ServerAdmin approval** - Discuss approach
5. ⏳ **Build auth system** - Install Laravel Breeze + custom validation
6. ⏳ **Seed initial users** - Invite staff to register
7. ⏳ **Add agent tracking** - fromname/toname fields
8. ⏳ **UI filters** - "My Conversations" vs "All"

---

## 💡 **Alternative: Skip Validation (Not Recommended)**

We *could* just create a standalone auth system without checking employee DB, but:
- ❌ Gets out of sync
- ❌ Manual user management
- ❌ No automatic disabling when terminated
- ❌ Duplicate data maintenance

**The hybrid approach is WAY better!** ✅

