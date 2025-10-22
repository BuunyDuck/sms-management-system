<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\ChatbotAnalyticsController;
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', function () {
    return view('welcome');
})->name('home');

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

// Authenticated routes
Route::middleware('auth')->group(function () {
    // Dashboard (redirect to welcome page)
    Route::get('/dashboard', function () {
        return view('welcome');
    })->name('dashboard');
    
    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Conversation routes (protected)
    Route::get('/conversations', [ConversationController::class, 'index'])->name('conversations.index');
    Route::get('/conversations/compose', [ConversationController::class, 'compose'])->name('conversations.compose');
    Route::post('/conversations/compose/send', [ConversationController::class, 'composeSend'])->name('conversations.compose.send');
    Route::get('/conversation/{phoneNumber}', [ConversationController::class, 'show'])->name('conversations.show');
    Route::post('/conversation/{phoneNumber}/send', [ConversationController::class, 'send'])->name('conversations.send');
    Route::post('/conversation/{phoneNumber}/toggle-support', [ConversationController::class, 'toggleSupport'])->name('conversations.toggle-support');
    Route::post('/conversations/archive', [ConversationController::class, 'archive'])->name('conversations.archive');
    Route::delete('/messages/{id}', [ConversationController::class, 'deleteMessage'])->name('messages.delete');
    
    // SMS Test Page (protected)
    Route::get('/send', function () {
        return view('sms-test');
    })->name('send');
    
    // Chatbot Analytics (protected)
    Route::get('/analytics/chatbot', [ChatbotAnalyticsController::class, 'index'])->name('analytics.chatbot');
    
    // Notifications (protected)
    Route::get('/api/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/api/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/api/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
    Route::delete('/api/notifications/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
    
    // Chatbot Admin (admin only - checked in controller)
    Route::prefix('admin/chatbot')->name('admin.chatbot.')->group(function () {
        Route::get('/', [\App\Http\Controllers\ChatbotAdminController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\ChatbotAdminController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\ChatbotAdminController::class, 'store'])->name('store');
        Route::get('/{chatbotResponse}/edit', [\App\Http\Controllers\ChatbotAdminController::class, 'edit'])->name('edit');
        Route::put('/{chatbotResponse}', [\App\Http\Controllers\ChatbotAdminController::class, 'update'])->name('update');
        Route::delete('/{chatbotResponse}', [\App\Http\Controllers\ChatbotAdminController::class, 'destroy'])->name('destroy');
        Route::post('/preview', [\App\Http\Controllers\ChatbotAdminController::class, 'preview'])->name('preview');
        Route::post('/reorder', [\App\Http\Controllers\ChatbotAdminController::class, 'reorder'])->name('reorder');
    });
});

// Quick Responses API - Load from database (chatbot_responses table)
Route::get('/api/quick-responses', function () {
    try {
        // Get all active chatbot responses, ordered
        $responses = \App\Models\ChatbotResponse::active()->ordered()->get();
        
        // Build HTML in the format expected by the UI
        $html = '<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 10px;">';
        
        foreach ($responses as $response) {
            // Prepare the message (strip any existing media tags since we'll handle them separately)
            $message = preg_replace('/<media>.*?<\/media>/s', '', $response->message);
            $message = trim($message);
            
            // If there's an image, add media tag
            if ($response->image_url) {
                $message .= "\n\n<media>{$response->image_url}</media>";
            }
            
            // Escape for HTML attributes
            $escapedMessage = htmlspecialchars($message, ENT_QUOTES);
            $escapedTitle = htmlspecialchars($response->title, ENT_QUOTES);
            
            // Create button with data attributes
            $html .= '<button type="button" class="quick-response-btn" ';
            $html .= 'data-message="' . $escapedMessage . '" ';
            $html .= 'style="padding: 12px; background: #007aff; color: white; border: none; border-radius: 8px; cursor: pointer; text-align: left; font-size: 13px; transition: all 0.2s;">';
            $html .= '<strong>' . $response->menu_number . '. ' . $escapedTitle . '</strong>';
            if ($response->image_url) {
                $html .= '<div style="font-size: 11px; margin-top: 4px; opacity: 0.9;">📸 Includes image</div>';
            }
            $html .= '</button>';
        }
        
        $html .= '</div>';
        
        return response($html)->header('Content-Type', 'text/html');
    } catch (\Exception $e) {
        \Log::error('Quick Responses API error', ['error' => $e->getMessage()]);
        return response('<div style="padding: 20px; color: #ef4444;">Failed to load quick responses. Please try again.</div>', 500)
            ->header('Content-Type', 'text/html');
    }
})->name('quick-responses');

// Twilio Webhooks (public - no auth, no CSRF)
Route::post('/webhook/twilio', [\App\Http\Controllers\API\WebhookController::class, 'receiveSms'])->name('webhook.twilio');
Route::post('/webhook/twilio/status', [\App\Http\Controllers\API\WebhookController::class, 'statusCallback'])->name('webhook.twilio.status');

require __DIR__.'/auth.php';
require __DIR__.'/api.php';
