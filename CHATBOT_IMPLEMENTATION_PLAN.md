# Chatbot Implementation Plan

**Date:** October 20, 2025  
**Status:** Planning Phase  
**Target:** Full Laravel SMS Chatbot System

---

## 🎯 Project Overview

Build a complete SMS chatbot system in Laravel to replace the legacy ColdFusion chatbot, with modern admin panel for menu management.

---

## 📋 Phase 1: Core Chatbot Backend (2-4 hours)

### 1.1 Database & Models
- [x] Use existing `smsbot` table (phone, menu, updated_dt)
- [ ] Create `BotMenu` model for Laravel
- [ ] Create `BotSession` service class
- [ ] Add indexes for performance

### 1.2 Chatbot Service Layer
```
app/Services/ChatbotService.php
├─ detectKeyword() - Check for MENU/EXIT
├─ getSession() - Load active session from smsbot
├─ updateSession() - Save menu state
├─ clearSession() - Exit/timeout cleanup
├─ processInput() - Handle user navigation
├─ getMenuResponse() - Return appropriate menu
└─ isSessionExpired() - Check 30-min timeout
```

### 1.3 Menu Logic
**Trigger:** "MENU" (case-insensitive)
**Exit:** "EXIT" (explicit) or 30-minute timeout (automatic)
**State:** Comma-delimited path (e.g., "m,4,2")

**Main Menu (12 Options):**
1. SkyConnect
2. DSL
3. Cable
4. Email (has submenus)
5. Outages
6. Speedtest
7. Payments
8. MontanaSkyTV
9. Voip Phone
10. Plume Wifi
11. Fiber GPON
12. Point to Points

### 1.4 Template System
**Decision Needed:** Database vs Files

**Option A: Keep .txt Files (Migration)**
- Copy all `/sms/*.txt` files to Laravel
- Store in `storage/chatbot/templates/`
- Load via `File::get()`
- Matches CF system exactly

**Option B: Move to Database (Modern)**
```sql
CREATE TABLE chatbot_menus (
    id INT PRIMARY KEY,
    parent_id INT NULL,
    option_number INT,
    title VARCHAR(255),
    content TEXT,
    media_url VARCHAR(255),
    is_active BOOLEAN,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

**Recommendation:** Start with Files (faster), migrate to DB in Phase 3

### 1.5 Media Support
- Parse `<media>` tags in templates
- Support images: logo.png, reboot-graphic.jpg, PLUMEINSTALL.png
- Host on production server or keep on dash.montanasky.net

### 1.6 Webhook Integration
```
WebhookController::receiveSms()
├─ Check if "MENU" keyword → ChatbotService::start()
├─ Check if active session → ChatbotService::processInput()
├─ Check if "EXIT" → ChatbotService::end()
└─ Otherwise → Normal agent processing
```

**IMPORTANT:** Reply from same number customer texted to (Option B: First TO logic)

---

## 📋 Phase 2: Testing & Validation (30 min)

### 2.1 Unit Tests
- [ ] Keyword detection
- [ ] Session timeout logic
- [ ] Menu navigation
- [ ] State management

### 2.2 Integration Tests
- [ ] Full conversation flow
- [ ] Invalid input handling
- [ ] Media delivery
- [ ] EXIT behavior

### 2.3 Live SMS Testing
- [ ] Text "MENU" to 752-4335
- [ ] Navigate through submenus
- [ ] Test "EXIT" command
- [ ] Test 30-minute timeout
- [ ] Verify correct FROM number

---

## 📋 Phase 3: Admin Panel (2-3 hours)

### 3.1 Menu Management UI
```
Route: /admin/chatbot/menus
Features:
├─ List all menu options (tree view)
├─ Create new menu option
├─ Edit existing option
│  ├─ Title
│  ├─ Content (rich text editor)
│  ├─ Media URL
│  ├─ Option number
│  └─ Active/Inactive toggle
├─ Delete menu option
├─ Reorder options (drag-and-drop)
└─ Preview chatbot flow
```

### 3.2 Template Editor
```
Features:
├─ WYSIWYG editor for menu content
├─ Markdown support
├─ Media upload/picker
├─ Variable support (customer name, account, etc.)
├─ Preview mode (see as customer would)
└─ Version history
```

### 3.3 Image Management
```
Route: /admin/chatbot/media
Features:
├─ Upload images
├─ Image library browser
├─ CDN/storage integration
├─ Image optimization
└─ Usage tracking (which menus use which images)
```

### 3.4 Analytics Dashboard
```
Route: /admin/chatbot/analytics
Metrics:
├─ Total chatbot sessions (today, week, month)
├─ Most popular menu options
├─ Average session duration
├─ Completion rate (reached end vs EXIT)
├─ Drop-off points (where users quit)
└─ Response time stats
```

### 3.5 Test Mode
```
Features:
├─ Send test SMS to chatbot
├─ Simulate conversation flow
├─ Preview all menus
├─ Test media delivery
└─ Validate navigation paths
```

---

## 📋 Phase 4: Landing Page (1-2 hours)

### Purpose: **TO BE DETERMINED**

**Option A: Customer-Facing Marketing Page**
```
URL: /sms-support or public domain
Content:
├─ "Text us at 406-752-4335"
├─ Feature highlights
├─ How to use chatbot
├─ FAQ
└─ Contact info
```

**Option B: Internal Agent Dashboard**
```
URL: /dashboard or /home
Content:
├─ Quick stats
├─ Active conversations
├─ Recent chatbot sessions
├─ Agent performance
└─ Quick actions
```

**Option C: Public SMS Info Page**
```
URL: /text-support
Content:
├─ SMS service hours
├─ What you can do via SMS
├─ Chatbot menu preview
├─ Privacy policy
└─ SMS terms
```

**Decision Needed:** Clarify purpose and audience

---

## 🔧 Technical Architecture

### Directory Structure
```
app/
├─ Services/
│  ├─ ChatbotService.php
│  ├─ BotMenuService.php
│  └─ BotSessionService.php
├─ Models/
│  ├─ BotSession.php (uses smsbot table)
│  └─ BotMenu.php (if using DB)
├─ Http/Controllers/
│  ├─ Admin/ChatbotController.php
│  └─ API/WebhookController.php (enhance existing)
└─ Http/Middleware/
   └─ ChatbotMiddleware.php (optional)

resources/views/
├─ admin/
│  └─ chatbot/
│     ├─ index.blade.php (menu list)
│     ├─ edit.blade.php (menu editor)
│     ├─ analytics.blade.php
│     └─ test.blade.php
└─ landing/
   └─ sms-support.blade.php

storage/
└─ chatbot/
   ├─ templates/ (if using files)
   │  ├─ SKYCONNECT.txt
   │  ├─ DSL.txt
   │  └─ ...
   └─ media/ (if self-hosting images)
      ├─ logo.png
      ├─ reboot-graphic.jpg
      └─ ...

routes/
└─ web.php (add admin routes)
```

### Database Schema
```sql
-- Existing table (ColdFusion legacy)
CREATE TABLE smsbot (
    phone VARCHAR(10) PRIMARY KEY,
    menu VARCHAR(255),
    updated_dt DATETIME
);

-- New tables (Phase 3)
CREATE TABLE chatbot_menus (
    id INT AUTO_INCREMENT PRIMARY KEY,
    parent_id INT NULL,
    option_number INT,
    title VARCHAR(255),
    content TEXT,
    media_url VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES chatbot_menus(id) ON DELETE CASCADE
);

CREATE TABLE chatbot_sessions_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    phone VARCHAR(15),
    menu_path VARCHAR(255),
    user_input VARCHAR(255),
    bot_response TEXT,
    session_start DATETIME,
    session_end DATETIME,
    exit_type ENUM('explicit', 'timeout', 'error') NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_phone ON chatbot_sessions_log(phone);
CREATE INDEX idx_session_start ON chatbot_sessions_log(session_start);
```

---

## 🎯 Success Criteria

### Phase 1 Complete When:
- [x] Customer texts "MENU" → Receives main menu
- [x] Customer sends number (1-12) → Receives submenu
- [x] Customer sends "EXIT" → Receives "Goodbye" + session cleared
- [x] 30-min timeout → Next message goes to agent
- [x] Invalid input → Helpful error message
- [x] All menus work with media
- [x] Replies from correct phone number

### Phase 2 Complete When:
- [x] All unit tests pass
- [x] Integration tests pass
- [x] Live SMS test successful
- [x] No breaking changes to existing features

### Phase 3 Complete When:
- [x] Admin can edit all menu options via UI
- [x] Admin can upload/manage images
- [x] Preview mode works
- [x] Analytics dashboard shows data
- [x] Test mode functional

### Phase 4 Complete When:
- [x] Landing page live
- [x] Responsive design
- [x] Content approved
- [x] SEO optimized (if public)

---

## 🚨 Risk Management

### Potential Issues:
1. **Breaking Existing Features**
   - Mitigation: Comprehensive tests, backup branch
   
2. **Performance (96K+ messages in DB)**
   - Mitigation: Proper indexing, query optimization
   
3. **Race Conditions (Multiple webhooks)**
   - Mitigation: Database locking, session validation
   
4. **Template Migration Complexity**
   - Mitigation: Start with files, migrate to DB later
   
5. **Media Hosting (CF vs Laravel)**
   - Mitigation: Proxy CF URLs initially, migrate later

---

## 📅 Timeline Estimates

| Phase | Task | Time | Total |
|-------|------|------|-------|
| 1 | Database & Models | 30 min | 30 min |
| 1 | Chatbot Service | 1 hour | 1.5 hrs |
| 1 | Menu Logic | 1 hour | 2.5 hrs |
| 1 | Webhook Integration | 30 min | 3 hrs |
| 1 | Template Migration | 30 min | 3.5 hrs |
| 2 | Testing | 30 min | 4 hrs |
| 3 | Admin UI Setup | 1 hour | 5 hrs |
| 3 | Menu CRUD | 1 hour | 6 hrs |
| 3 | Analytics | 30 min | 6.5 hrs |
| 3 | Test Mode | 30 min | 7 hrs |
| 4 | Landing Page | 1-2 hours | 8-9 hrs |

**Total Estimated Time:** 8-9 hours (can span multiple sessions)

---

## 🔄 Migration Strategy

### Transition Plan:
```
Phase 1: Parallel Systems (CURRENT PLAN)
├─ 752-4335 → Laravel (new chatbot)
├─ 215-2048 → Laravel (new chatbot)
└─ CF chatbot deprecated

Phase 2: Validate & Monitor (1 week)
├─ Monitor Laravel chatbot performance
├─ Compare usage vs CF (if any overlap)
├─ Collect user feedback
└─ Fix any issues

Phase 3: Enhance (Ongoing)
├─ Add admin panel
├─ Add analytics
├─ Optimize responses
└─ Add new features
```

### Rollback Plan:
```
If critical issues:
1. Revert to git tag: v1.0-pre-chatbot
2. Restore from tar: sms-backup-20251020-123600.tar.gz
3. Update Twilio webhooks (if needed)
4. Clear Laravel cache
5. Verify agent conversations still work
```

---

## 📝 Open Questions

1. **Templates:** Database or Files for Phase 1?
2. **Landing Page:** What is the purpose and audience?
3. **Admin Panel:** Priority - Full CRUD or just Edit?
4. **Media Hosting:** Keep on CF server or migrate to Laravel?
5. **New Menu Items:** Should admins be able to add beyond 12?
6. **Branding:** Any specific design/colors for admin panel?
7. **Permissions:** Who has admin access to chatbot management?

---

## 🚀 Next Steps

1. ✅ Get answers to open questions
2. ✅ Create TODO list for Phase 1
3. ✅ Start building ChatbotService
4. ✅ Port templates
5. ✅ Integrate with WebhookController
6. ✅ Test with live SMS
7. ✅ Deploy to production
8. ✅ Monitor and iterate

---

**Last Updated:** October 20, 2025  
**Document Owner:** Development Team  
**Review Date:** After Phase 1 completion

