<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New SMS/MMS Message</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: #e5e5ea;
        }
        
        .compose-container {
            max-width: 100%;
            height: 100vh;
            display: flex;
            flex-direction: column;
            background: white;
        }
        
        .compose-header {
            background: #f7f7f8;
            border-bottom: 1px solid #c6c6c8;
            padding: 12px 16px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .compose-header h1 {
            font-size: 17px;
            font-weight: 600;
            color: #333;
            margin: 0;
        }
        
        .phone-input-container {
            padding: 8px 16px;
            background: white;
            border-bottom: 1px solid #e5e5ea;
        }
        
        .phone-input {
            width: 100%;
            border: 1px solid #d1d1d6;
            border-radius: 8px;
            padding: 12px;
            font-size: 17px;
        }
        
        .phone-input:focus {
            outline: none;
            border-color: #007aff;
        }
        
        .messages-area {
            flex: 1;
            overflow-y: auto;
            background: #e5e5ea;
            padding: 16px;
        }
        
        .compose-footer {
            background: #f7f7f8;
            border-top: 1px solid #c6c6c8;
            padding: 12px 16px;
        }
        
        .footer-row {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 8px;
        }
        
        .quick-responses-link {
            color: #007aff;
            text-decoration: none;
            font-size: 15px;
            cursor: pointer;
        }
        
        .send-to-support-btn {
            margin-left: auto;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 15px;
            font-weight: 500;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .send-to-support-btn.active {
            background: #34c759;
            color: white;
        }
        
        .send-to-support-btn.inactive {
            background: #007aff;
            color: white;
        }
        
        .message-input-row {
            display: flex;
            align-items: flex-end;
            gap: 8px;
        }
        
        .message-input {
            flex: 1;
            border: 1px solid #d1d1d6;
            border-radius: 20px;
            padding: 10px 16px;
            font-size: 17px;
            min-height: 36px;
            max-height: 120px;
            resize: none;
        }
        
        .message-input:focus {
            outline: none;
            border-color: #007aff;
        }
        
        .attach-file-btn {
            background: #007aff;
            color: white;
            padding: 8px 16px;
            border-radius: 8px;
            border: none;
            font-size: 15px;
            cursor: pointer;
            white-space: nowrap;
        }
        
        .media-url-input {
            width: 100%;
            border: 1px solid #d1d1d6;
            border-radius: 8px;
            padding: 8px 12px;
            font-size: 15px;
            margin-top: 8px;
        }
        
        .send-btn {
            background: #007aff;
            color: white;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        
        .send-btn:disabled {
            background: #c6c6c8;
            cursor: not-allowed;
        }
        
        .char-count {
            font-size: 13px;
            color: #8e8e93;
            text-align: right;
            margin-top: 4px;
        }
        
        .quick-responses-container {
            display: none;
            margin-top: 12px;
            padding: 12px;
            background: white;
            border-radius: 12px;
            max-height: 300px;
            overflow-y: auto;
        }
        
        .quick-responses-container.visible {
            display: block;
        }
        
        .qr-button {
            display: block;
            width: 100%;
            background: #f2f2f7;
            border: none;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 8px;
            text-align: left;
            cursor: pointer;
            font-size: 15px;
        }
        
        .qr-button:hover {
            background: #e5e5ea;
        }
        
        @media (min-width: 768px) {
            .compose-container {
                max-width: 600px;
                margin: 0 auto;
                height: 100vh;
            }
        }
    </style>
</head>
<body>
    <div class="compose-container">
        <!-- Header -->
        <div class="compose-header">
            <a href="{{ route('conversations.index') }}" style="color: #007aff; text-decoration: none; font-size: 17px;">← Back</a>
            <h1>New SMS-MMS Message</h1>
        </div>
        
        <!-- Phone Number Input -->
        <div class="phone-input-container">
            <input type="tel" 
                   id="phone-number" 
                   class="phone-input" 
                   placeholder="Enter phone number (+1406...)"
                   autocomplete="tel">
        </div>
        
        <!-- Messages Area (empty for new conversation) -->
        <div class="messages-area">
            <div style="text-align: center; color: #8e8e93; padding: 40px 20px;">
                <p style="font-size: 17px;">Enter a phone number above to start a new conversation</p>
            </div>
        </div>
        
        <!-- Footer (Compose Area) -->
        <div class="compose-footer">
            <form id="compose-form" method="POST" action="{{ route('conversations.compose.send') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="to" id="to-field">
                
                <!-- Quick Responses & Send to Support -->
                <div class="footer-row">
                    <a href="#" class="quick-responses-link" id="toggle-qr">⚡ Quick Responses</a>
                    <button type="button" 
                            class="send-to-support-btn inactive" 
                            id="send-to-support-toggle">
                        📧 Send to Support
                    </button>
                    <input type="hidden" name="send_to_support" id="send-to-support-field" value="0">
                </div>
                
                <!-- Quick Responses Container -->
                <div class="quick-responses-container" id="quick-responses"></div>
                
                <!-- Message Input Row -->
                <div class="message-input-row">
                    <button type="button" class="attach-file-btn" onclick="document.getElementById('file-input').click()">
                        Attach File
                    </button>
                    <input type="file" id="file-input" name="media_file" accept="image/*,video/*" style="display: none;">
                    
                    <textarea id="message-input" 
                              name="body" 
                              class="message-input" 
                              placeholder="iMessage"
                              rows="1"
                              maxlength="1600"
                              required></textarea>
                    
                    <button type="submit" class="send-btn" id="send-btn" disabled>
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                            <path d="M2 12L22 2L12 22L10 14L2 12Z" fill="white"/>
                        </svg>
                    </button>
                </div>
                
                <!-- Media URL Input -->
                <input type="url" 
                       name="media_url" 
                       id="media-url" 
                       class="media-url-input" 
                       placeholder="Optional: Media URL (https://...)">
                
                <!-- Character Count -->
                <div class="char-count">
                    <span id="char-count">0</span> / 1600
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // Auto-resize textarea
        const messageInput = document.getElementById('message-input');
        messageInput.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
            
            // Update character count
            document.getElementById('char-count').textContent = this.value.length;
            
            // Enable/disable send button
            updateSendButton();
        });
        
        // Phone number input handling
        const phoneInput = document.getElementById('phone-number');
        const toField = document.getElementById('to-field');
        
        phoneInput.addEventListener('input', function() {
            toField.value = this.value;
            updateSendButton();
        });
        
        function updateSendButton() {
            const phone = phoneInput.value.trim();
            const message = messageInput.value.trim();
            const sendBtn = document.getElementById('send-btn');
            
            if (phone && message) {
                sendBtn.disabled = false;
            } else {
                sendBtn.disabled = true;
            }
        }
        
        // Send to Support toggle
        const sendToSupportBtn = document.getElementById('send-to-support-toggle');
        const sendToSupportField = document.getElementById('send-to-support-field');
        let sendToSupport = false;
        
        sendToSupportBtn.addEventListener('click', function() {
            sendToSupport = !sendToSupport;
            
            if (sendToSupport) {
                this.classList.remove('inactive');
                this.classList.add('active');
                this.innerHTML = '✓ Send to Support';
                sendToSupportField.value = '1';
            } else {
                this.classList.remove('active');
                this.classList.add('inactive');
                this.innerHTML = '📧 Send to Support';
                sendToSupportField.value = '0';
            }
        });
        
        // Quick Responses
        const toggleQR = document.getElementById('toggle-qr');
        const qrContainer = document.getElementById('quick-responses');
        
        toggleQR.addEventListener('click', function(e) {
            e.preventDefault();
            
            if (qrContainer.classList.contains('visible')) {
                qrContainer.classList.remove('visible');
            } else {
                qrContainer.classList.add('visible');
                
                // Load quick responses if not already loaded
                if (qrContainer.innerHTML === '') {
                    loadQuickResponses();
                }
            }
        });
        
        function loadQuickResponses() {
            console.log('Loading Quick Responses...');
            
            fetch('/api/quick-responses')
                .then(response => {
                    console.log('Response status:', response.status);
                    return response.text();
                })
                .then(html => {
                    console.log('HTML received, length:', html.length);
                    
                    // Create temporary container to use jQuery
                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = html;
                    document.body.appendChild(tempDiv);
                    tempDiv.style.display = 'none';
                    
                    // Now use jQuery on the actual DOM element
                    const buttons = $(tempDiv).find('.ai-message-button');
                    console.log('Buttons found:', buttons.length);
                    
                    qrContainer.innerHTML = '';
                    
                    if (buttons.length === 0) {
                        qrContainer.innerHTML = '<p style="color: #f59e0b; padding: 12px;">No quick responses found</p>';
                        return;
                    }
                    
                    buttons.each(function() {
                        const content = $(this).data('content') || '';
                        const title = $(this).text().trim();
                        
                        console.log('Creating button:', title);
                        
                        const newBtn = document.createElement('button');
                        newBtn.className = 'qr-button';
                        newBtn.textContent = title;
                        newBtn.type = 'button';
                        
                        newBtn.addEventListener('click', function() {
                            // Check for <media> tag
                            const mediaMatch = content.match(/<media>(.*?)<\/media>/);
                            let cleanContent = content;
                            
                            if (mediaMatch) {
                                const mediaUrl = mediaMatch[1];
                                cleanContent = content.replace(/<media>.*?<\/media>/g, '').trim();
                                document.getElementById('media-url').value = mediaUrl;
                            }
                            
                            messageInput.value = cleanContent;
                            messageInput.dispatchEvent(new Event('input'));
                            qrContainer.classList.remove('visible');
                        });
                        
                        qrContainer.appendChild(newBtn);
                    });
                    
                    // Remove temporary div
                    document.body.removeChild(tempDiv);
                })
                .catch(error => {
                    console.error('Failed to load quick responses:', error);
                    qrContainer.innerHTML = '<p style="color: #ff3b30; padding: 12px;">Error: ' + error.message + '</p>';
                });
        }
        
        // File input handling
        document.getElementById('file-input').addEventListener('change', function(e) {
            if (e.target.files.length > 0) {
                const file = e.target.files[0];
                const fileName = file.name;
                
                // Show file name in media URL field
                document.getElementById('media-url').placeholder = `File selected: ${fileName}`;
                document.getElementById('media-url').disabled = true;
            }
        });
        
        // Form submission
        document.getElementById('compose-form').addEventListener('submit', function(e) {
            const phone = phoneInput.value.trim();
            if (!phone) {
                e.preventDefault();
                alert('Please enter a phone number');
                return;
            }
        });
    </script>
</body>
</html>

