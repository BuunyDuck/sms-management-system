<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Broadcast Results</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: #f7fafc;
        }
        
        .container {
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
        }
        
        .results-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .results-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .results-header h1 {
            font-size: 28px;
            font-weight: 700;
            margin: 0 0 10px 0;
        }
        
        .results-summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            padding: 30px;
            border-bottom: 2px solid #e2e8f0;
        }
        
        .summary-item {
            text-align: center;
        }
        
        .summary-number {
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .summary-label {
            font-size: 13px;
            color: #718096;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .success { color: #48bb78; }
        .error { color: #f56565; }
        .cost { color: #667eea; }
        
        .results-list {
            padding: 20px;
        }
        
        .result-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px;
            border-bottom: 1px solid #e2e8f0;
            transition: background 0.2s;
        }
        
        .result-item:hover {
            background: #f7fafc;
        }
        
        .result-item:last-child {
            border-bottom: none;
        }
        
        .result-phone {
            font-size: 15px;
            font-weight: 600;
            color: #2d3748;
        }
        
        .result-status {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            font-weight: 600;
        }
        
        .status-success {
            color: #48bb78;
        }
        
        .status-error {
            color: #f56565;
        }
        
        .message-preview {
            margin-top: 30px;
            padding: 20px;
            background: #f7fafc;
            border-left: 4px solid #667eea;
            border-radius: 4px;
        }
        
        .message-preview h3 {
            font-size: 14px;
            font-weight: 600;
            color: #4a5568;
            margin: 0 0 10px 0;
        }
        
        .message-preview pre {
            background: white;
            padding: 15px;
            border-radius: 6px;
            font-size: 14px;
            color: #2d3748;
            white-space: pre-wrap;
            word-wrap: break-word;
            max-height: 200px;
            overflow-y: auto;
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            padding: 30px;
            justify-content: center;
        }
        
        .btn {
            padding: 12px 24px;
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
            background: #667eea;
            color: white;
        }
        
        .btn-primary:hover {
            background: #5a67d8;
            transform: translateY(-1px);
        }
        
        .btn-secondary {
            background: #e2e8f0;
            color: #2d3748;
        }
        
        .btn-secondary:hover {
            background: #cbd5e0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="results-card">
            <!-- Header -->
            <div class="results-header">
                <h1>📢 Broadcast Results</h1>
                <p style="opacity: 0.9; font-size: 14px;">{{ now()->format('F j, Y g:i A') }}</p>
            </div>
            
            <!-- Summary -->
            <div class="results-summary">
                <div class="summary-item">
                    <div class="summary-number success">{{ $successCount }}</div>
                    <div class="summary-label">✓ Sent Successfully</div>
                </div>
                <div class="summary-item">
                    <div class="summary-number error">{{ $failureCount }}</div>
                    <div class="summary-label">✗ Failed</div>
                </div>
                <div class="summary-item">
                    <div class="summary-number cost">${{ number_format($totalCost, 2) }}</div>
                    <div class="summary-label">💰 Estimated Cost</div>
                </div>
            </div>
            
            <!-- From Number Info -->
            <div style="background: #f7fafc; padding: 12px 16px; border-radius: 8px; margin-top: 20px; text-align: center;">
                <div style="font-size: 12px; color: #718096; margin-bottom: 4px;">Sent From:</div>
                <div style="font-size: 14px; font-weight: 600; color: #2d3748;">{{ $fromNumber ?? config('services.twilio.from_number') }}</div>
                <div style="font-size: 11px; color: #718096; margin-top: 2px;">
                    @php
                        $fromNumbers = config('services.twilio.from_numbers', []);
                        $label = $fromNumbers[$fromNumber ?? ''] ?? $fromNumbers[config('services.twilio.from_number')] ?? 'Main';
                    @endphp
                    {{ $label }}
                </div>
            </div>
            
            <!-- Results List -->
            <div class="results-list">
                @foreach($results as $result)
                    <div class="result-item">
                        <div class="result-phone">{{ $result['phone'] }}</div>
                        <div class="result-status {{ $result['success'] ? 'status-success' : 'status-error' }}">
                            @if($result['success'])
                                ✓ {{ $result['status'] }}
                            @else
                                ✗ {{ $result['error'] }}
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
            
            <!-- Message Preview -->
            @if(isset($messageBody))
                <div class="message-preview" style="margin: 0 20px 20px 20px;">
                    <h3>📝 Message Sent:</h3>
                    <pre>{{ $messageBody }}</pre>
                </div>
            @endif
            
            <!-- Action Buttons -->
            <div class="action-buttons">
                <a href="{{ route('conversations.broadcast') }}" class="btn btn-primary">
                    📢 Send Another Broadcast
                </a>
                <a href="{{ route('conversations.broadcast.history') }}" class="btn btn-primary" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                    📊 View History
                </a>
                <a href="{{ route('conversations.index') }}" class="btn btn-secondary">
                    💬 View Conversations
                </a>
                <a href="{{ url('/') }}" class="btn btn-secondary">
                    🏠 Home
                </a>
            </div>
        </div>
    </div>
</body>
</html>

