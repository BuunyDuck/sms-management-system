<!DOCTYPE html>
<html lang="en">
    <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SMS Management System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            max-width: 1000px;
            width: 100%;
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
            position: relative;
        }
        
        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            font-weight: 700;
        }
        
        .header p {
            font-size: 1.1rem;
            opacity: 0.95;
        }
        
        .status-badge {
            display: inline-block;
            background: rgba(255,255,255,0.2);
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.9rem;
            margin-top: 15px;
            backdrop-filter: blur(10px);
        }
        
        .content {
            padding: 40px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .info-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            border-left: 4px solid #667eea;
        }
        
        .info-card h3 {
            color: #667eea;
            font-size: 0.9rem;
            text-transform: uppercase;
            margin-bottom: 8px;
            font-weight: 600;
        }
        
        .info-card p {
            color: #333;
            font-size: 1.2rem;
            font-weight: 600;
        }
        
        .features {
            margin-bottom: 40px;
        }
        
        .features h2 {
            color: #333;
            margin-bottom: 20px;
            font-size: 1.5rem;
        }
        
        .feature-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
        }
        
        .feature-item {
            background: #f8f9fa;
            padding: 15px 20px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            transition: all 0.3s ease;
        }
        
        .feature-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.15);
        }
        
        .feature-item.ready {
            border-left: 4px solid #10b981;
        }
        
        .feature-item.pending {
            border-left: 4px solid #f59e0b;
            opacity: 0.6;
        }
        
        .feature-icon {
            font-size: 1.5rem;
            margin-right: 12px;
        }
        
        .feature-item.ready .feature-icon {
            color: #10b981;
        }
        
        .feature-item.pending .feature-icon {
            color: #f59e0b;
        }
        
        .actions {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            margin-bottom: 40px;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 1rem;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }
        
        .btn-secondary {
            background: #f8f9fa;
            color: #667eea;
            border: 2px solid #667eea;
        }
        
        .btn-secondary:hover {
            background: #667eea;
            color: white;
        }
        
        .footer {
            background: #f8f9fa;
            padding: 20px 40px;
            text-align: center;
            color: #666;
            font-size: 0.9rem;
        }
        
        .paths-info {
            background: #1e293b;
            color: #e2e8f0;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            font-family: 'Monaco', 'Courier New', monospace;
            font-size: 0.85rem;
        }
        
        .paths-info h3 {
            color: #60a5fa;
            margin-bottom: 12px;
            font-size: 1rem;
        }
        
        .paths-info code {
            color: #4ade80;
            background: rgba(74, 222, 128, 0.1);
            padding: 2px 6px;
            border-radius: 3px;
        }
        
        .path-item {
            margin: 8px 0;
            padding: 8px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 4px;
        }
            </style>
    </head>
<body>
    <div class="container">
        <div class="header">
            <h1>📱 SMS Management System</h1>
            <p>Modern Laravel SMS Platform</p>
            <div class="status-badge">✓ System Online - Development Mode</div>
            
            @auth
            <!-- Notification Bell for Authenticated Users -->
            <div style="position: absolute; top: 20px; right: 20px;" id="notification-container">
                <button id="notification-bell" style="position: relative; padding: 8px; background: rgba(255,255,255,0.2); border: none; cursor: pointer; color: white; border-radius: 50%; backdrop-filter: blur(10px);">
                    <svg style="width: 24px; height: 24px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    <span id="notification-badge" style="display: none; position: absolute; top: 0; right: 0; background: #ef4444; color: white; border-radius: 9999px; padding: 2px 6px; font-size: 11px; font-weight: bold;">0</span>
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
            @endauth
        </div>
        
        <div class="content">
            <!-- Actions -->
            <div class="actions">
                    @auth
                    <a href="{{ route('conversations.index') }}" class="btn btn-primary" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">💬 Conversations</a>
                    <a href="{{ route('conversations.compose') }}" class="btn btn-primary" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">📱 New Message</a>
                    <a href="{{ route('analytics.chatbot') }}" class="btn btn-primary" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">📊 Chatbot Analytics</a>
                    <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn btn-secondary">🚪 Logout</button>
                    </form>
                    @else
                    <a href="{{ route('login') }}" class="btn btn-primary">🔐 Login</a>
                    <a href="{{ route('register') }}" class="btn btn-primary" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">✨ Register</a>
                @endauth
                <a href="{{ route('test.db') }}" class="btn btn-secondary">🔗 Test Database</a>
            </div>

            <!-- Server Info -->
            <div class="info-grid">
                <div class="info-card">
                    <h3>Laravel Version</h3>
                    <p>{{ app()->version() }}</p>
                </div>
                <div class="info-card">
                    <h3>PHP Version</h3>
                    <p>{{ PHP_VERSION }}</p>
                </div>
                <div class="info-card">
                    <h3>Environment</h3>
                    <p>{{ config('app.env') }}</p>
                </div>
                <div class="info-card">
                    <h3>Database</h3>
                    <p>{{ config('database.connections.mysql.database') }}.cat_sms</p>
                </div>
            </div>
            
            <!-- Path Testing -->
            <div class="paths-info">
                <h3>🔗 Local Server Paths</h3>
                <div class="path-item">
                    <strong>Base URL:</strong> <code>{{ config('app.url') }}</code>
                </div>
                <div class="path-item">
                    <strong>Project Path:</strong> <code>{{ base_path() }}</code>
                </div>
                <div class="path-item">
                    <strong>Public Path:</strong> <code>{{ public_path() }}</code>
                </div>
                <div class="path-item">
                    <strong>Storage Path:</strong> <code>{{ storage_path() }}</code>
                </div>
            </div>
            
            <!-- Auth Status -->
            @auth
                <div style="background: #f0fdf4; padding: 20px; border-radius: 10px; margin-bottom: 30px; border-left: 4px solid #10b981;">
                    <p style="color: #166534; font-weight: 600; margin-bottom: 5px;">
                        👋 Welcome back, {{ auth()->user()->name }}!
                        @if(auth()->user()->is_admin)
                            <span style="background: #fbbf24; color: #92400e; padding: 3px 8px; border-radius: 6px; font-size: 11px; font-weight: 600; margin-left: 5px;">ADMIN</span>
                        @endif
                    </p>
                    <p style="color: #166534; font-size: 0.9rem;">Last login: {{ auth()->user()->last_login_at ? auth()->user()->last_login_at->diffForHumans() : 'First time!' }}</p>
                </div>
            @else
                <div style="background: #fef3c7; padding: 20px; border-radius: 10px; margin-bottom: 30px; border-left: 4px solid #f59e0b;">
                    <p style="color: #92400e; font-weight: 600;">
                        🔐 Please login or register to access the SMS system
                    </p>
                </div>
            @endauth

            <!-- Features Status -->
            <div class="features">
                <h2>🚀 Features Status</h2>
                <div class="feature-list">
                    <div class="feature-item ready">
                        <span class="feature-icon">✓</span>
                        <span>Send SMS</span>
                    </div>
                    <div class="feature-item ready">
                        <span class="feature-icon">✓</span>
                        <span>Receive SMS Webhook</span>
                    </div>
                    <div class="feature-item ready">
                        <span class="feature-icon">✓</span>
                        <span>Twilio Integration</span>
                    </div>
                    <div class="feature-item ready">
                        <span class="feature-icon">✓</span>
                        <span>Test UI</span>
                    </div>
                    <div class="feature-item ready">
                        <span class="feature-icon">✓</span>
                        <span>Database Storage</span>
                    </div>
                    <div class="feature-item ready">
                        <span class="feature-icon">✓</span>
                        <span>Conversation History</span>
                    </div>
                    <div class="feature-item ready">
                        <span class="feature-icon">✓</span>
                        <span>Chatbot System</span>
                    </div>
                    <div class="feature-item ready">
                        <span class="feature-icon">✓</span>
                        <span>Chatbot Analytics</span>
                    </div>
                    <div class="feature-item pending">
                        <span class="feature-icon">⏳</span>
                        <span>Customer Linking</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="footer">
            <p>SMS Management System v0.1.0 | Built with Laravel {{ app()->version() }}</p>
            <p style="margin-top: 5px;">Montana Sky Internet © {{ date('Y') }}</p>
        </div>
    </div>
    
    @auth
    <script>
        // Notification System (same as other pages)
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
    @endauth
    </body>
</html>