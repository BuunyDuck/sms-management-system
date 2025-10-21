<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New SMS/MMS Message</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: #e5e5ea;
            height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .compose-container {
            max-width: 100%;
            height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .compose-header {
            background: white;
            padding: 12px 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            gap: 15px;
            flex-shrink: 0;
            flex-wrap: wrap;
        }
        
        .compose-header h1 {
            font-size: 17px;
            font-weight: 600;
            color: #333;
            margin: 0;
        }
        
        .back-button {
            text-decoration: none;
            color: #007aff;
            font-size: 15px;
            font-weight: 500;
            white-space: nowrap;
        }
        
        .back-button:hover {
            opacity: 0.7;
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
            background: white;
            padding: 15px 20px;
            border-top: 1px solid #e5e5ea;
            flex-shrink: 0;
        }
        
        .compose-form {
            display: flex;
            gap: 10px;
            align-items: flex-end;
        }
        
        .compose-input-wrapper {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .compose-input {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #d1d1d6;
            border-radius: 20px;
            font-size: 14px;
            font-family: inherit;
            resize: none;
            max-height: 100px;
            min-height: 40px;
        }
        
        .compose-input:focus {
            outline: none;
            border-color: #007aff;
        }
        
        .media-url-input {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #d1d1d6;
            border-radius: 15px;
            font-size: 12px;
            font-family: inherit;
        }
        
        .media-url-input:focus {
            outline: none;
            border-color: #007aff;
        }
        
        .send-button {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #007aff;
            border: none;
            color: white;
            font-size: 18px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
            flex-shrink: 0;
        }
        
        .send-button:hover {
            background: #0051d5;
            transform: scale(1.05);
        }
        
        .send-button:active {
            transform: scale(0.95);
        }
        
        .send-button:disabled {
            background: #d1d1d6;
            cursor: not-allowed;
            transform: scale(1);
        }
        
        .char-count {
            font-size: 11px;
            color: #8e8e93;
            text-align: right;
        }
        
        /* Quick Response Buttons - Match conversation page styling */
        #ai-message-include-btns {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-top: 10px;
        }

        #ai-message-include-btns div {
            padding: 12px 16px !important;
            font-size: 14px !important;
            font-weight: 500 !important;
            background: #007aff !important;
            color: white !important;
            border-radius: 8px !important;
            text-align: center !important;
            cursor: pointer !important;
            border: none !important;
            transition: all 0.2s !important;
            min-height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        #ai-message-include-btns div:hover {
            background: #0051d5 !important;
            transform: scale(1.02);
        }

        #ai-message-include-btns div:active {
            transform: scale(0.98);
        }
        
        /* Responsive Styles - Match conversation page */
        @media (max-width: 768px) {
            .compose-header {
                gap: 10px;
                padding: 10px 15px;
            }
            
            .compose-header h1 {
                font-size: 14px;
            }
        }
        
        @media (max-width: 480px) {
            .compose-header {
                gap: 8px;
                padding: 8px 12px;
            }
        }
        
        /* Smooth scrolling */
        .messages-area {
            scroll-behavior: smooth;
        }
    </style>
</head>
<body>
    <div class="compose-container">
        <!-- Header -->
        <div class="compose-header">
            <a href="{{ route('conversations.index') }}" class="back-button">← Back</a>
            <span style="font-size: 28px; line-height: 1;">🚀</span>
            <h1>New SMS-MMS Message</h1>
            
            <!-- Notification Bell -->
            <div style="position: relative; margin-left: auto;" id="notification-container">
                <button id="notification-bell" style="position: relative; padding: 8px; background: none; border: none; cursor: pointer; color: #4a5568;">
                    <svg style="width: 22px; height: 22px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    <span id="notification-badge" style="display: none; position: absolute; top: 0; right: 0; background: #ef4444; color: white; border-radius: 9999px; padding: 2px 6px; font-size: 10px; font-weight: bold;">0</span>
                </button>
                
                <!-- Notification Dropdown -->
                <div id="notification-dropdown" style="display: none; position: absolute; right: 0; margin-top: 8px; width: 380px; max-width: 90vw; background: white; border-radius: 8px; box-shadow: 0 10px 40px rgba(0,0,0,0.2); border: 1px solid #e2e8f0; z-index: 10000; max-height: 400px; overflow-y: auto;">
                    <div style="padding: 15px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
                        <h3 style="font-size: 16px; font-weight: 600; color: #2d3748; margin: 0;">Notifications</h3>
                        <div style="display: flex; gap: 10px;">
                            <button id="mark-all-read-btn" style="font-size: 12px; color: #667eea; background: none; border: none; cursor: pointer;">Mark all read</button>
                            <button id="sound-toggle-btn" style="font-size: 16px; background: none; border: none; cursor: pointer;">
                                <span id="sound-on-icon">🔔</span>
                                <span id="sound-off-icon" style="display: none;">🔕</span>
                            </button>
                        </div>
                    </div>
                    <div id="notification-list">
                        <div style="padding: 20px; text-align: center; color: #718096;">No new notifications</div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Flash Messages -->
        @if(session('info'))
            <div style="background: #007aff; color: white; padding: 12px 16px; text-align: center; font-size: 15px;">
                {{ session('info') }}
            </div>
        @endif
        
        @if(session('success'))
            <div style="background: #34c759; color: white; padding: 12px 16px; text-align: center; font-size: 15px;">
                {{ session('success') }}
            </div>
        @endif
        
        @if(session('error'))
            <div style="background: #ff3b30; color: white; padding: 12px 16px; text-align: center; font-size: 15px;">
                {{ session('error') }}
            </div>
        @endif
        
        <!-- Phone Number Input -->
        <div class="phone-input-container">
            <input type="tel" 
                   id="phone-number" 
                   class="phone-input" 
                   placeholder="Enter phone number (+1406...)"
                   value="{{ session('prefill_number', old('to')) }}"
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
            <!-- Quick Responses Container -->
            <div class="quick-responses-container" id="quick-responses" style="display: none; padding: 10px; background: #f8f8f8; border-bottom: 1px solid #d1d1d6; max-height: 200px; overflow-y: auto;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <strong style="font-size: 13px; color: #333;">📋 Quick Responses</strong>
                    <a href="#" onclick="document.getElementById('quick-responses').style.display='none'; return false;" style="color: #007aff; text-decoration: none; font-size: 12px;">Hide</a>
                </div>
                <div id="quick-response-content" style="font-size: 12px;">
                    Loading...
                </div>
            </div>
            
            <form id="compose-form" method="POST" action="{{ route('conversations.compose.send') }}" enctype="multipart/form-data" class="compose-form">
                @csrf
                <input type="hidden" name="to" id="to-field">
                <input type="hidden" name="send_to_support" id="send-to-support-field" value="0">
                <input type="file" id="file-input" name="media_file" accept="image/*,video/*" style="display: none;">
                
                <div class="compose-input-wrapper">
                    <!-- Quick Responses, Attach File, Send to Support Row -->
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; gap: 10px;">
                        <div style="display: flex; gap: 12px; align-items: center;">
                            <a href="#" onclick="document.getElementById('quick-responses').style.display='block'; return false;" style="color: #007aff; text-decoration: none; font-size: 12px; font-weight: 500;">⚡ Quick Responses</a>
                            <a href="#" onclick="document.getElementById('file-input').click(); return false;" style="color: #007aff; text-decoration: none; font-size: 12px; font-weight: 500;">📎 Attach File</a>
                        </div>
                        <button type="button" id="send-to-support-toggle" onclick="toggleSendToSupport()" style="
                            background: linear-gradient(135deg, #007aff 0%, #0051d5 100%);
                            color: white;
                            border: none;
                            padding: 8px 16px;
                            border-radius: 20px;
                            font-size: 13px;
                            font-weight: 600;
                            cursor: pointer;
                            transition: all 0.3s;
                            display: flex;
                            align-items: center;
                            gap: 6px;
                            box-shadow: 0 2px 8px rgba(0, 122, 255, 0.3);
                        ">
                            <span id="support-icon">📧</span>
                            <span>Send to Support</span>
                            <span id="support-check" style="display: none; font-size: 16px;">✓</span>
                        </button>
                    </div>
                    
                    <!-- Message Textarea -->
                    <textarea id="message-input" 
                              name="body" 
                              class="compose-input" 
                              placeholder="iMessage"
                              rows="1"
                              maxlength="1600"
                              required></textarea>
                    
                    <!-- Media URL Input -->
                    <input type="url" 
                           name="media_url" 
                           id="media-url" 
                           class="media-url-input" 
                           placeholder="Optional: Media URL (https://...)">
                    
                    <!-- Character Count -->
                    <div class="char-count" id="char-count">0 / 1600</div>
                </div>
                
                <!-- Send Button -->
                <button type="submit" class="send-button" id="send-btn" disabled>
                    ↑
                </button>
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
        
        // Initialize: If phone number is pre-filled, populate hidden field
        if (phoneInput.value) {
            toField.value = phoneInput.value;
            updateSendButton();
        }
        
        // Send to Support toggle (match conversation page)
        let sendToSupportEnabled = false;
        
        function toggleSendToSupport() {
            sendToSupportEnabled = !sendToSupportEnabled;
            
            const btn = document.getElementById('send-to-support-toggle');
            const icon = document.getElementById('support-icon');
            const check = document.getElementById('support-check');
            const field = document.getElementById('send-to-support-field');
            
            if (sendToSupportEnabled) {
                // Active state - show checkmark
                btn.style.background = 'linear-gradient(135deg, #34c759 0%, #2da846 100%)';
                btn.style.boxShadow = '0 2px 8px rgba(52, 199, 89, 0.4)';
                icon.style.display = 'none';
                check.style.display = 'inline';
                field.value = '1';
            } else {
                // Inactive state - show email icon
                btn.style.background = 'linear-gradient(135deg, #007aff 0%, #0051d5 100%)';
                btn.style.boxShadow = '0 2px 8px rgba(0, 122, 255, 0.3)';
                icon.style.display = 'inline';
                check.style.display = 'none';
                field.value = '0';
            }
        }
        
        // Load Quick Responses on page load (match conversation page approach)
        fetch('/api/quick-responses')
            .then(response => response.text())
            .then(html => {
                document.getElementById('quick-response-content').innerHTML = html;
                
                // Fix button clicks to work with our textarea and handle <media> tags
                document.querySelectorAll('#ai-message-include-btns div[onclick]').forEach(btn => {
                    // Extract the ID from the onclick: $('#ID').data('content')
                    const onclickAttr = btn.getAttribute('onclick');
                    const contentIdMatch = onclickAttr.match(/\$\('#(\w+)'\)\.data\('content'\)/);
                    
                    if (!contentIdMatch) return; // Skip if we can't parse it
                    
                    const contentId = contentIdMatch[1];
                    
                    // Replace the onclick with our custom handler
                    btn.onclick = function(e) {
                        e.preventDefault();
                        
                        // Get content from the hidden div
                        const hiddenDiv = document.getElementById(contentId);
                        if (hiddenDiv) {
                            let content = hiddenDiv.getAttribute('data-content');
                            
                            // Check for <media> tag
                            const mediaMatch = content.match(/<media>(.*?)<\/media>/);
                            
                            if (mediaMatch) {
                                // Extract media URL
                                const mediaUrl = mediaMatch[1];
                                
                                // Remove <media> tag from message
                                content = content.replace(/<media>.*?<\/media>/g, '').trim();
                                
                                // Populate media URL field
                                document.getElementById('media-url').value = mediaUrl;
                            }
                            
                            // Populate message textarea
                            messageInput.value = content;
                            messageInput.style.height = 'auto';
                            messageInput.style.height = Math.min(messageInput.scrollHeight, 100) + 'px';
                            
                            // Update character count
                            document.getElementById('char-count').textContent = content.length + ' / 1600';
                            
                            // Enable send button
                            updateSendButton();
                            
                            // Hide quick responses
                            document.getElementById('quick-responses').style.display = 'none';
                        }
                        return false;
                    };
                });
            })
            .catch(error => {
                document.getElementById('quick-response-content').innerHTML = '<span style="color: #999;">Failed to load quick responses</span>';
                console.error('Error loading quick responses:', error);
            });
        
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

        // Notification System
        const NotificationSystem = {
            lastNotificationCount: 0,
            soundEnabled: localStorage.getItem('notificationSoundEnabled') !== 'false',
            pollInterval: null,

            init() {
                this.setupEventListeners();
                this.updateSoundIcon();
                this.fetchNotifications();
                this.startPolling();
            },

            setupEventListeners() {
                document.getElementById('notification-bell')?.addEventListener('click', (e) => {
                    e.stopPropagation();
                    this.toggleDropdown();
                });

                document.addEventListener('click', (e) => {
                    const container = document.getElementById('notification-container');
                    if (container && !container.contains(e.target)) {
                        this.closeDropdown();
                    }
                });

                document.getElementById('mark-all-read-btn')?.addEventListener('click', () => {
                    this.markAllAsRead();
                });

                document.getElementById('sound-toggle-btn')?.addEventListener('click', () => {
                    this.toggleSound();
                });
            },

            toggleDropdown() {
                const dropdown = document.getElementById('notification-dropdown');
                if (dropdown) {
                    dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';
                }
            },

            closeDropdown() {
                const dropdown = document.getElementById('notification-dropdown');
                if (dropdown) {
                    dropdown.style.display = 'none';
                }
            },

            async fetchNotifications() {
                try {
                    const response = await fetch('/api/notifications', {
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        }
                    });

                    if (!response.ok) throw new Error('Failed to fetch notifications');

                    const notifications = await response.json();
                    this.updateUI(notifications);

                    if (notifications.length > this.lastNotificationCount && this.lastNotificationCount > 0 && this.soundEnabled) {
                        this.playSound();
                    }

                    this.lastNotificationCount = notifications.length;
                } catch (error) {
                    console.error('Error fetching notifications:', error);
                }
            },

            updateUI(notifications) {
                const badge = document.getElementById('notification-badge');
                const list = document.getElementById('notification-list');

                if (notifications.length > 0) {
                    badge.textContent = notifications.length;
                    badge.style.display = 'inline-block';
                } else {
                    badge.style.display = 'none';
                }

                if (notifications.length === 0) {
                    list.innerHTML = '<div style="padding: 20px; text-align: center; color: #718096;">No new notifications</div>';
                } else {
                    list.innerHTML = notifications.map(n => this.createNotificationHTML(n)).join('');
                    this.attachNotificationListeners();
                }
            },

            createNotificationHTML(notification) {
                const customerName = notification.customer_name || 'Unknown';
                const preview = notification.message_preview || '';
                const timeAgo = this.formatTimeAgo(notification.created_at);

                return `
                    <div style="padding: 15px; border-bottom: 1px solid #e2e8f0; cursor: pointer; transition: background 0.2s;" 
                         class="notification-item" 
                         data-id="${notification.id}" 
                         data-phone="${notification.phone_number}"
                         onmouseover="this.style.background='#f7fafc'"
                         onmouseout="this.style.background='white'">
                        <div style="display: flex; justify-content: space-between; align-items: start;">
                            <div style="flex: 1;">
                                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 5px;">
                                    <span style="font-size: 14px; font-weight: 600; color: #2d3748;">📱 ${this.escapeHtml(customerName)}</span>
                                    <span style="font-size: 11px; color: #a0aec0;">${timeAgo}</span>
                                </div>
                                <div style="font-size: 13px; color: #4a5568; margin-bottom: 5px;">${this.escapeHtml(preview)}</div>
                                <div style="font-size: 11px; color: #a0aec0;">${notification.phone_number}</div>
                            </div>
                            <button class="dismiss-btn" 
                                    data-id="${notification.id}" 
                                    style="background: none; border: none; color: #cbd5e0; cursor: pointer; padding: 5px; font-size: 18px; line-height: 1;"
                                    onmouseover="this.style.color='#4a5568'"
                                    onmouseout="this.style.color='#cbd5e0'"
                                    title="Dismiss">✕</button>
                        </div>
                    </div>
                `;
            },

            attachNotificationListeners() {
                document.querySelectorAll('.notification-item').forEach(item => {
                    item.addEventListener('click', (e) => {
                        if (e.target.classList.contains('dismiss-btn')) return;
                        
                        const id = item.dataset.id;
                        const phone = item.dataset.phone;
                        
                        this.markAsRead(id);
                        window.location.href = `/conversation/${phone.replace('+', '')}`;
                    });
                });

                document.querySelectorAll('.dismiss-btn').forEach(btn => {
                    btn.addEventListener('click', (e) => {
                        e.stopPropagation();
                        const id = btn.dataset.id;
                        this.markAsRead(id);
                    });
                });
            },

            async markAsRead(id) {
                try {
                    const response = await fetch(`/api/notifications/${id}/read`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        }
                    });

                    if (response.ok) {
                        this.fetchNotifications();
                    }
                } catch (error) {
                    console.error('Error marking notification as read:', error);
                }
            },

            async markAllAsRead() {
                try {
                    const response = await fetch('/api/notifications/read-all', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        }
                    });

                    if (response.ok) {
                        this.fetchNotifications();
                    }
                } catch (error) {
                    console.error('Error marking all as read:', error);
                }
            },

            toggleSound() {
                this.soundEnabled = !this.soundEnabled;
                localStorage.setItem('notificationSoundEnabled', this.soundEnabled);
                this.updateSoundIcon();
            },

            updateSoundIcon() {
                const onIcon = document.getElementById('sound-on-icon');
                const offIcon = document.getElementById('sound-off-icon');
                
                if (this.soundEnabled) {
                    if (onIcon) onIcon.style.display = 'inline';
                    if (offIcon) offIcon.style.display = 'none';
                } else {
                    if (onIcon) onIcon.style.display = 'none';
                    if (offIcon) offIcon.style.display = 'inline';
                }
            },

            playSound() {
                try {
                    const audioContext = new (window.AudioContext || window.webkitAudioContext)();
                    const oscillator = audioContext.createOscillator();
                    const gainNode = audioContext.createGain();

                    oscillator.connect(gainNode);
                    gainNode.connect(audioContext.destination);

                    oscillator.frequency.value = 800;
                    oscillator.type = 'sine';

                    gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
                    gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.5);

                    oscillator.start(audioContext.currentTime);
                    oscillator.stop(audioContext.currentTime + 0.5);
                } catch (error) {
                    console.error('Error playing sound:', error);
                }
            },

            startPolling() {
                this.pollInterval = setInterval(() => {
                    this.fetchNotifications();
                }, 30000);
            },

            formatTimeAgo(dateString) {
                const date = new Date(dateString);
                const seconds = Math.floor((new Date() - date) / 1000);

                if (seconds < 60) return 'just now';
                if (seconds < 3600) return `${Math.floor(seconds / 60)}m ago`;
                if (seconds < 86400) return `${Math.floor(seconds / 3600)}h ago`;
                return `${Math.floor(seconds / 86400)}d ago`;
            },

            escapeHtml(text) {
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }
        };

        // Initialize notification system
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => NotificationSystem.init());
        } else {
            NotificationSystem.init();
        }
    </script>
</body>
</html>

