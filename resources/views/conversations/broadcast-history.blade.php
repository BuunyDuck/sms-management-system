<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Broadcast History</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: #f7fafc;
        }
        
        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 20px;
        }
        
        .header {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin-bottom: 30px;
        }
        
        .header h1 {
            font-size: 32px;
            font-weight: 700;
            color: #2d3748;
            margin: 0 0 10px 0;
        }
        
        .header p {
            color: #718096;
            font-size: 14px;
        }
        
        .actions {
            display: flex;
            gap: 15px;
            margin-top: 20px;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.2s;
            cursor: pointer;
            border: none;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(139, 92, 246, 0.4);
        }
        
        .btn-secondary {
            background: #e2e8f0;
            color: #2d3748;
        }
        
        .btn-secondary:hover {
            background: #cbd5e0;
        }
        
        .history-table {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        thead {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        thead th {
            padding: 15px 20px;
            text-align: left;
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        tbody tr {
            border-bottom: 1px solid #e2e8f0;
            transition: background 0.2s;
        }
        
        tbody tr:hover {
            background: #f7fafc;
        }
        
        tbody td {
            padding: 20px;
            font-size: 14px;
            color: #2d3748;
        }
        
        .date-cell {
            font-weight: 600;
            color: #4a5568;
        }
        
        .response-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .response-badge.manual {
            background: #e2e8f0;
            color: #4a5568;
        }
        
        .response-badge.quick {
            background: #d4f4dd;
            color: #276749;
        }
        
        .stat {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
        }
        
        .stat-success {
            color: #48bb78;
            font-weight: 600;
        }
        
        .stat-error {
            color: #f56565;
            font-weight: 600;
        }
        
        .stat-cost {
            color: #667eea;
            font-weight: 600;
        }
        
        .message-preview {
            max-width: 300px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            color: #718096;
            font-size: 13px;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 30px;
        }
        
        .pagination a,
        .pagination span {
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
        }
        
        .pagination a {
            background: white;
            color: #667eea;
            border: 2px solid #667eea;
        }
        
        .pagination a:hover {
            background: #667eea;
            color: white;
        }
        
        .pagination .active {
            background: #667eea;
            color: white;
        }
        
        .pagination .disabled {
            background: #f7fafc;
            color: #cbd5e0;
            border: 2px solid #cbd5e0;
            cursor: not-allowed;
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
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>📊 Broadcast History</h1>
            <p>View all past SMS broadcasts sent by administrators</p>
            
            <div class="actions">
                <a href="{{ route('conversations.broadcast') }}" class="btn btn-primary">📢 Send New Broadcast</a>
                <a href="{{ url('/') }}" class="btn btn-secondary">🏠 Home</a>
                <a href="{{ route('conversations.index') }}" class="btn btn-secondary">💬 Conversations</a>
            </div>
        </div>
        
        <!-- History Table -->
        <div class="history-table">
            @if($broadcasts->isEmpty())
                <div class="empty-state">
                    <div class="empty-state-icon">📭</div>
                    <h2 style="font-size: 24px; color: #2d3748; margin-bottom: 10px;">No Broadcasts Yet</h2>
                    <p style="font-size: 14px;">Send your first broadcast to see it appear here</p>
                    <a href="{{ route('conversations.broadcast') }}" class="btn btn-primary" style="margin-top: 20px;">📢 Send Broadcast</a>
                </div>
            @else
                <table>
                    <thead>
                        <tr>
                            <th>Date & Time</th>
                            <th>Sent By</th>
                            <th>Type</th>
                            <th>Message</th>
                            <th style="text-align: center;">Recipients</th>
                            <th style="text-align: center;">✓ Success</th>
                            <th style="text-align: center;">✗ Failed</th>
                            <th style="text-align: center;">💰 Cost</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($broadcasts as $broadcast)
                            <tr>
                                <!-- Date & Time -->
                                <td class="date-cell">
                                    {{ $broadcast->sent_at->format('M j, Y') }}<br>
                                    <span style="font-size: 12px; color: #718096;">{{ $broadcast->sent_at->format('g:i A') }}</span>
                                </td>
                                
                                <!-- Sent By -->
                                <td>
                                    <div style="font-weight: 600;">{{ $broadcast->user_name }}</div>
                                </td>
                                
                                <!-- Type (Quick Response or Manual) -->
                                <td>
                                    @if($broadcast->quick_response_id)
                                        <div class="response-badge quick">
                                            #{{ $broadcast->quick_response_id }} {{ $broadcast->quick_response_title }}
                                        </div>
                                    @else
                                        <div class="response-badge manual">
                                            Manual
                                        </div>
                                    @endif
                                </td>
                                
                                <!-- Message Preview -->
                                <td>
                                    <div class="message-preview" title="{{ $broadcast->message_body }}">
                                        {{ $broadcast->message_body }}
                                    </div>
                                </td>
                                
                                <!-- Recipients Count -->
                                <td style="text-align: center; font-weight: 600;">
                                    {{ $broadcast->recipients_count }}
                                </td>
                                
                                <!-- Success Count -->
                                <td style="text-align: center;">
                                    <span class="stat-success">{{ $broadcast->success_count }}</span>
                                </td>
                                
                                <!-- Failure Count -->
                                <td style="text-align: center;">
                                    <span class="stat-error">{{ $broadcast->failure_count }}</span>
                                </td>
                                
                                <!-- Total Cost -->
                                <td style="text-align: center;">
                                    <span class="stat-cost">${{ number_format($broadcast->total_cost, 2) }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                
                <!-- Pagination -->
                @if($broadcasts->hasPages())
                    <div class="pagination">
                        {{-- Previous Page Link --}}
                        @if ($broadcasts->onFirstPage())
                            <span class="disabled">« Previous</span>
                        @else
                            <a href="{{ $broadcasts->previousPageUrl() }}">« Previous</a>
                        @endif

                        {{-- Next Page Link --}}
                        @if ($broadcasts->hasMorePages())
                            <a href="{{ $broadcasts->nextPageUrl() }}">Next »</a>
                        @else
                            <span class="disabled">Next »</span>
                        @endif
                    </div>
                @endif
            @endif
        </div>
    </div>
</body>
</html>

