<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>💬 {{ $formattedNumber }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
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

        .chat-header {
            background: white;
            padding: 12px 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            gap: 15px;
            flex-shrink: 0;
            flex-wrap: wrap;
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

        .header-title {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 16px;
        }
        
        .header-title strong {
            color: #000;
            font-weight: 600;
        }
        
        .agent-name {
            font-size: 13px;
            color: #666;
            font-weight: 400;
        }
        
        .timeframe-dropdown {
            padding: 6px 12px;
            border-radius: 6px;
            border: 1px solid #ddd;
            background: white;
            color: #333;
            font-size: 13px;
            cursor: pointer;
            font-weight: 500;
            min-width: 140px;
        }
        
        .timeframe-dropdown:hover {
            border-color: #007aff;
        }
        
        .select-all-button {
            padding: 6px 14px;
            background: #6c757d;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            white-space: nowrap;
            transition: all 0.2s;
        }
        
        .select-all-button:hover {
            background: #5a6268;
        }
        
        .header-btn {
            padding: 6px 14px;
            border-radius: 6px;
            text-decoration: none;
            color: white;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.2s;
            white-space: nowrap;
        }
        
        .account-btn {
            background: #007aff;
        }
        
        .account-btn:hover {
            background: #0051d5;
        }
        
        .ticket-btn {
            background: #f59e0b;
        }
        
        .ticket-btn:hover {
            background: #d97706;
        }

        .quick-link.account:hover {
            background: #0051d5;
        }

        /* Quick Response Buttons Styling */
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

        .messages-container {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            display: flex;
            flex-direction: column;
        }

        .message {
            max-width: 65%;
            margin-bottom: 15px;
            display: flex;
            flex-direction: column;
        }

        .message-inbound {
            align-self: flex-start;
        }

        .message-outbound {
            align-self: flex-end;
        }

        .message-bubble {
            padding: 12px 16px;
            border-radius: 18px;
            word-wrap: break-word;
            line-height: 1.4;
        }

        .message-inbound .message-bubble {
            background: white;
            color: #000;
            border-bottom-left-radius: 4px;
        }

        .message-outbound .message-bubble {
            background: #007aff;
            color: white;
            border-bottom-right-radius: 4px;
        }

        .message-timestamp {
            font-size: 11px;
            color: #8e8e93;
            margin-top: 4px;
            padding: 0 4px;
        }

        .message-inbound .message-timestamp {
            text-align: left;
        }

        .message-outbound .message-timestamp {
            text-align: right;
        }

        .message-media {
            max-width: 100%;
            margin-top: 8px;
            border-radius: 12px;
            overflow: hidden;
        }

        .message-media img {
            max-width: 100%;
            display: block;
            border-radius: 12px;
        }

        .message-media video {
            max-width: 100%;
            display: block;
            border-radius: 12px;
        }

        .agent-badge {
            background: #d6bcfa;
            color: #553c9a;
            padding: 3px 8px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 6px;
        }

        .compose-area {
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

        .alert {
            padding: 12px 20px;
            margin: 10px 20px;
            border-radius: 8px;
            font-size: 14px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .char-count {
            font-size: 11px;
            color: #8e8e93;
            text-align: right;
        }

        .date-divider {
            text-align: center;
            margin: 20px 0;
            position: relative;
        }

        .date-divider span {
            background: #e5e5ea;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            color: #8e8e93;
            font-weight: 500;
        }

        @media (max-width: 768px) {
            .message {
                max-width: 80%;
            }

            .chat-header {
                gap: 10px;
                padding: 10px 15px;
            }
            
            .header-title {
                font-size: 14px;
                gap: 8px;
            }
            
            .agent-name {
                font-size: 11px;
            }
            
            .timeframe-dropdown,
            .select-all-button,
            .header-btn {
                font-size: 12px;
                padding: 5px 10px;
            }
            
            .timeframe-dropdown {
                min-width: 120px;
            }
        }
        
        @media (max-width: 480px) {
            .chat-header {
                gap: 8px;
                padding: 8px 12px;
            }
            
            .agent-name {
                display: none; /* Hide agent name on very small screens */
            }
            
            .select-all-button {
                font-size: 11px;
                padding: 4px 8px;
            }
        }

        /* Scroll to bottom on load */
        .messages-container {
            scroll-behavior: smooth;
        }
    </style>
</head>
<body>
    <div class="chat-header">
        <a href="{{ route('conversations.index') }}" class="back-button">← Back</a>
        
        <div class="header-title">
            <strong>{{ $formattedNumber }}</strong>
            @if($customerInfo)
                <span class="agent-name">
                    👤 
                    @if(!empty($customerInfo->NAME))
                        {{ $customerInfo->NAME }}
                    @elseif(!empty($customerInfo->first_name) && !empty($customerInfo->last_name))
                        {{ $customerInfo->first_name }} {{ $customerInfo->last_name }}
                    @else
                        Customer
                    @endif
                </span>
            @endif
        </div>

        <select id="timeframe" name="timeframe" class="timeframe-dropdown" onchange="window.location.href='{{ route('conversations.show', $phoneNumber) }}?timeframe=' + this.value">
            <option value="24h" {{ $timeframe == '24h' ? 'selected' : '' }}>Last 24 Hours</option>
            <option value="48h" {{ $timeframe == '48h' ? 'selected' : '' }}>Last 48 Hours</option>
            <option value="week" {{ $timeframe == 'week' ? 'selected' : '' }}>This Week</option>
            <option value="month" {{ $timeframe == 'month' ? 'selected' : '' }}>This Month</option>
            <option value="year" {{ $timeframe == 'year' ? 'selected' : '' }}>This Year</option>
            <option value="all" {{ $timeframe == 'all' ? 'selected' : '' }}>All Time</option>
        </select>

        <button type="button" onclick="toggleSelectAll()" class="select-all-button">✓ Select All</button>

        @if($customerInfo)
            <a href="http://www.montanasky.net/MyAccount/AdminEdit.tpl?sku={{ $customerInfo->SKU }}&findnet=y" 
               target="_blank" 
               class="header-btn account-btn" 
               title="Open Customer Account">
                📋 Account
            </a>
            <a href="http://www.montanasky.net/MyAccount/TicketTracker/NewTicket.tpl?ticType=Support&tAction=SEARCH&uid={{ $customerInfo->SKU }}&bdy=&subj=From+SMS" 
               target="_blank" 
               class="header-btn ticket-btn" 
               title="Create Support Ticket">
                🎫 New Ticket
            </a>
        @endif

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

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-error">
            {{ session('error') }}
        </div>
    @endif

    <!-- Archive Button (hidden by default) -->
    <div id="archive-bar" style="display: none; position: sticky; top: 41px; background: #fff3cd; border-bottom: 2px solid #ffc107; padding: 12px 20px; z-index: 100; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <span id="selected-count" style="font-weight: 600; color: #856404;">0 messages selected</span>
            <div style="display: flex; gap: 10px;">
                <button type="button" onclick="archiveSelected()" class="btn" style="background: #007aff; color: white; border: none; padding: 8px 20px; border-radius: 6px; font-weight: 600; cursor: pointer;">
                    📋 Archive to Ticket
                </button>
                <button type="button" onclick="clearSelection()" class="btn" style="background: #dc3545; color: white; border: none; padding: 8px 16px; border-radius: 6px; cursor: pointer;">
                    ✕ Clear
                </button>
            </div>
        </div>
    </div>

    <div class="messages-container" id="messages-container">
        @if($messages->isEmpty())
            <div style="text-align: center; padding: 40px 20px; color: #666;">
                <p style="font-size: 18px; margin-bottom: 10px;">📭 No messages found</p>
                <p style="font-size: 14px;">Try selecting a different timeframe from the dropdown above.</p>
            </div>
        @endif
        
        @php
            $lastDate = null;
        @endphp

        @foreach($messages as $message)
            @php
                $messageDate = $message->thetime->format('Y-m-d');
                $showDateDivider = $lastDate !== $messageDate;
                $lastDate = $messageDate;
                $isLastMessage = $loop->last;
            @endphp

            @if($showDateDivider)
                <div class="date-divider">
                    <span>
                        @if($message->thetime->isToday())
                            Today
                        @elseif($message->thetime->isYesterday())
                            Yesterday
                        @else
                            {{ $message->thetime->format('M j, Y') }}
                        @endif
                    </span>
                </div>
            @endif

            <div class="message {{ $message->isInbound() ? 'message-inbound' : 'message-outbound' }}" @if($isLastMessage) id="last-message" @endif data-message-id="{{ $message->id }}">
                <input type="checkbox" class="message-checkbox" value="{{ $message->id }}" style="margin-right: 8px; cursor: pointer;">
                <div class="message-bubble">
                    @if($message->isOutbound() && $message->fromname)
                        <span class="agent-badge">{{ $message->fromname }}</span>
                    @endif
                    @php
                        // Strip <media> tags from display
                        $displayBody = preg_replace('/<media>.*?<\/media>/s', '', $message->BODY);
                        $displayBody = trim($displayBody);
                    @endphp
                    {!! nl2br(e($displayBody)) !!}
                    
                    @if($message->NUMMEDIA > 0)
                        <div class="message-media">
                            @foreach($message->media_attachments as $media)
                                @if(str_starts_with($media['type'], 'image/'))
                                    <img src="{{ $media['url'] }}" alt="Media attachment" loading="lazy">
                                @elseif(str_starts_with($media['type'], 'video/'))
                                    <video controls>
                                        <source src="{{ $media['url'] }}" type="{{ $media['type'] }}">
                                    </video>
                                @else
                                    <a href="{{ $media['url'] }}" target="_blank" style="color: inherit;">
                                        📎 {{ basename($media['url']) }}
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>
                @if(auth()->user()->is_admin)
                    <button class="delete-message-btn" onclick="deleteMessage({{ $message->id }})" title="Delete Message (Admin Only)" style="background: none; border: none; cursor: pointer; color: #ef4444; font-size: 16px; padding: 4px; margin-left: 4px;">
                        🗑️
                    </button>
                @endif
                <div class="message-timestamp">
                    {{ $message->thetime->format('g:i A') }}
                </div>
            </div>
        @endforeach
    </div>

    <div class="compose-area">
        <!-- Quick Response Templates -->
        <div class="quick-responses" id="quick-responses" style="display: none; padding: 10px; background: #f8f8f8; border-bottom: 1px solid #d1d1d6; max-height: 200px; overflow-y: auto;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                <strong style="font-size: 13px; color: #333;">📋 Quick Responses</strong>
                <a href="#" onclick="document.getElementById('quick-responses').style.display='none'; return false;" style="color: #007aff; text-decoration: none; font-size: 12px;">Hide</a>
            </div>
            <div id="quick-response-content" style="font-size: 12px;">
                Loading...
            </div>
        </div>
        
        <form method="POST" action="{{ route('conversations.send', ['phoneNumber' => ltrim($phoneNumber, '+')]) }}" class="compose-form" enctype="multipart/form-data">
            @csrf
            <input type="file" id="file-input-conversation" name="media_file" accept="image/*,video/*" style="display: none;">
            <div class="compose-input-wrapper">
                <div style="display: flex; justify-content: flex-start; align-items: center; margin-bottom: 8px; gap: 10px;">
                    <div style="display: flex; gap: 12px; align-items: center;">
                        <a href="#" onclick="document.getElementById('quick-responses').style.display='block'; return false;" style="color: #007aff; text-decoration: none; font-size: 12px; font-weight: 500;">⚡ Quick Responses</a>
                        <a href="#" onclick="document.getElementById('file-input-conversation').click(); return false;" style="color: #007aff; text-decoration: none; font-size: 12px; font-weight: 500;">📎 Attach File</a>
                        <a href="#" id="send-to-support-btn" onclick="toggleSendToSupport(); return false;" style="color: #007aff; text-decoration: none; font-size: 12px; font-weight: 500; cursor: pointer;">
                            <span id="support-icon">📧</span>
                            <span>Send to Support</span>
                        </a>
                    </div>
                </div>
                <textarea 
                    name="body" 
                    class="compose-input" 
                    placeholder="iMessage" 
                    rows="1"
                    id="message-input"
                    required
                ></textarea>
                <input 
                    type="url" 
                    name="media_url" 
                    class="media-url-input" 
                    placeholder="Optional: Media URL (https://...)"
                    id="media-url-input"
                >
                <div class="char-count" id="char-count">0 / 1600</div>
            </div>
            <button type="submit" class="send-button" id="send-button">
                ↑
            </button>
        </form>
    </div>

    <!-- Hidden Archive Form (permanent in DOM for Safari iOS compatibility) -->
    <form id="archive-form" method="POST" action="{{ route('conversations.archive') }}" target="_blank" style="display: none;">
        @csrf
        <input type="hidden" name="phone_number" id="archive-phone" value="{{ $phoneNumber }}">
        <input type="hidden" name="ids" id="archive-ids" value="">
        <button type="submit" id="archive-submit-btn" style="display: none;">Submit</button>
    </form>

    <script>
        // Load Quick Responses from database (chatbot_responses table)
        fetch('{{ route('quick-responses') }}')
            .then(response => response.text())
            .then(html => {
                document.getElementById('quick-response-content').innerHTML = html;
                
                // Attach click handlers to new database-driven buttons
                document.querySelectorAll('.quick-response-btn').forEach(btn => {
                    btn.onclick = function(e) {
                        e.preventDefault();
                        
                        // Get message from data attribute
                        let content = btn.getAttribute('data-message');
                        
                        if (content) {
                            // Check for <media> tag
                            const mediaMatch = content.match(/<media>(.*?)<\/media>/);
                            
                            if (mediaMatch) {
                                // Extract media URL
                                const mediaUrl = mediaMatch[1];
                                
                                // Remove <media> tag from message
                                content = content.replace(/<media>.*?<\/media>/g, '').trim();
                                
                                // Populate media URL field
                                const mediaUrlInput = document.querySelector('input[name="media_url"]');
                                if (mediaUrlInput) {
                                    mediaUrlInput.value = mediaUrl;
                                }
                            }
                            
                            // Populate message textarea
                            const messageInput = document.getElementById('message-input');
                            if (messageInput) {
                                messageInput.value = content;
                                messageInput.style.height = 'auto';
                                messageInput.style.height = Math.min(messageInput.scrollHeight, 100) + 'px';
                                
                                // Update character count
                                const charCount = document.getElementById('char-count');
                                if (charCount) {
                                    charCount.textContent = `${content.length} / 1600`;
                                }
                                
                                // Enable send button
                                const sendButton = document.getElementById('send-button');
                                if (sendButton) {
                                    sendButton.disabled = false;
                                }
                            }
                            
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

        // Auto-resize textarea
        const messageInput = document.getElementById('message-input');
        messageInput.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 100) + 'px';
            
            // Update character count
            const charCount = document.getElementById('char-count');
            charCount.textContent = `${this.value.length} / 1600`;
            
            // Disable send button if empty
            const sendButton = document.getElementById('send-button');
            sendButton.disabled = this.value.trim() === '';
        });

        // Scroll to absolute bottom of messages container
        const messagesContainer = document.getElementById('messages-container');
        
        function scrollToAbsoluteBottom() {
            // Method 1: Scroll container to max height
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
            
            // Method 2: Also try scrollIntoView on last message
            const lastMessage = document.getElementById('last-message');
            if (lastMessage) {
                lastMessage.scrollIntoView({ behavior: 'instant', block: 'nearest', inline: 'nearest' });
            }
            
            // Method 3: Force scroll to bottom again
            messagesContainer.scrollTop = messagesContainer.scrollHeight + 1000;
        }

        // Aggressive scrolling on page load
        scrollToAbsoluteBottom();
        setTimeout(scrollToAbsoluteBottom, 50);
        setTimeout(scrollToAbsoluteBottom, 150);
        setTimeout(scrollToAbsoluteBottom, 300);
        setTimeout(scrollToAbsoluteBottom, 500);
        setTimeout(scrollToAbsoluteBottom, 1000);
        
        // After all images load
        window.addEventListener('load', () => {
            scrollToAbsoluteBottom();
            setTimeout(scrollToAbsoluteBottom, 200);
            setTimeout(scrollToAbsoluteBottom, 500);
        });

        // Auto-refresh every 5 seconds
        let lastMessageCount = {{ $messageCount }};
        setInterval(async () => {
            if (document.hidden) return; // Don't refresh if tab is hidden
            
            try {
                const response = await fetch(window.location.href);
                const html = await response.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newMessages = doc.querySelectorAll('.message');
                
                if (newMessages.length > lastMessageCount) {
                    // New messages detected, reload page
                    // The scrollIntoView will automatically handle scrolling to last message
                    location.reload();
                }
            } catch (error) {
                console.error('Auto-refresh failed:', error);
            }
        }, 5000);

        // Handle Enter key (send on Enter, new line on Shift+Enter)
        messageInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                if (messageInput.value.trim() !== '') {
                    document.querySelector('.compose-form').submit();
                }
            }
        });

        // Clear alerts after 5 seconds
        setTimeout(() => {
            document.querySelectorAll('.alert').forEach(alert => {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            });
        }, 5000);

        // ============ Archive Feature ============
        
        // Handle checkbox selection
        document.addEventListener('change', (e) => {
            if (e.target.classList.contains('message-checkbox')) {
                updateArchiveBar();
            }
        });

        function updateArchiveBar() {
            const checkboxes = document.querySelectorAll('.message-checkbox:checked');
            const count = checkboxes.length;
            const archiveBar = document.getElementById('archive-bar');
            const selectedCount = document.getElementById('selected-count');
            const selectAllBtn = document.getElementById('select-all-btn');
            const allCheckboxes = document.querySelectorAll('.message-checkbox');
            
            if (count > 0) {
                archiveBar.style.display = 'block';
                selectedCount.textContent = count + ' message' + (count !== 1 ? 's' : '') + ' selected';
            } else {
                archiveBar.style.display = 'none';
            }
            
            // Update Select All button text
            if (selectAllBtn && allCheckboxes.length > 0) {
                if (count === allCheckboxes.length) {
                    selectAllBtn.textContent = '☐ Deselect All';
                    selectAllBtn.style.background = '#6c757d';
                } else {
                    selectAllBtn.textContent = '☑️ Select All';
                    selectAllBtn.style.background = '#6c757d';
                }
            }
        }

        function clearSelection() {
            document.querySelectorAll('.message-checkbox').forEach(cb => cb.checked = false);
            updateArchiveBar();
        }

        function toggleSelectAll() {
            const allCheckboxes = document.querySelectorAll('.message-checkbox');
            const checkedCheckboxes = document.querySelectorAll('.message-checkbox:checked');
            
            // If all are selected, deselect all. Otherwise, select all
            const shouldSelect = checkedCheckboxes.length !== allCheckboxes.length;
            
            allCheckboxes.forEach(cb => cb.checked = shouldSelect);
            updateArchiveBar();
        }

        function archiveSelected() {
            const checkboxes = document.querySelectorAll('.message-checkbox:checked');
            const ids = Array.from(checkboxes).map(cb => cb.value);
            
            if (ids.length === 0) {
                alert('Please select at least one message');
                return;
            }
            
            // Update the hidden form with selected IDs
            const idsInput = document.getElementById('archive-ids');
            idsInput.value = ids.join(',');
            
            // Click the actual submit button (Safari iOS trusts button.click() more than form.submit())
            const submitBtn = document.getElementById('archive-submit-btn');
            
            // Use setTimeout to ensure the click happens after this function returns
            // This makes Safari iOS think it's a direct user action
            setTimeout(() => {
                submitBtn.click();
            }, 0);
        }

        // Send to Support toggle functionality (simple text link style)
        let sendToSupportEnabled = {{ $sendToSupport ? 'true' : 'false' }};
        
        function toggleSendToSupport() {
            sendToSupportEnabled = !sendToSupportEnabled;
            
            const link = document.getElementById('send-to-support-btn');
            const icon = document.getElementById('support-icon');
            
            if (sendToSupportEnabled) {
                // Active state - green color and checkmark
                link.style.color = '#34c759';
                link.style.fontWeight = '600';
                icon.textContent = '✓';
                
                // Save to localStorage
                localStorage.setItem('sendToSupport_{{ $phoneNumber }}', 'true');
                
                // Save to server
                fetch('{{ route('conversations.toggle-support', ['phoneNumber' => ltrim($phoneNumber, '+')]) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ enabled: true })
                });
            } else {
                // Inactive state - blue color and email icon
                link.style.color = '#007aff';
                link.style.fontWeight = '500';
                icon.textContent = '📧';
                
                // Save to localStorage
                localStorage.setItem('sendToSupport_{{ $phoneNumber }}', 'false');
                
                // Save to server
                fetch('{{ route('conversations.toggle-support', ['phoneNumber' => ltrim($phoneNumber, '+')]) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ enabled: false })
                });
            }
        }
        
        // Initialize link state on page load
        if (sendToSupportEnabled) {
            const link = document.getElementById('send-to-support-btn');
            const icon = document.getElementById('support-icon');
            
            link.style.color = '#34c759';
            link.style.fontWeight = '600';
            icon.textContent = '✓';
        }

        // File input handler
        const fileInputConversation = document.getElementById('file-input-conversation');
        const mediaUrlInput = document.getElementById('media-url-input');
        
        fileInputConversation.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                // Show selected file name
                const fileName = file.name;
                const fileSize = (file.size / 1024 / 1024).toFixed(2); // MB
                
                // Update the Quick Responses link to show file name
                const attachLink = document.querySelector('a[href="#"][onclick*="file-input-conversation"]');
                if (attachLink) {
                    attachLink.innerHTML = '📎 ' + fileName + ' (' + fileSize + ' MB)';
                    attachLink.style.color = '#34c759'; // Green to show success
                }
                
                // Disable media URL input when file is selected
                if (mediaUrlInput) {
                    mediaUrlInput.disabled = true;
                    mediaUrlInput.placeholder = 'File selected - URL disabled';
                }
            }
        });
        
        // Reset file input when form is submitted successfully
        document.querySelector('.compose-form').addEventListener('submit', function() {
            setTimeout(() => {
                fileInputConversation.value = '';
                if (mediaUrlInput) {
                    mediaUrlInput.disabled = false;
                    mediaUrlInput.placeholder = 'Optional: Media URL (https://...)';
                }
                // Reset the attach link text
                const attachLink = document.querySelector('a[href="#"][onclick*="file-input-conversation"]');
                if (attachLink) {
                    attachLink.innerHTML = '📎 Attach File';
                    attachLink.style.color = '#007aff';
                }
            }, 100);
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

        // Delete message function (Admin only)
        async function deleteMessage(messageId) {
            if (!confirm('Are you sure you want to delete this message? This action will hide the message from all views.')) {
                return;
            }

            try {
                const response = await fetch(`/messages/${messageId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                });

                const result = await response.json();

                if (result.success) {
                    // Remove message from DOM with animation
                    const messageElement = document.querySelector(`[data-message-id="${messageId}"]`);
                    if (messageElement) {
                        messageElement.style.transition = 'opacity 0.3s';
                        messageElement.style.opacity = '0';
                        setTimeout(() => {
                            messageElement.remove();
                            
                            // Check if this was the last message in the conversation
                            const remainingMessages = document.querySelectorAll('.message');
                            if (remainingMessages.length === 0) {
                                // Redirect to conversations list if no messages left
                                window.location.href = '/conversations';
                            }
                        }, 300);
                    }
                } else {
                    alert('Failed to delete message: ' + (result.error || 'Unknown error'));
                }
            } catch (error) {
                console.error('Error deleting message:', error);
                alert('Failed to delete message. Please try again.');
            }
        }

        // Initialize notification system
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => NotificationSystem.init());
        } else {
            NotificationSystem.init();
        }
    </script>
</body>
</html>

