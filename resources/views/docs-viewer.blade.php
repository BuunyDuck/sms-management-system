<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} - SMS Management</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f7fa;
            padding: 20px;
            line-height: 1.6;
        }
        
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .back-link {
            display: inline-block;
            margin-bottom: 30px;
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
        
        .markdown-body {
            color: #333;
        }
        
        .markdown-body h1 {
            color: #667eea;
            font-size: 2.5rem;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 3px solid #667eea;
        }
        
        .markdown-body h2 {
            color: #333;
            font-size: 1.8rem;
            margin-top: 40px;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e9ecef;
        }
        
        .markdown-body h3 {
            color: #555;
            font-size: 1.4rem;
            margin-top: 30px;
            margin-bottom: 12px;
        }
        
        .markdown-body h4 {
            color: #666;
            font-size: 1.2rem;
            margin-top: 25px;
            margin-bottom: 10px;
        }
        
        .markdown-body p {
            margin-bottom: 15px;
            color: #555;
        }
        
        .markdown-body pre {
            background: #1e293b;
            color: #e2e8f0;
            padding: 20px;
            border-radius: 8px;
            overflow-x: auto;
            margin: 20px 0;
            font-family: 'Monaco', 'Courier New', monospace;
            font-size: 0.9rem;
        }
        
        .markdown-body code {
            background: #f8f9fa;
            color: #e83e8c;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Monaco', 'Courier New', monospace;
            font-size: 0.9em;
        }
        
        .markdown-body pre code {
            background: transparent;
            color: #4ade80;
            padding: 0;
        }
        
        .markdown-body ul, .markdown-body ol {
            margin-left: 30px;
            margin-bottom: 15px;
        }
        
        .markdown-body li {
            margin-bottom: 8px;
            color: #555;
        }
        
        .markdown-body table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        
        .markdown-body th {
            background: #f8f9fa;
            padding: 12px;
            text-align: left;
            border: 1px solid #dee2e6;
            font-weight: 600;
        }
        
        .markdown-body td {
            padding: 12px;
            border: 1px solid #dee2e6;
        }
        
        .markdown-body blockquote {
            border-left: 4px solid #667eea;
            padding-left: 20px;
            margin: 20px 0;
            color: #666;
            font-style: italic;
        }
        
        .markdown-body a {
            color: #667eea;
            text-decoration: none;
        }
        
        .markdown-body a:hover {
            text-decoration: underline;
        }
        
        .markdown-body hr {
            border: none;
            border-top: 2px solid #e9ecef;
            margin: 40px 0;
        }
        
        .markdown-body strong {
            color: #333;
            font-weight: 600;
        }
        
        .markdown-body em {
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="/docs" class="back-link">← Back to Documentation</a>
        
        <div class="markdown-body">
            {!! nl2br(e($content)) !!}
        </div>
    </div>
</body>
</html>

