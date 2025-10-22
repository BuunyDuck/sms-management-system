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
});

// Proxy for quick response templates (to avoid CORS) - accessible without auth
Route::get('/api/quick-responses', function () {
    try {
        $url = 'https://www.montanasky.net/MyAccount/TicketTracker/ajax/AI-Messages-Include.tpl?prepared_sms_text_area_id=message-input&is_include_media_tag=T';
        $response = file_get_contents($url);
        return response($response)->header('Content-Type', 'text/html');
    } catch (\Exception $e) {
        return response('Failed to load quick responses', 500);
    }
})->name('quick-responses');

// Twilio Webhooks (public - no auth, no CSRF)
Route::post('/webhook/twilio', [\App\Http\Controllers\API\WebhookController::class, 'receiveSms'])->name('webhook.twilio');
Route::post('/webhook/twilio/status', [\App\Http\Controllers\API\WebhookController::class, 'statusCallback'])->name('webhook.twilio.status');

require __DIR__.'/auth.php';
require __DIR__.'/api.php';
