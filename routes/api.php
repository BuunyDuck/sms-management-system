<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\SmsController;
use App\Http\Controllers\API\WebhookController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// SMS Sending
Route::prefix('sms')->group(function () {
    Route::post('/send', [SmsController::class, 'send'])->name('sms.send');
    Route::post('/send-test', [SmsController::class, 'sendTest'])->name('sms.send-test');
    Route::get('/test-connection', [SmsController::class, 'testConnection'])->name('sms.test-connection');
});

// Twilio Webhooks (public - no auth)
Route::prefix('webhook')->group(function () {
    Route::post('/twilio', [WebhookController::class, 'receiveSms'])->name('webhook.twilio');
    Route::post('/twilio/status', [WebhookController::class, 'statusCallback'])->name('webhook.twilio.status');
});

