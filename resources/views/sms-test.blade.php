<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SMS Test - Phase 1</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 20px;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 2rem;
            margin-bottom: 10px;
        }
        
        .phase-badge {
            display: inline-block;
            background: rgba(255,255,255,0.2);
            padding: 6px 12px;
            border-radius: 15px;
            font-size: 0.85rem;
            margin-top: 10px;
        }
        
        .content {
            padding: 40px;
        }
        
        .back-link {
            display: inline-block;
            margin-bottom: 30px;
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
        
        .test-section {
            background: #f8f9fa;
            padding: 30px;
            border-radius: 12px;
            margin-bottom: 30px;
        }
        
        .test-section h2 {
            color: #333;
            margin-bottom: 10px;
        }
        
        .test-section p {
            color: #666;
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            color: #333;
            font-weight: 600;
            margin-bottom: 8px;
        }
        
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-family: inherit;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }
        
        .hint {
            font-size: 0.9rem;
            color: #666;
            margin-top: 5px;
        }
        
        .btn {
            padding: 12px 24px;
            border-radius: 8px;
            border: none;
            font-weight: 600;
            cursor: pointer;
            font-size: 1rem;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-primary:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
            margin-left: 10px;
        }
        
        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-top: 20px;
            display: none;
        }
        
        .alert-success {
            background: #d1f2eb;
            color: #0c5346;
            border-left: 4px solid #10b981;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }
        
        .alert-info {
            background: #d1ecf1;
            color: #0c5460;
            border-left: 4px solid #17a2b8;
        }
        
        .response-box {
            background: #1e293b;
            color: #e2e8f0;
            padding: 20px;
            border-radius: 8px;
            font-family: 'Monaco', 'Courier New', monospace;
            font-size: 0.9rem;
            margin-top: 20px;
            display: none;
            max-height: 400px;
            overflow-y: auto;
        }
        
        .response-box pre {
            margin: 0;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        
        .loading {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 3px solid rgba(255,255,255,.3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 1s ease-in-out infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        .setup-notice {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        
        .setup-notice h3 {
            color: #856404;
            margin-bottom: 10px;
        }
        
        .setup-notice code {
            background: rgba(0,0,0,0.1);
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Monaco', 'Courier New', monospace;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📱 SMS Test Interface</h1>
            <div class="phase-badge">Phase 1: Logging Only (No Database)</div>
        </div>
        
        <div class="content">
            <a href="/" class="back-link">← Back to Home</a>
            
            <div class="setup-notice">
                <h3>⚙️ Setup Required</h3>
                <p><strong>Before testing, add your Twilio credentials to .env:</strong></p>
                <p>
                    <code>TWILIO_ACCOUNT_SID</code>=your_account_sid<br>
                    <code>TWILIO_AUTH_TOKEN</code>=your_auth_token<br>
                    <code>TWILIO_FROM_NUMBER</code>=+14062152048
                </p>
                <p style="margin-top: 10px;">Messages will be logged to <code>storage/logs/laravel.log</code></p>
            </div>
            
            <!-- Test Connection -->
            <div class="test-section">
                <h2>1️⃣ Test Twilio Connection</h2>
                <p>Verify your Twilio credentials are configured correctly</p>
                <button class="btn btn-primary" onclick="testConnection()">Test Connection</button>
                <div id="connection-alert" class="alert"></div>
                <div id="connection-response" class="response-box"></div>
            </div>
            
            <!-- Send Test SMS -->
            <div class="test-section">
                <h2>2️⃣ Send Test SMS</h2>
                <p>Send a quick test message to verify SMS sending works</p>
                
                <form id="test-form" onsubmit="sendTestSms(event)">
                    <div class="form-group">
                        <label for="test-phone">Your Phone Number</label>
                        <input type="tel" id="test-phone" name="to" required placeholder="+14065551234">
                        <div class="hint">Format: +1 followed by 10 digits</div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary" id="test-btn">
                        Send Test SMS
                    </button>
                </form>
                
                <div id="test-alert" class="alert"></div>
                <div id="test-response" class="response-box"></div>
            </div>
            
            <!-- Send Custom SMS/MMS -->
            <div class="test-section">
                <h2>3️⃣ Send Custom SMS/MMS</h2>
                <p>Send a custom message with optional media attachment</p>
                
                <form id="custom-form" onsubmit="sendCustomSms(event)" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="custom-phone">To Phone Number</label>
                        <input type="tel" id="custom-phone" name="to" required placeholder="+14065551234">
                    </div>
                    
                    <div class="form-group">
                        <label for="custom-message">Message</label>
                        <textarea id="custom-message" name="body" required placeholder="Your message here..."></textarea>
                        <div class="hint" id="char-count">0 / 1600 characters</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="media-url">Media URL (Optional)</label>
                        <input type="url" id="media-url" name="media_url" placeholder="https://example.com/image.jpg">
                        <div class="hint">Enter a public image URL...</div>
                    </div>
                    
                    <div style="margin: 15px 0; text-align: center; color: #666; font-weight: bold;">— OR —</div>
                    
                    <div class="form-group">
                        <label for="media-file">Upload Image File (Local)</label>
                        <input type="file" id="media-file" name="media_file" accept="image/*,video/*,.pdf" onchange="handleFileSelect(this)">
                        <div class="hint" id="file-hint">Upload from your computer (JPG, PNG, GIF, MP4, PDF - Max 5MB)</div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary" id="custom-btn">
                        Send SMS/MMS
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="clearForm()">
                        Clear
                    </button>
                </form>
                
                <div id="custom-alert" class="alert"></div>
                <div id="custom-response" class="response-box"></div>
            </div>
            
            <!-- Test Media URLs -->
            <div class="test-section">
                <h2>🖼️ Test MMS with Media</h2>
                <p>Try these publicly accessible test images for MMS testing:</p>
                
                <div style="margin-top: 15px;">
                    <strong>Quick Test Images (Click "Use This"):</strong><br>
                    <div style="margin-top: 10px; background: white; padding: 15px; border-radius: 8px;">
                        <p style="margin-bottom: 12px; font-family: monospace; font-size: 0.9rem;">
                            <strong>🌄 Random Photo (Changes each time):</strong><br>
                            <code style="font-size: 0.85rem;">https://picsum.photos/500/400</code>
                            <button onclick="document.getElementById('media-url').value = 'https://picsum.photos/500/400'" style="margin-left: 10px; padding: 6px 12px; border-radius: 4px; border: 1px solid #667eea; background: #667eea; color: white; cursor: pointer; font-weight: bold;">Use This</button>
                        </p>
                        <p style="margin-bottom: 12px; font-family: monospace; font-size: 0.9rem;">
                            <strong>🖼️ Sample Image (Landscape):</strong><br>
                            <code style="font-size: 0.85rem;">https://dummyimage.com/600x400/0066cc/ffffff.png&text=Montana+Sky+Test</code>
                            <button onclick="document.getElementById('media-url').value = 'https://dummyimage.com/600x400/0066cc/ffffff.png&text=Montana+Sky+Test'" style="margin-left: 10px; padding: 6px 12px; border-radius: 4px; border: 1px solid #667eea; background: #667eea; color: white; cursor: pointer; font-weight: bold;">Use This</button>
                        </p>
                        <p style="margin-bottom: 8px; font-family: monospace; font-size: 0.9rem;">
                            <strong>📱 Sample Image (Portrait):</strong><br>
                            <code style="font-size: 0.85rem;">https://dummyimage.com/400x600/10b981/ffffff.png&text=MMS+Working</code>
                            <button onclick="document.getElementById('media-url').value = 'https://dummyimage.com/400x600/10b981/ffffff.png&text=MMS+Working'" style="margin-left: 10px; padding: 6px 12px; border-radius: 4px; border: 1px solid #667eea; background: #667eea; color: white; cursor: pointer; font-weight: bold;">Use This</button>
                        </p>
                    </div>
                    
                    <div style="margin-top: 15px; background: #d1f2eb; padding: 12px; border-radius: 6px; border-left: 4px solid #10b981;">
                        <strong>✅ All URLs tested and working!</strong> Media must be publicly accessible for Twilio to fetch.
                    </div>
                </div>
            </div>
            
            <!-- Log Viewer -->
            <div class="test-section">
                <h2>📋 How to View Results</h2>
                <p>Messages are logged to <code>storage/logs/laravel.log</code></p>
                <p style="margin-top: 10px;"><strong>Watch logs in real-time:</strong></p>
                <div class="response-box" style="display: block;">
<pre>tail -f storage/logs/laravel.log</pre>
                </div>
                <p style="margin-top: 15px;"><strong>When you receive an SMS:</strong> Reply to it (or send with media), and you'll see it appear in the logs instantly!</p>
            </div>
        </div>
    </div>
    
    <script>
        // Character counter
        document.getElementById('custom-message').addEventListener('input', function(e) {
            const count = e.target.value.length;
            document.getElementById('char-count').textContent = `${count} / 1600 characters`;
        });
        
        // Test Twilio connection
        async function testConnection() {
            const alert = document.getElementById('connection-alert');
            const response = document.getElementById('connection-response');
            
            alert.style.display = 'none';
            response.style.display = 'none';
            
            try {
                const res = await fetch('/api/sms/test-connection');
                const data = await res.json();
                
                if (data.success) {
                    showAlert(alert, 'success', '✅ Twilio connection successful!');
                    showResponse(response, data);
                } else {
                    showAlert(alert, 'error', '❌ Connection failed: ' + data.error);
                    showResponse(response, data);
                }
            } catch (error) {
                showAlert(alert, 'error', '❌ Error: ' + error.message);
            }
        }
        
        // Send test SMS
        async function sendTestSms(event) {
            event.preventDefault();
            
            const form = event.target;
            const alert = document.getElementById('test-alert');
            const response = document.getElementById('test-response');
            const btn = document.getElementById('test-btn');
            
            alert.style.display = 'none';
            response.style.display = 'none';
            btn.disabled = true;
            btn.innerHTML = '<span class="loading"></span> Sending...';
            
            try {
                const formData = new FormData(form);
                const res = await fetch('/api/sms/send-test', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: formData
                });
                
                const data = await res.json();
                
                if (data.success) {
                    showAlert(alert, 'success', '✅ ' + data.message);
                    showResponse(response, data);
                } else {
                    showAlert(alert, 'error', '❌ ' + data.message + ': ' + (data.error || ''));
                    showResponse(response, data);
                }
            } catch (error) {
                showAlert(alert, 'error', '❌ Error: ' + error.message);
            } finally {
                btn.disabled = false;
                btn.textContent = 'Send Test SMS';
            }
        }
        
        // Handle file selection
        function handleFileSelect(input) {
            const fileHint = document.getElementById('file-hint');
            const mediaUrl = document.getElementById('media-url');
            
            if (input.files && input.files[0]) {
                const file = input.files[0];
                const fileSizeMB = (file.size / 1024 / 1024).toFixed(2);
                
                // Check file size
                if (file.size > 5 * 1024 * 1024) { // 5MB limit
                    fileHint.textContent = `⚠️ File too large (${fileSizeMB}MB). Max 5MB.`;
                    fileHint.style.color = '#dc3545';
                    input.value = '';
                    return;
                }
                
                // Show file info
                fileHint.textContent = `✅ Selected: ${file.name} (${fileSizeMB}MB)`;
                fileHint.style.color = '#10b981';
                
                // Disable URL input when file is selected
                mediaUrl.disabled = true;
                mediaUrl.style.opacity = '0.5';
            } else {
                fileHint.textContent = 'Upload from your computer (JPG, PNG, GIF, MP4, PDF - Max 5MB)';
                fileHint.style.color = '#666';
                mediaUrl.disabled = false;
                mediaUrl.style.opacity = '1';
            }
        }
        
        // Send custom SMS
        async function sendCustomSms(event) {
            event.preventDefault();
            
            const form = event.target;
            const alert = document.getElementById('custom-alert');
            const response = document.getElementById('custom-response');
            const btn = document.getElementById('custom-btn');
            
            alert.style.display = 'none';
            response.style.display = 'none';
            btn.disabled = true;
            btn.innerHTML = '<span class="loading"></span> Sending...';
            
            try {
                const formData = new FormData(form);
                
                // Note: FormData automatically handles file uploads
                const res = await fetch('/api/sms/send', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: formData
                });
                
                const data = await res.json();
                
                if (data.success) {
                    showAlert(alert, 'success', '✅ SMS sent successfully!');
                    showResponse(response, data);
                    form.reset();
                    document.getElementById('char-count').textContent = '0 / 1600 characters';
                    document.getElementById('file-hint').textContent = 'Upload from your computer (JPG, PNG, GIF, MP4, PDF - Max 5MB)';
                    document.getElementById('media-url').disabled = false;
                    document.getElementById('media-url').style.opacity = '1';
                } else {
                    showAlert(alert, 'error', '❌ Failed: ' + (data.error || data.message));
                    showResponse(response, data);
                }
            } catch (error) {
                showAlert(alert, 'error', '❌ Error: ' + error.message);
            } finally {
                btn.disabled = false;
                btn.textContent = 'Send SMS/MMS';
            }
        }
        
        function showAlert(element, type, message) {
            element.className = 'alert alert-' + type;
            element.textContent = message;
            element.style.display = 'block';
        }
        
        function showResponse(element, data) {
            element.style.display = 'block';
            element.innerHTML = '<pre>' + JSON.stringify(data, null, 2) + '</pre>';
        }
        
        function clearForm() {
            document.getElementById('custom-form').reset();
            document.getElementById('char-count').textContent = '0 / 1600 characters';
            document.getElementById('custom-alert').style.display = 'none';
            document.getElementById('custom-response').style.display = 'none';
        }
        
        // Helper function to populate with test image
        function useTestImage() {
            document.getElementById('media-url').value = 'https://picsum.photos/400/300';
            alert('Test image URL added! This will send as MMS.');
        }
    </script>
</body>
</html>

