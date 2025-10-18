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
            padding: 15px 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-shrink: 0;
        }

        .chat-header-left {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .back-button {
            text-decoration: none;
            color: #007aff;
            font-size: 16px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .back-button:hover {
            opacity: 0.7;
        }

        .contact-info h1 {
            font-size: 18px;
            color: #000;
            margin-bottom: 2px;
        }

        .contact-info p {
            font-size: 12px;
            color: #8e8e93;
        }

        .customer-name {
            font-size: 14px;
            color: #007aff;
            font-weight: 500;
            margin-top: 4px;
        }

        .quick-links {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .quick-link {
            padding: 6px 12px;
            background: #f0f0f0;
            border-radius: 8px;
            text-decoration: none;
            color: #333;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .quick-link:hover {
            background: #007aff;
            color: white;
        }

        .quick-link.account {
            background: #007aff;
            color: white;
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

            .chat-header h1 {
                font-size: 16px;
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
        <div class="chat-header-left">
            <a href="{{ route('conversations.index') }}" class="back-button">
                ← Back
            </a>
            <div class="contact-info">
                <h1>{{ $formattedNumber }}</h1>
                <p>{{ $messageCount }} messages</p>
                @if($customerInfo)
                    <div class="customer-name">👤 {{ $customerInfo->NAME }}</div>
                @endif
            </div>
        </div>
        @if($customerInfo)
        <div class="quick-links">
            <a href="http://www.montanasky.net/MyAccount/AdminEdit.tpl?sku={{ $customerInfo->SKU }}&findnet=y" 
               target="_blank" 
               class="quick-link account" 
               title="Open Customer Account">
                📋 Account
            </a>
            <a href="http://www.montanasky.net/MyAccount/TicketTracker/NewTicket.tpl?ticType=Support&tAction=SEARCH&uid={{ $customerInfo->SKU }}&bdy=&subj=From+SMS" 
               target="_blank" 
               class="quick-link" 
               title="Create Support Ticket">
                🎫 New Ticket
            </a>
        </div>
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

    <div class="messages-container" id="messages-container">
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

            <div class="message {{ $message->isInbound() ? 'message-inbound' : 'message-outbound' }}" @if($isLastMessage) id="last-message" @endif>
                <div class="message-bubble">
                    {{ $message->BODY }}
                    
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
        
        <form method="POST" action="{{ route('conversations.send', ['phoneNumber' => ltrim($phoneNumber, '+')]) }}" class="compose-form">
            @csrf
            <div class="compose-input-wrapper">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px;">
                    <a href="#" onclick="document.getElementById('quick-responses').style.display='block'; return false;" style="color: #007aff; text-decoration: none; font-size: 12px; font-weight: 500;">⚡ Quick Responses</a>
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

    <script>
        // Load quick response templates via our proxy (avoids CORS)
        fetch('{{ route('quick-responses') }}')
            .then(response => response.text())
            .then(html => {
                document.getElementById('quick-response-content').innerHTML = html;
                
                // Fix button clicks to work with our textarea id
                document.querySelectorAll('#ai-message-include-btns div[onclick]').forEach(btn => {
                    const originalClick = btn.getAttribute('onclick');
                    // Replace #smsmessage with #message-input
                    const newClick = originalClick.replace(/#smsmessage/g, '#message-input');
                    btn.setAttribute('onclick', newClick);
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
    </script>
</body>
</html>

