<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
        </div>
        
        <div class="content">
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
                    <p>{{ config('database.default') }}</p>
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
                    <div class="feature-item pending">
                        <span class="feature-icon">⏳</span>
                        <span>Chatbot System</span>
                    </div>
                    <div class="feature-item pending">
                        <span class="feature-icon">⏳</span>
                        <span>Customer Linking</span>
                    </div>
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

            <!-- Actions -->
            <div class="actions">
                @auth
                    <a href="{{ route('conversations.index') }}" class="btn btn-primary" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">💬 Conversations</a>
                    <a href="{{ route('conversations.compose') }}" class="btn btn-primary" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">📱 New Message</a>
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
        </div>
        
        <div class="footer">
            <p>SMS Management System v0.1.0 | Built with Laravel {{ app()->version() }}</p>
            <p style="margin-top: 5px;">Montana Sky Internet © {{ date('Y') }}</p>
        </div>
    </div>
</body>
</html>
