# 📎 MMS Testing Guide

## Testing Media Attachments (MMS)

The SMS Management System supports sending and receiving MMS (Multimedia Messaging Service) with images, videos, and PDFs.

---

## 🔧 Sending MMS

### Via Web Interface

1. **Go to:** http://localhost:8001/send
2. **Scroll to:** Section 3 - "Send Custom SMS/MMS"
3. **Fill in:**
   - Phone number
   - Message text
   - **Media URL** (this is the new field!)
4. **Click:** "Send SMS/MMS"

### Quick Test URLs

Use these publicly accessible URLs for testing:

**Random Photo:**
```
https://picsum.photos/400/300
```

**Placeholder Image:**
```
https://via.placeholder.com/300x200.png?text=Test+MMS
```

**Your Own Image:**
- Upload an image to your server
- Or use any publicly accessible image URL

---

## 📨 Receiving MMS

### Setup Webhook (via ngrok)

```bash
# Start ngrok
ngrok http 8001

# Copy the HTTPS URL (e.g., https://abc123.ngrok.io)
# Configure in Twilio Console:
# Phone Numbers > Your Number > Messaging
# Set webhook to: https://abc123.ngrok.io/webhook/twilio
```

### Send MMS to Your Twilio Number

1. **From your phone:** Send a picture message to `+14062152048`
2. **Watch terminal logs:** You'll see:

```
═══════════════════════════════════════════════
📱 NEW SMS RECEIVED
═══════════════════════════════════════════════
From: +14065551234
To: +14062152048
Message: Check out this photo!
📎 Media: 1 attachment(s)
   1. image/jpeg - https://api.twilio.com/2010-04-01/Accounts/.../Media/...
Time: 2025-10-16 11:45:00
═══════════════════════════════════════════════
```

---

## 🧪 Testing Scenarios

### Test 1: SMS Only (No Media)
- ✅ Send message without media URL
- ✅ Should deliver as regular SMS

### Test 2: SMS with Image
- ✅ Add image URL in "Media URL" field
- ✅ Should deliver as MMS with embedded image

### Test 3: Receive MMS
- ✅ Send picture message from your phone
- ✅ Check terminal logs for media URL
- ✅ Check `storage/logs/laravel.log` for full details

### Test 4: Multiple Media Types
Try different content types:
- **Image:** `.jpg`, `.png`, `.gif`
- **PDF:** `.pdf` documents
- **Video:** `.mp4`, `.mov` (Note: may have size limits)

---

## 📊 What Gets Logged

### Outbound MMS
```json
{
  "to": "+14065551234",
  "from": "+14062152048",
  "message_sid": "MMxxxxxxxxxx",
  "status": "queued",
  "body": "Here's the image!",
  "media_url": "https://example.com/image.jpg"
}
```

### Inbound MMS
```json
{
  "from": "+14065551234",
  "to": "+14062152048",
  "body": "Check this out!",
  "num_media": 1,
  "media_urls": [
    {
      "url": "https://api.twilio.com/.../Media/MExxxx",
      "content_type": "image/jpeg"
    }
  ]
}
```

---

## 💡 Important Notes

### Media URL Requirements:
- ✅ Must be **publicly accessible** (no localhost, no auth required)
- ✅ Must be **HTTPS** (recommended)
- ✅ Common formats: JPG, PNG, GIF, PDF, MP4
- ✅ Size limits: Usually 5MB per message (carrier dependent)

### Twilio Media Storage:
- Twilio hosts received media for **7 days**
- After 7 days, media URLs expire
- In Phase 2+, we'll download and store media locally

### Cost Considerations:
- **SMS:** ~$0.0079 per message
- **MMS:** ~$0.0200 per message (varies by carrier)
- MMS is about 2.5x more expensive than SMS

---

## 🐛 Troubleshooting

### Error: "Unable to fetch media"
- Check URL is publicly accessible
- Verify URL starts with `http://` or `https://`
- Test URL in browser first

### Media not appearing in message
- Some carriers may not support all media types
- Check file size (keep under 1MB for best results)
- Try a different image format

### Receiving MMS but no media URL
- Check ngrok is running
- Verify webhook is configured correctly
- Check `storage/logs/laravel.log` for full payload

---

## ✅ Success Checklist

- [ ] Can send SMS without media
- [ ] Can send MMS with image URL
- [ ] Can receive SMS
- [ ] Can receive MMS with attachments
- [ ] Media URLs appear in logs
- [ ] Terminal shows media attachments with emoji 📎

---

## 🚀 Next Steps (Phase 2+)

Once MMS testing is complete, Phase 2 will add:
- **Media Storage:** Download and save media files locally
- **Media Gallery:** View received images in conversation history
- **File Uploads:** Upload files directly (instead of URLs)
- **Media Preview:** Display images in the UI
- **Media Archive:** Long-term storage beyond 7 days

---

**Status:** MMS support ready for testing! 📎✨

