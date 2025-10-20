# ColdFusion Webhook - Phone Number 406-752-4335

**Document Date:** October 20, 2025  
**System:** ColdFusion SMS System  
**Webhook URL:** `http://dash.montanasky.net/sms/smsresponse.cfm`  
**Status:** 🟡 Active - Chatbot API Issues

---

## 📞 Phone Number Configuration

```
Number: +1 406-752-4335
Type: Customer Support Line
Twilio Config: Direct Webhook (No Messaging Service)
Webhook: http://dash.montanasky.net/sms/smsresponse.cfm
Method: POST
```

---

## 🔄 Complete Message Flow

### Step 1: Customer Sends SMS
```
Customer Phone: +1 (406) XXX-XXXX
Message To: +1 406-752-4335
Body: "MENU" (or any text)
```

### Step 2: Twilio Receives & Logs
- Incoming SMS logged in Twilio console
- Direction: Incoming
- Status: Received

### Step 3: Webhook POST
```http
POST http://dash.montanasky.net/sms/smsresponse.cfm
Content-Type: application/x-www-form-urlencoded

From=+14065551234
To=+14067524335
Body=MENU
MessageSid=SMxxxxxxxxxxxxxxxxxx
AccountSid=ACxxxxxxxxxxxxxxxxxx
NumMedia=0
```

### Step 4: smsresponse.cfm Execution

**Line 1:** `<cfinclude template="smsboot.cfm">`

This immediately includes the chatbot engine BEFORE any other processing.

---

## 🤖 Chatbot Processing (smsboot.cfm)

### Decision Tree:

```
┌─────────────────────────────────────────┐
│  Check Message Body                     │
└────────────────┬────────────────────────┘
                 │
        ┌────────┴────────┐
        │                 │
        ▼                 ▼
   ┌─────────┐      ┌──────────┐
   │"MENU"?  │      │Active    │
   │         │      │Session?  │
   └────┬────┘      └────┬─────┘
        │                │
        ▼                ▼
   ┌─────────┐      ┌──────────┐
   │Start    │      │Navigate  │
   │Session  │      │Menu      │
   └────┬────┘      └────┬─────┘
        │                │
        └────────┬───────┘
                 │
                 ▼
        ┌────────────────┐
        │Send Response   │
        │via smsboot.php │
        └────────┬───────┘
                 │
                 ▼
        ┌────────────────┐
        │  <cfabort>     │
        │  STOPS HERE    │
        └────────────────┘
```

### If Body = "MENU":

1. **Database Update:**
   ```sql
   INSERT INTO smsbot (phone, menu, updated_dt)
   VALUES ('4065551234', 'm', NOW())
   ON DUPLICATE KEY UPDATE menu='m', updated_dt=NOW()
   ```

2. **Build Response:**
   ```
   BOT:
   Send for Issue:
    1  for SkyConnect
    2  for DSL
    3  for Cable
    4  for Email
    5  for Outages
    6  for Speedtest
    7  for Payments
    8  for MontanaSkyTV
    9  for Voip Phone
   10 for Plume Wifi
   11 for Fiber GPON
   12 for Point to Points

   Send EXIT to Quit
   [Montana Sky Logo]
   ```

3. **Send via smsboot.php:**
   ```php
   // HTTP POST to internal API
   POST https://admin01.montanasat.net/api/twilio/sms/send
   
   smsData[From] = +14062152048
   smsData[To] = +14065551234
   smsData[Body] = [Menu Response]
   ```

4. **admin01 API → Twilio:**
   - Internal API validates request
   - Calls Twilio API to send SMS
   - Returns 200 OK

5. **Script Execution:**
   ```cfml
   <cfabort>  <!-- Stops all processing -->
   ```
   - ❌ Does NOT continue to rest of smsresponse.cfm
   - ❌ Does NOT save to cat_sms database
   - ❌ Does NOT send email notification

### If Active Bot Session (30 min window):

**Example: User texts "4"**

1. **Retrieve Session:**
   ```sql
   SELECT phone, menu FROM smsbot
   WHERE phone='4065551234'
     AND menu != ''
     AND updated_dt >= DATE_SUB(NOW(), INTERVAL 30 MINUTE)
   ```

2. **Update Menu Path:**
   - Current: `m`
   - User input: `4`
   - New path: `m,4`

3. **Send Submenu:**
   ```
   BOT:
   Email Menu
   
   1  Email Settings
   2  Missing Email
   3  Spamfilter
   
   Send MENU or EXIT
   ```

4. **Update Database & Send:**
   ```sql
   UPDATE smsbot 
   SET menu='m,4', updated_dt=NOW()
   WHERE phone='4065551234'
   ```
   - Send response via smsboot.php
   - Execute `<cfabort>`

---

## 💬 Normal Message Processing

### If NOT a Bot Interaction:

**Script continues to smsresponse.cfm line 2+**

1. **Parse Twilio Data:**
   ```cfml
   <cfset flist="ACCOUNTSID,APIVERSION,BODY,FROM,FROMCITY,
                 FROMCOUNTRY,FROMSTATE,FROMZIP,MESSAGESID,
                 MESSAGINGSERVICESID,NUMMEDIA,NUMSEGMENTS,
                 SMSMESSAGESID,SMSSID,SMSSTATUS,TO,TOCITY,
                 TOCOUNTRY,TOSTATE,TOZIP,MESSAGESTATUS">
   ```

2. **Handle Media Attachments:**
   ```cfml
   <cfif form.nummedia gt 0>
     <cfloop from="1" to="#form.nummedia#" index="medloop">
       <cfset mediaurllist=listappend(mediaurllist, ...)>
       <cfset mediatypelist=listappend(mediatypelist, ...)>
     </cfloop>
   </cfif>
   ```

3. **Save to Database:**
   ```sql
   INSERT INTO cat_sms (
     ACCOUNTSID, APIVERSION, BODY, FROM, TO,
     MESSAGESID, NUMMEDIA, mediaurllist, mediatypelist
   ) VALUES (...)
   ```

4. **Lookup Customer:**
   ```sql
   -- Get last outbound message to this customer
   SELECT * FROM cat_sms 
   WHERE `TO`='[customer_phone]' 
     AND user_id!=0
     AND thetime > DATE_SUB(NOW(), INTERVAL 1 DAY)
   ORDER BY id DESC LIMIT 1
   ```

5. **Determine Email Routing:**
   ```cfml
   <cfset sendtoemail='support@montanasky.net'>
   
   <cfif lastuser.recordcount>
     <cfif lastuser.replies_to_support is 0>
       <!-- Send to agent's email -->
       <cfquery name="cpass" datasource="mtsky-dash">
         SELECT email FROM user_table 
         WHERE id=#lastuser.user_id#
       </cfquery>
       <cfset sendtoemail=cpass.email>
     </cfif>
   </cfif>
   ```

6. **Send Email Notification:**
   ```cfml
   <cfmail to="#sendtoemail#" 
           from="dash-sms@montanasky.net" 
           subject="SMS from #form.from#" 
           type="html">
     From: [Customer Name]
     Message: [Body]
     [Recent Conversation History]
     <a href="[SMS Tool Link]">Open SMS Tool</a>
   </cfmail>
   ```

---

## 🔐 Database Tables Used

### cat_sms (Main Messages)
```sql
-- Stores all NON-BOT messages
-- Bot messages use <cfabort> before saving
```

### smsbot (Bot Sessions)
```sql
CREATE TABLE smsbot (
  phone VARCHAR(10) PRIMARY KEY,
  menu VARCHAR(255),        -- "m", "m,4", "m,4,2"
  updated_dt TIMESTAMP
);

-- Session expires after 30 minutes of inactivity
```

---

## ⚠️ Current Issues

### Issue 1: admin01 API Failure
**Symptoms:**
- Customer texts "MENU" to 406-752-4335
- Twilio receives message ✅
- Webhook fires ✅
- Bot session created in database ✅
- Bot tries to send response ❌
- No response received by customer ❌

**Root Cause:**
```php
// smsboot.php line 46
$url = 'https://admin01.montanasat.net/api/twilio/sms/send';

// This API is not responding or timing out
```

**Impact:**
- Messages not saved to database (due to `<cfabort>`)
- Not visible in Laravel UI
- Customer gets no response
- Poor user experience

**Debug Steps:**
```bash
# Test the API directly
curl -v -X POST https://admin01.montanasat.net/api/twilio/sms/send \
  -d "smsData[From]=+14062152048" \
  -d "smsData[To]=+14065551234" \
  -d "smsData[Body]=Test Message"
```

### Issue 2: Bot Messages Not Logged
**Problem:**
- Chatbot handles message
- Executes `<cfabort>` before database save
- Message exists in Twilio logs only
- No record in cat_sms table

**Solutions:**
1. Save BEFORE calling bot
2. Remove `<cfabort>` and return after bot
3. Implement bot in Laravel (saves first)

---

## 📊 Message Flow Summary

### Bot Interaction:
```
Customer → Twilio → Webhook → smsboot.cfm
    → Create session → Send response → <cfabort>
    ❌ Not saved to DB
    ❌ No email sent
```

### Normal Message:
```
Customer → Twilio → Webhook → smsboot.cfm (no match)
    → smsresponse.cfm → Save to DB → Email notify → Done
    ✅ Saved to DB
    ✅ Email sent
```

---

## 🎯 Recommendations

### Immediate (This Week):
1. ✅ Document this webhook behavior
2. 🔧 Debug admin01 API connectivity
3. 🧪 Test bot functionality thoroughly
4. 📋 Decide: Fix or migrate to Laravel

### Short Term (This Month):
1. Consider saving bot messages to DB
2. Add error handling for API failures
3. Implement retry logic
4. Add logging for troubleshooting

### Long Term (Q1 2026):
1. Migrate chatbot to Laravel
2. Consolidate to single system
3. Improve error handling
4. Add monitoring/alerts

---

## 📞 Support Contacts

**Twilio Console:** https://console.twilio.com  
**ColdFusion Server:** dash.montanasky.net  
**Internal API:** admin01.montanasat.net  
**Database:** mtsky-webkittens (MySQL)

---

**Last Updated:** October 20, 2025  
**Prepared By:** AI Technical Analysis  
**Status:** Active - Requires Attention

