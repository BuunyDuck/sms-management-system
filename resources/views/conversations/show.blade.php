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
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; gap: 10px;">
                    <div style="display: flex; gap: 12px; align-items: center;">
                        <a href="#" onclick="document.getElementById('quick-responses').style.display='block'; return false;" style="color: #007aff; text-decoration: none; font-size: 12px; font-weight: 500;">⚡ Quick Responses</a>
                        <a href="#" onclick="document.getElementById('file-input-conversation').click(); return false;" style="color: #007aff; text-decoration: none; font-size: 12px; font-weight: 500;">📎 Attach File</a>
                    </div>
                    <button type="button" id="send-to-support-btn" onclick="toggleSendToSupport()" style="
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
        // Load quick response templates via our proxy (avoids CORS)
        fetch('{{ route('quick-responses') }}')
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

        // Send to Support toggle functionality (use server value)
        let sendToSupportEnabled = {{ $sendToSupport ? 'true' : 'false' }};
        
        function toggleSendToSupport() {
            sendToSupportEnabled = !sendToSupportEnabled;
            
            const btn = document.getElementById('send-to-support-btn');
            const icon = document.getElementById('support-icon');
            const check = document.getElementById('support-check');
            
            if (sendToSupportEnabled) {
                // Active state - show checkmark
                btn.style.background = 'linear-gradient(135deg, #34c759 0%, #2da846 100%)';
                btn.style.boxShadow = '0 2px 8px rgba(52, 199, 89, 0.4)';
                icon.style.display = 'none';
                check.style.display = 'inline';
                
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
                // Inactive state - show icon
                btn.style.background = 'linear-gradient(135deg, #007aff 0%, #0051d5 100%)';
                btn.style.boxShadow = '0 2px 8px rgba(0, 122, 255, 0.3)';
                icon.style.display = 'inline';
                check.style.display = 'none';
                
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
        
        // Initialize button state on page load
        if (sendToSupportEnabled) {
            const btn = document.getElementById('send-to-support-btn');
            const icon = document.getElementById('support-icon');
            const check = document.getElementById('support-check');
            
            btn.style.background = 'linear-gradient(135deg, #34c759 0%, #2da846 100%)';
            btn.style.boxShadow = '0 2px 8px rgba(52, 199, 89, 0.4)';
            icon.style.display = 'none';
            check.style.display = 'inline';
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
    </script>
</body>
</html>

