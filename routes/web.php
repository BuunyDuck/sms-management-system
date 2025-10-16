<?php

use Illuminate\Support\Facades\Route;

// Welcome page
Route::get('/', function () {
    return view('welcome');
})->name('home');

// PHP Info (for testing)
Route::get('/phpinfo', function () {
    phpinfo();
})->name('phpinfo');

// Test routes page
Route::get('/test-routes', function () {
    $routes = collect(Route::getRoutes())->map(function ($route) {
        return [
            'method' => implode('|', $route->methods()),
            'uri' => $route->uri(),
            'name' => $route->getName(),
        ];
    })->filter(function ($route) {
        return !str_starts_with($route['uri'], '_');
    })->values();
    
    return view('test-routes', ['routes' => $routes]);
})->name('test.routes');

// Documentation page
Route::get('/docs', function () {
    $docs = [
        'PROJECT_OVERVIEW.md' => 'Project Overview & Architecture',
        'DEPLOYMENT_SECURITY.md' => 'Deployment & Security Guide',
        'NEXT_STEPS.md' => 'Development Roadmap',
        'LOCAL_TESTING.md' => 'Local Testing Guide',
        'README.md' => 'Quick Start Guide',
    ];
    
    return view('docs-index', ['docs' => $docs]);
})->name('docs');

// Serve specific documentation file
Route::get('/docs/{file}', function ($file) {
    $allowedFiles = [
        'overview' => 'PROJECT_OVERVIEW.md',
        'security' => 'DEPLOYMENT_SECURITY.md',
        'next-steps' => 'NEXT_STEPS.md',
        'testing' => 'LOCAL_TESTING.md',
        'readme' => 'README.md',
    ];
    
    if (!isset($allowedFiles[$file])) {
        abort(404);
    }
    
    $filePath = base_path($allowedFiles[$file]);
    
    if (!file_exists($filePath)) {
        abort(404);
    }
    
    $content = file_get_contents($filePath);
    $title = str_replace('.md', '', $allowedFiles[$file]);
    
    return view('docs-viewer', [
        'title' => $title,
        'content' => $content,
        'file' => $file,
    ]);
})->name('docs.view');

// Health check endpoint
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toIso8601String(),
        'laravel' => app()->version(),
        'php' => PHP_VERSION,
        'environment' => config('app.env'),
    ]);
})->name('health');

// Test database connection
Route::get('/test-db', function () {
    try {
        DB::connection()->getPdo();
        $dbName = DB::connection()->getDatabaseName();
        
        return response()->json([
            'status' => 'connected',
            'driver' => config('database.default'),
            'database' => $dbName,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
        ], 500);
    }
})->name('test.db');

// SMS Test Page
Route::get('/send', function () {
    return view('sms-test');
})->name('send');

// Coming soon pages
Route::get('/conversations', function () {
    return view('coming-soon', ['feature' => 'Conversations View']);
})->name('conversations');

Route::get('/chatbot', function () {
    return view('coming-soon', ['feature' => 'Chatbot Manager']);
})->name('chatbot');
