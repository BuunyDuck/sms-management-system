<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Routes - SMS Management</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f7fa;
            padding: 40px 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px 40px;
        }
        
        .header h1 {
            font-size: 2rem;
            margin-bottom: 5px;
        }
        
        .header p {
            opacity: 0.9;
        }
        
        .content {
            padding: 40px;
        }
        
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        thead {
            background: #f8f9fa;
        }
        
        th {
            text-align: left;
            padding: 15px;
            font-weight: 600;
            color: #333;
            border-bottom: 2px solid #dee2e6;
        }
        
        td {
            padding: 15px;
            border-bottom: 1px solid #e9ecef;
        }
        
        tbody tr:hover {
            background: #f8f9fa;
        }
        
        .method {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
            font-family: monospace;
        }
        
        .method-get {
            background: #d1fae5;
            color: #065f46;
        }
        
        .method-post {
            background: #dbeafe;
            color: #1e40af;
        }
        
        .method-put {
            background: #fed7aa;
            color: #92400e;
        }
        
        .method-delete {
            background: #fecaca;
            color: #991b1b;
        }
        
        .uri {
            font-family: 'Monaco', 'Courier New', monospace;
            color: #667eea;
        }
        
        .name {
            color: #666;
            font-size: 0.9rem;
        }
        
        .test-link {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }
        
        .test-link:hover {
            text-decoration: underline;
        }
        
        .count {
            background: #667eea;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔗 Registered Routes</h1>
            <p>Testing all available endpoints</p>
        </div>
        
        <div class="content">
            <a href="/" class="back-link">← Back to Home</a>
            
            <p style="margin-bottom: 20px;">
                <span class="count">{{ count($routes) }} routes</span>
            </p>
            
            <table>
                <thead>
                    <tr>
                        <th>Method</th>
                        <th>URI</th>
                        <th>Name</th>
                        <th>Test</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($routes as $route)
                    <tr>
                        <td>
                            @foreach(explode('|', $route['method']) as $method)
                                @if(strtoupper($method) !== 'HEAD')
                                <span class="method method-{{ strtolower($method) }}">{{ strtoupper($method) }}</span>
                                @endif
                            @endforeach
                        </td>
                        <td class="uri">{{ $route['uri'] }}</td>
                        <td class="name">{{ $route['name'] ?? '-' }}</td>
                        <td>
                            @if(str_contains($route['method'], 'GET'))
                            <a href="/{{ $route['uri'] }}" class="test-link" target="_blank">Test →</a>
                            @else
                            <span style="color: #999;">N/A</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>

