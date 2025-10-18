<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>📋 Archive SMS to Ticket</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 30px;
        }

        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 24px;
        }

        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }

        .messages-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        .messages-table thead {
            background: #667eea;
            color: white;
        }

        .messages-table th,
        .messages-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }

        .messages-table th {
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .messages-table td {
            font-size: 14px;
        }

        .messages-table tbody tr:hover {
            background: #f8f9fa;
        }

        .message-body {
            max-width: 400px;
            word-wrap: break-word;
        }

        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-delivered {
            background: #d4edda;
            color: #155724;
        }

        .status-sent {
            background: #d1ecf1;
            color: #0c5460;
        }

        .status-received {
            background: #e2e3e5;
            color: #383d41;
        }

        .status-warning {
            background: #fff3cd;
            color: #856404;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            padding: 30px 0;
            border-top: 2px solid #e0e0e0;
            border-bottom: 2px solid #e0e0e0;
            margin-bottom: 30px;
        }

        .btn {
            padding: 15px 30px;
            border: none;
            border-radius: 6px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .btn-primary {
            background: #00d4ff;
            color: #333;
        }

        .btn-warning {
            background: #ffc107;
            color: #333;
        }

        .btn-danger {
            background: #ff69b4;
            color: white;
        }

        .ticket-info {
            background: #e7f3ff;
            border-left: 4px solid #007aff;
            padding: 15px 20px;
            margin-bottom: 30px;
            border-radius: 4px;
        }

        .ticket-info h3 {
            color: #007aff;
            font-size: 16px;
            margin-bottom: 8px;
        }

        .ticket-info p {
            color: #333;
            font-size: 14px;
            margin: 5px 0;
        }

        .formatted-body {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 20px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            line-height: 1.6;
            white-space: pre-wrap;
            margin-top: 30px;
            max-height: 400px;
            overflow-y: auto;
        }

        .formatted-body h3 {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            font-size: 14px;
            margin-bottom: 15px;
            color: #666;
        }

        .customer-info {
            background: #f8f9fa;
            padding: 15px 20px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .customer-info h2 {
            font-size: 18px;
            color: #333;
            margin-bottom: 10px;
        }

        .customer-info p {
            color: #666;
            font-size: 14px;
            margin: 5px 0;
        }

        .media-icon {
            color: #007aff;
            font-size: 18px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📋 Archive SMS Messages to Ticket</h1>
        <p class="subtitle">Review selected messages and choose an action below</p>

        @if($customerInfo)
        <div class="customer-info">
            <h2>👤 {{ $customerInfo->NAME }}</h2>
            <p><strong>SKU:</strong> {{ $customerInfo->SKU }}</p>
            <p><strong>Phone:</strong> {{ $phoneNumber }}</p>
        </div>
        @endif

        @if($ticketInfo && $ticketInfo['number'])
        <div class="ticket-info">
            <h3>🎫 Existing Ticket Found</h3>
            <p><strong>Ticket Number:</strong> {{ $ticketInfo['number'] }}</p>
            <p><strong>Status:</strong> {{ strtoupper($ticketInfo['status']) }}</p>
        </div>
        @endif

        <table class="messages-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Date/Time</th>
                    <th>FROM</th>
                    <th>FROM NAME</th>
                    <th>TO</th>
                    <th>TO NAME</th>
                    <th>STATUS</th>
                    <th>MESSAGE</th>
                    <th>FILES</th>
                </tr>
            </thead>
            <tbody>
                @foreach($messages as $message)
                <tr>
                    <td><small>{{ str_pad($message->id, 6, '0', STR_PAD_LEFT) }}</small></td>
                    <td>
                        <small>{{ $message->thetime->format('Y-m-d h:i A') }}</small>
                    </td>
                    <td>{{ $message->FROM }}</td>
                    <td>{{ $message->fromname }}</td>
                    <td>{{ $message->TO }}</td>
                    <td>{{ $message->toname }}</td>
                    <td>
                        <span class="status-badge status-{{ strtolower($message->MESSAGESTATUS ?? 'unknown') }}">
                            {{ $message->MESSAGESTATUS ?? 'Unknown' }}
                        </span>
                    </td>
                    <td class="message-body">{{ $message->BODY }}</td>
                    <td style="width:75px">
                        @if($message->NUMMEDIA > 0)
                            @foreach($message->media_attachments as $index => $media)
                                @if(str_starts_with($media['type'], 'image/'))
                                    <a href="{{ $media['url'] }}" target="_blank" title="{{ $media['url'] }}">
                                        <span class="media-icon">🖼️</span>
                                    </a>
                                @else
                                    <a href="{{ $media['url'] }}" target="_blank" title="{{ $media['url'] }}">
                                        <small>{{ $index + 1 }}. {{ $media['type'] }}</small>
                                    </a>
                                @endif
                                <br>
                            @endforeach
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="action-buttons">
            <!-- Always show: Open New Ticket -->
            <form method="post" action="https://www.montanasky.net/MyAccount/TicketTracker/NewTicket.tpl" target="_blank" style="display: inline;">
                <input type="hidden" name="ticType" value="Support">
                <input type="hidden" name="tAction" value="SEARCH">
                @if($customerInfo)
                <input type="hidden" name="uid" value="{{ $customerInfo->SKU }}">
                @endif
                <input type="hidden" name="subj" value="From SMS Number {{ $phoneNumber }}">
                <input type="hidden" name="smsid" value="{{ $messages->first()->id }}">
                <input type="hidden" name="bdy" value="{{ urlencode($formattedBody) }}">
                <button type="submit" class="btn btn-primary">
                    🎫 OPEN NEW TICKET
                </button>
            </form>

            <!-- Show if ticket exists and is OPEN or PRE-CLOSED -->
            @if($ticketInfo && $ticketInfo['number'] && in_array(strtoupper($ticketInfo['status']), ['OPEN', 'PRE-CLOSED']))
            <form method="post" action="https://www.montanasky.net/MyAccount/TicketTracker/ModifyTicket.tpl" target="_blank" style="display: inline;">
                <input type="hidden" name="tAction" value="FIND">
                <input type="hidden" name="searchBy" value="TicketNumber">
                <input type="hidden" name="searchVal" value="{{ $ticketInfo['number'] }}">
                <input type="hidden" name="subj" value="">
                <input type="hidden" name="smsid" value="{{ $messages->first()->id }}">
                <input type="hidden" name="bdy" value="{{ urlencode($formattedBody) }}">
                <button type="submit" class="btn btn-warning">
                    📝 APPEND TO OPEN TICKET
                </button>
            </form>
            @endif

            <!-- Show if ticket exists and is CLOSED -->
            @if($ticketInfo && $ticketInfo['number'] && !in_array(strtoupper($ticketInfo['status']), ['OPEN', 'PRE-CLOSED']))
            <form method="post" action="https://www.montanasky.net/MyAccount/TicketTracker/ReOpenTicket.tpl" target="_blank" style="display: inline;">
                <input type="hidden" name="tAction" value="FIND">
                <input type="hidden" name="searchBy" value="TicketNumber">
                <input type="hidden" name="searchVal" value="{{ $ticketInfo['number'] }}">
                <input type="hidden" name="bdy" value="{{ urlencode($formattedBody) }}">
                <button type="submit" class="btn btn-danger">
                    🔄 APPEND TO CLOSED TICKET & REOPEN
                </button>
            </form>
            @endif
        </div>

        <div class="formatted-body">
            <h3>📄 Formatted Ticket Body (will be submitted):</h3>
            {{ $formattedBody }}
        </div>
    </div>

    <script>
        // Auto-focus first button
        document.addEventListener('DOMContentLoaded', () => {
            const firstButton = document.querySelector('.btn-primary');
            if (firstButton) {
                firstButton.focus();
            }
        });
    </script>
</body>
</html>

