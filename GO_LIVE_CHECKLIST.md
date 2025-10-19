# 🚀 GO LIVE CHECKLIST - SMS Management System

## ✅ **COMPLETED - Ready for Launch**

### **Infrastructure**
- [x] Laravel production deployed on `mooseweb@208.123.195.10`
- [x] Server running on `http://mooseman.montanasky.net:8001`
- [x] Database migrations completed
- [x] Production `.env` configured
- [x] Git repository up to date

### **Authentication System**
- [x] Laravel Breeze installed
- [x] Users table created
- [x] Employee validation against `db_1257_employees`
- [x] Admin roles working
- [x] Login/logout functional
- [x] Password reset ready
- [x] Production account registered (Frederick Weber)

### **Agent Tracking**
- [x] `fromname` field saves agent name on send
- [x] `toname` field saves customer name
- [x] `custsku` field saves customer SKU
- [x] Agent filter dropdown working
- [x] "My Conversations" filter working

### **"Send to Support" Feature**
- [x] Toggle button styled and working
- [x] `conversation_preferences` table created
- [x] State persists across sessions
- [x] Ready for email integration (Phase 2)

### **Testing Completed**
- [x] Local dev tested thoroughly
- [x] Production registration tested
- [x] Outbound SMS tested from production
- [x] Status callbacks working
- [x] Auto-refresh working
- [x] Agent names saving correctly

---

## ⏳ **WAITING - Twilio Support**

### **Webhook Configuration**
- [ ] **Change MTSKY SMS Messaging Service webhook**
  - **From:** `https://dash.montanasky.net/sms/smsresponse.cfm`
  - **To:** `http://mooseman.montanasky.net:8001/webhook/twilio`
  - **Method:** POST
  - **Waiting on:** Twilio support to show where to edit

---

## 🧪 **POST-WEBHOOK TESTING (After Twilio Updates)**

### **Immediate Tests (5 minutes)**
- [ ] Text 406-215-2048 from personal phone
- [ ] Verify message appears in Laravel conversations
- [ ] Reply from Laravel to personal phone
- [ ] Verify reply is received
- [ ] Check that `toname` is set to "MTSKY" for inbound
- [ ] Verify agent name saved on outbound replies

### **Functional Tests (15 minutes)**
- [ ] Test with multiple phone numbers
- [ ] Test with media attachments (MMS)
- [ ] Test "Send to Support" toggle
- [ ] Test agent filtering
- [ ] Test "Archive to Ticket" feature
- [ ] Test quick response templates
- [ ] Test time filtering (24h, week, month, etc.)

### **Performance Monitoring (1 hour)**
- [ ] Monitor production logs: `tail -f /tmp/laravel_sms.log`
- [ ] Check for any errors
- [ ] Verify auto-refresh working for all agents
- [ ] Check database write performance

---

## 🔄 **ROLLBACK PLAN (If Issues Arise)**

### **Instant Rollback (30 seconds)**
1. Go to Twilio MTSKY SMS Messaging Service
2. Change webhook back to: `https://dash.montanasky.net/sms/smsresponse.cfm`
3. Save
4. ColdFusion immediately takes over
5. **Zero data loss** - all historical messages remain in `cat_sms_dev`

### **What Stays Intact**
- All ColdFusion functionality
- All historical SMS data
- All customer records
- All ticket integrations

---

## 📋 **PHASE 2 - After Successful Go Live**

### **Email Integration (1-2 days)**
When "Send to Support" is enabled:
- [ ] Configure Laravel Mail settings in `.env`
- [ ] Test email sending locally
- [ ] Update `WebhookController::receiveSms()` to check preference
- [ ] Send formatted email to `support@montanasky.net`
- [ ] Include: customer name, phone, message, conversation link
- [ ] Test with real incoming SMS

### **File Upload Enhancement (1 day)**
- [ ] Add file upload button to conversation UI
- [ ] Handle file uploads in `ConversationController::send()`
- [ ] Move files to `public/media`
- [ ] Generate public URLs for Twilio
- [ ] Test MMS with uploaded images

### **Additional Features (Optional)**
- [ ] Email notifications for new conversations
- [ ] SMS templates library
- [ ] Bulk SMS sending
- [ ] Conversation notes/tags
- [ ] Advanced reporting/analytics
- [ ] Export conversations to CSV

---

## 🛡️ **PRODUCTION MONITORING**

### **Daily Checks**
- Check `/tmp/laravel_sms.log` for errors
- Verify server is running: `ps aux | grep "php artisan serve"`
- Check database connection
- Monitor disk space

### **Weekly Checks**
- Review agent activity
- Check "Send to Support" usage
- Review archived tickets
- Check for any stuck messages

### **Commands**
```bash
# SSH to production
ssh mooseweb@208.123.195.10

# Check server status
ps aux | grep "php artisan serve"

# View logs
tail -f /tmp/laravel_sms.log

# Restart server (if needed)
pkill -f "php artisan serve"
cd ~/sms-management-system
nohup php artisan serve --host=0.0.0.0 --port=8001 > /tmp/laravel_sms.log 2>&1 &
```

---

## 📞 **CONTACTS**

### **Twilio Support**
- Dashboard: https://console.twilio.com
- Support: https://support.twilio.com
- Issue: Need to edit MTSKY SMS Messaging Service webhook

### **Production Server**
- SSH: `mooseweb@208.123.195.10`
- URL: `http://mooseman.montanasky.net:8001`
- Project: `/home/mooseweb/sms-management-system`

### **ColdFusion Backup**
- Dashboard: https://dash.montanasky.net/sendsms.cfm
- Webhook: https://dash.montanasky.net/sms/smsresponse.cfm

---

## 🎉 **SUCCESS CRITERIA**

**System is considered LIVE when:**
1. ✅ Incoming SMS appear in Laravel within 1 second
2. ✅ Outbound SMS send successfully from Laravel
3. ✅ Agent names tracked on all messages
4. ✅ No errors in production logs for 1 hour
5. ✅ Multiple agents can use system simultaneously
6. ✅ Auto-refresh working reliably

**Then we can:**
- Decommission ColdFusion SMS UI (keep as read-only backup)
- Train team on new Laravel system
- Implement Phase 2 features (email, uploads, etc.)

---

## 📊 **METRICS TO TRACK**

### **Week 1**
- Number of incoming SMS
- Number of outbound SMS
- Average response time
- Number of active agents
- "Send to Support" usage rate

### **Month 1**
- System uptime %
- Average messages per day
- Agent activity breakdown
- Feature usage statistics
- User feedback/issues

---

**Last Updated:** October 19, 2025
**Status:** ⏳ Waiting on Twilio webhook configuration
**Ready for Launch:** ✅ YES

