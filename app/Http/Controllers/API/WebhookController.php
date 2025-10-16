<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\TwilioService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Response;

class WebhookController extends Controller
{
    public function __construct(
        private TwilioService $twilioService
    ) {}

    /**
     * Handle incoming SMS from Twilio
     *
     * POST /webhook/twilio
     * 
     * @param Request $request
     * @return Response
     */
    public function receiveSms(Request $request): Response
    {
        // Validate Twilio signature (optional for now, enable in production)
        if (config('services.twilio.validate_signature')) {
            $signature = $request->header('X-Twilio-Signature');
            $url = $request->fullUrl();
            
            if (!$this->twilioService->validateWebhookSignature($url, $request->all(), $signature ?? '')) {
                Log::warning('Invalid Twilio webhook signature', [
                    'url' => $url,
                    'signature' => $signature,
                ]);
                
                // For now, just log warning but don't block (for testing)
                // In production, you'd return a 403:
                // return response('Forbidden', 403);
            }
        }

        // Parse incoming message
        $message = $this->twilioService->parseIncomingMessage($request->all());

        // Log the incoming message
        Log::info('📨 SMS RECEIVED', [
            'from' => $message['from'],
            'to' => $message['to'],
            'body' => $message['body'],
            'message_sid' => $message['message_sid'],
            'num_media' => $message['num_media'],
            'media_urls' => $message['media_urls'],
            'location' => [
                'city' => $message['from_city'],
                'state' => $message['from_state'],
                'zip' => $message['from_zip'],
                'country' => $message['from_country'],
            ],
        ]);

        // Display in console (you'll see this in terminal logs)
        echo "\n";
        echo "═══════════════════════════════════════════════\n";
        echo "📱 NEW SMS RECEIVED\n";
        echo "═══════════════════════════════════════════════\n";
        echo "From: {$message['from']}\n";
        echo "To: {$message['to']}\n";
        echo "Message: {$message['body']}\n";
        if ($message['num_media'] > 0) {
            echo "📎 Media: {$message['num_media']} attachment(s)\n";
            foreach ($message['media_urls'] as $index => $media) {
                echo "   " . ($index + 1) . ". {$media['content_type']} - {$media['url']}\n";
            }
        }
        echo "Time: " . now()->format('Y-m-d H:i:s') . "\n";
        echo "═══════════════════════════════════════════════\n";
        echo "\n";

        // TODO: Later we'll add:
        // - Save to database
        // - Check for chatbot keywords
        // - Send email notification
        // - Link to customer

        // Return TwiML response (optional - tells Twilio we received it)
        return response('<?xml version="1.0" encoding="UTF-8"?><Response></Response>', 200)
            ->header('Content-Type', 'text/xml');
    }

    /**
     * Handle SMS status callbacks from Twilio
     *
     * POST /webhook/twilio/status
     * 
     * @param Request $request
     * @return Response
     */
    public function statusCallback(Request $request): Response
    {
        $messageSid = $request->input('MessageSid');
        $messageStatus = $request->input('MessageStatus');
        $to = $request->input('To');

        Log::info('📊 SMS Status Update', [
            'message_sid' => $messageSid,
            'status' => $messageStatus,
            'to' => $to,
            'timestamp' => now(),
        ]);

        // TODO: Later update database record with delivery status

        return response('OK', 200);
    }
}
