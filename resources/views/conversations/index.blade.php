<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>💬 SMS Conversations</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
        }

        .header {
            background: white;
            padding: 30px;
            border-radius: 15px 15px 0 0;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        }

        .header h1 {
            color: #2d3748;
            font-size: 32px;
            margin-bottom: 10px;
        }

        .header p {
            color: #718096;
            font-size: 16px;
        }

        .header-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .btn {
            display: inline-block;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            font-size: 14px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }

        .btn-secondary {
            background: #e2e8f0;
            color: #4a5568;
        }

        .btn-secondary:hover {
            background: #cbd5e0;
        }

        .user-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-top: 1px solid #e2e8f0;
            margin-top: 15px;
        }

        .user-name {
            font-weight: 600;
            color: #2d3748;
        }

        .filter-section {
            padding: 15px 20px;
            background: #faf5ff;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .filter-label {
            font-weight: 600;
            color: #4a5568;
            font-size: 14px;
        }

        .filter-select {
            flex: 1;
            padding: 10px 15px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 14px;
            background: white;
            cursor: pointer;
            transition: all 0.3s;
        }

        .filter-select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .agent-badge {
            background: #d6bcfa;
            color: #553c9a;
            padding: 3px 8px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
            margin-left: 5px;
        }

        .conversations-list {
            background: white;
            border-radius: 0 0 15px 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .conversation-item {
            display: flex;
            align-items: center;
            padding: 20px;
            border-bottom: 1px solid #e2e8f0;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            color: inherit;
        }

        .conversation-item:hover {
            background: #f7fafc;
        }

        .conversation-item:last-child {
            border-bottom: none;
        }

        .conversation-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 20px;
            margin-right: 15px;
            flex-shrink: 0;
        }

        .conversation-content {
            flex: 1;
            min-width: 0;
        }

        .conversation-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 5px;
        }

        .conversation-number {
            font-weight: 600;
            color: #2d3748;
            font-size: 16px;
        }

        .conversation-time {
            font-size: 12px;
            color: #a0aec0;
        }

        .conversation-preview {
            color: #718096;
            font-size: 14px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .conversation-meta {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-left: 10px;
            flex-shrink: 0;
        }

        .message-count {
            background: #e2e8f0;
            color: #4a5568;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }

        .direction-badge {
            font-size: 18px;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #718096;
        }

        .empty-state-icon {
            font-size: 64px;
            margin-bottom: 20px;
        }

        .empty-state h2 {
            color: #2d3748;
            margin-bottom: 10px;
        }

        .search-box {
            padding: 20px;
            border-bottom: 1px solid #e2e8f0;
            background: #f7fafc;
        }

        .search-input {
            width: 100%;
            padding: 12px 15px 12px 40px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s;
            background: white url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="%23a0aec0" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.35-4.35"></path></svg>') no-repeat 12px center;
        }

        .search-input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        @media (max-width: 600px) {
            .header h1 {
                font-size: 24px;
            }

            .conversation-meta {
                flex-direction: column;
                align-items: flex-end;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>💬 SMS Conversations</h1>
            <p>View and manage all your text message conversations</p>
            <p style="margin-top: 10px; font-size: 14px; opacity: 0.9;">📅 Showing conversations from the last 30 days (top 50)</p>
            <div class="header-actions">
                <a href="{{ url('/') }}" class="btn btn-secondary">← Home</a>
                <a href="{{ route('conversations.compose') }}" class="btn btn-primary">+ New Message</a>
            </div>
            <div class="user-info">
                <div>
                    <span style="color: #718096;">Logged in as:</span>
                    <span class="user-name">{{ auth()->user()->name }}</span>
                    @if(auth()->user()->is_admin)
                        <span style="background: #fbbf24; color: #92400e; padding: 3px 8px; border-radius: 6px; font-size: 11px; font-weight: 600; margin-left: 5px;">ADMIN</span>
                    @endif
                </div>
                <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-secondary" style="padding: 8px 16px; font-size: 13px;">Logout</button>
                </form>
            </div>
        </div>

        <div class="conversations-list">
            <div class="search-box">
                <input type="text" id="search" class="search-input" placeholder="Search conversations by phone number...">
            </div>

            <div class="filter-section">
                <span class="filter-label">👤 Filter by Agent:</span>
                <select class="filter-select" id="agent-filter" onchange="window.location.href='{{ route('conversations.index') }}?agent=' + this.value">
                    <option value="all" {{ (!$filterAgent || $filterAgent === 'all') ? 'selected' : '' }}>All Agents</option>
                    <option value="my" {{ $filterAgent === 'my' ? 'selected' : '' }}>My Conversations</option>
                    @foreach($agents as $agent)
                        <option value="{{ $agent }}" {{ $filterAgent === $agent ? 'selected' : '' }}>{{ $agent }}</option>
                    @endforeach
                </select>
            </div>

            @if($conversations->isEmpty())
                <div class="empty-state">
                    <div class="empty-state-icon">📭</div>
                    <h2>No conversations yet</h2>
                    <p>Send your first message to start a conversation</p>
                </div>
            @else
                <div id="conversations-container">
                    @foreach($conversations as $conversation)
                        <a href="{{ route('conversations.show', ['phoneNumber' => ltrim($conversation->contact_number, '+')]) }}" 
                           class="conversation-item"
                           data-number="{{ $conversation->contact_number }}">
                            <div class="conversation-avatar">
                                📱
                            </div>
                            <div class="conversation-content">
                                <div class="conversation-header">
                                    <span class="conversation-number">{{ $conversation->formatted_number }}</span>
                                    <span class="conversation-time">
                                        {{ $conversation->last_message_date->diffForHumans() }}
                                    </span>
                                </div>
                                <div class="conversation-preview">
                                    @if($conversation->is_inbound)
                                        <span class="direction-badge">📥</span>
                                    @else
                                        <span class="direction-badge">📤</span>
                                    @endif
                                    {{ Str::limit($conversation->last_body ?? '(Media message)', 60) }}
                                </div>
                                @if($conversation->agent_name && $conversation->agent_name !== 'System')
                                    <div style="margin-top: 5px;">
                                        <span class="agent-badge">{{ $conversation->agent_name }}</span>
                                    </div>
                                @endif
                            </div>
                            <div class="conversation-meta">
                                <span class="message-count">{{ $conversation->message_count }}</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <script>
        // Search functionality
        const searchInput = document.getElementById('search');
        const conversationItems = document.querySelectorAll('.conversation-item');

        searchInput.addEventListener('input', (e) => {
            const searchTerm = e.target.value.toLowerCase();
            
            conversationItems.forEach(item => {
                const number = item.dataset.number.toLowerCase();
                const text = item.textContent.toLowerCase();
                
                if (number.includes(searchTerm) || text.includes(searchTerm)) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        });

        // Auto-refresh every 30 seconds
        let autoRefresh = setInterval(() => {
            if (document.hidden) return; // Don't refresh if tab is hidden
            
            // Only refresh if not searching
            if (searchInput.value === '') {
                location.reload();
            }
        }, 30000);

        // Clear interval when page is hidden (save resources)
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                clearInterval(autoRefresh);
            } else {
                autoRefresh = setInterval(() => location.reload(), 30000);
            }
        });
    </script>
</body>
</html>

