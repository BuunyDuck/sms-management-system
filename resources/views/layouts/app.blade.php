<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Tailwind CSS CDN -->
        <script src="https://cdn.tailwindcss.com"></script>
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>

        <!-- Notification System JavaScript -->
        <script>
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
                    // Toggle dropdown
                    document.getElementById('notification-bell')?.addEventListener('click', (e) => {
                        e.stopPropagation();
                        this.toggleDropdown();
                    });

                    // Close dropdown when clicking outside
                    document.addEventListener('click', (e) => {
                        const container = document.getElementById('notification-container');
                        if (container && !container.contains(e.target)) {
                            this.closeDropdown();
                        }
                    });

                    // Mark all as read
                    document.getElementById('mark-all-read-btn')?.addEventListener('click', () => {
                        this.markAllAsRead();
                    });

                    // Toggle sound
                    document.getElementById('sound-toggle-btn')?.addEventListener('click', () => {
                        this.toggleSound();
                    });
                },

                toggleDropdown() {
                    const dropdown = document.getElementById('notification-dropdown');
                    if (dropdown) {
                        dropdown.classList.toggle('hidden');
                    }
                },

                closeDropdown() {
                    const dropdown = document.getElementById('notification-dropdown');
                    if (dropdown) {
                        dropdown.classList.add('hidden');
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

                        // Play sound if new notifications arrived
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

                    // Update badge
                    if (notifications.length > 0) {
                        badge.textContent = notifications.length;
                        badge.classList.remove('hidden');
                    } else {
                        badge.classList.add('hidden');
                    }

                    // Update list
                    if (notifications.length === 0) {
                        list.innerHTML = '<div class="p-4 text-center text-gray-500">No new notifications</div>';
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
                        <div class="p-4 hover:bg-gray-50 transition duration-150 ease-in-out cursor-pointer notification-item" 
                             data-id="${notification.id}" 
                             data-phone="${notification.phone_number}">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-2">
                                        <span class="text-sm font-semibold text-gray-900">📱 ${customerName}</span>
                                        <span class="text-xs text-gray-500">${timeAgo}</span>
                                    </div>
                                    <div class="text-sm text-gray-600 mt-1">${this.escapeHtml(preview)}</div>
                                    <div class="text-xs text-gray-500 mt-1">${notification.phone_number}</div>
                                </div>
                                <button class="dismiss-btn ml-2 text-gray-400 hover:text-gray-600" 
                                        data-id="${notification.id}" 
                                        title="Dismiss">
                                    ✕
                                </button>
                            </div>
                        </div>
                    `;
                },

                attachNotificationListeners() {
                    // Click notification to view conversation
                    document.querySelectorAll('.notification-item').forEach(item => {
                        item.addEventListener('click', (e) => {
                            if (e.target.classList.contains('dismiss-btn')) return;
                            
                            const id = item.dataset.id;
                            const phone = item.dataset.phone;
                            
                            this.markAsRead(id);
                            window.location.href = `/conversations/${phone.replace('+', '')}`;
                        });
                    });

                    // Dismiss button
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
                        onIcon?.classList.remove('hidden');
                        offIcon?.classList.add('hidden');
                    } else {
                        onIcon?.classList.add('hidden');
                        offIcon?.classList.remove('hidden');
                    }
                },

                playSound() {
                    // Create a simple notification beep using Web Audio API
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
                },

                startPolling() {
                    // Poll every 30 seconds
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

            // Initialize when DOM is ready
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', () => NotificationSystem.init());
            } else {
                NotificationSystem.init();
            }
        </script>
    </body>
</html>
