<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documentation - SMS Management</title>
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
            max-width: 1000px;
            margin: 0 auto;
        }
        
        .header {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .header h1 {
            color: #333;
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        
        .header p {
            color: #666;
            font-size: 1.1rem;
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
        
        .docs-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .doc-card {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            text-decoration: none;
            color: inherit;
            display: block;
            border-left: 4px solid #667eea;
        }
        
        .doc-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 16px rgba(102, 126, 234, 0.2);
        }
        
        .doc-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
        }
        
        .doc-card h3 {
            color: #333;
            font-size: 1.3rem;
            margin-bottom: 10px;
        }
        
        .doc-card p {
            color: #666;
            font-size: 0.95rem;
            line-height: 1.5;
        }
        
        .doc-card .arrow {
            margin-top: 15px;
            color: #667eea;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="/" class="back-link">← Back to Home</a>
        
        <div class="header">
            <h1>📚 Documentation</h1>
            <p>Complete guides for the SMS Management System</p>
        </div>
        
        <div class="docs-grid">
            <a href="/docs/overview" class="doc-card">
                <div class="doc-icon">📋</div>
                <h3>Project Overview</h3>
                <p>Complete system architecture, features, and technical documentation</p>
                <div class="arrow">Read more →</div>
            </a>
            
            <a href="/docs/security" class="doc-card">
                <div class="doc-icon">🔒</div>
                <h3>Security & Deployment</h3>
                <p>Security best practices, self-containment verification, and deployment guide</p>
                <div class="arrow">Read more →</div>
            </a>
            
            <a href="/docs/next-steps" class="doc-card">
                <div class="doc-icon">🚀</div>
                <h3>Development Roadmap</h3>
                <p>Step-by-step development guide and feature priorities</p>
                <div class="arrow">Read more →</div>
            </a>
            
            <a href="/docs/testing" class="doc-card">
                <div class="doc-icon">🧪</div>
                <h3>Local Testing</h3>
                <p>Testing guide, troubleshooting, and development tips</p>
                <div class="arrow">Read more →</div>
            </a>
            
            <a href="/docs/readme" class="doc-card">
                <div class="doc-icon">⚡</div>
                <h3>Quick Start</h3>
                <p>Installation, setup, and basic usage</p>
                <div class="arrow">Read more →</div>
            </a>
            
            <a href="https://laravel.com/docs" target="_blank" class="doc-card">
                <div class="doc-icon">🔗</div>
                <h3>Laravel Docs</h3>
                <p>Official Laravel framework documentation (external link)</p>
                <div class="arrow">Open external →</div>
            </a>
        </div>
    </div>
</body>
</html>

