<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>💬 {{ $formattedNumber }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
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

    <div class="messages-container" id="messages-container">
        @php
            $lastDate = null;
        @endphp

        @foreach($messages as $message)
            @php
                $messageDate = $message->thetime->format('Y-m-d');
                $showDateDivider = $lastDate !== $messageDate;
                $lastDate = $messageDate;
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

            <div class="message {{ $message->isInbound() ? 'message-inbound' : 'message-outbound' }}">
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
        <form method="POST" action="{{ route('conversations.send', ['phoneNumber' => ltrim($phoneNumber, '+')]) }}" class="compose-form">
            @csrf
            <div class="compose-input-wrapper">
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

        // Scroll to bottom on load
        const messagesContainer = document.getElementById('messages-container');
        messagesContainer.scrollTop = messagesContainer.scrollHeight;

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

