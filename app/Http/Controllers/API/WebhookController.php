<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\SmsMessage;
use App\Services\TwilioService;
use App\Services\ChatbotService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Response;

class WebhookController extends Controller
{
    public function __construct(
        private TwilioService $twilioService,
        private ChatbotService $chatbotService
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

        // ========================================
        // CHATBOT LOGIC
        // ========================================
        $chatbotResponse = null;
        $shouldProcessNormally = true;

        // Check if this is a MENU keyword (start chatbot)
        if ($this->chatbotService->isMenuKeyword($message['body'])) {
            Log::info('🤖 Chatbot MENU detected, starting session', ['from' => $message['from']]);
            $chatbotResponse = $this->chatbotService->startSession($message['from']);
            $shouldProcessNormally = false;
        }
        // Check if this is an EXIT keyword (end chatbot)
        elseif ($this->chatbotService->isExitKeyword($message['body'])) {
            Log::info('🤖 Chatbot EXIT detected, ending session', ['from' => $message['from']]);
            $chatbotResponse = $this->chatbotService->endSession($message['from']);
            $shouldProcessNormally = false;
        }
        // Check if there's an active chatbot session
        elseif ($this->chatbotService->hasActiveSession($message['from'])) {
            Log::info('🤖 Active chatbot session found, processing input', ['from' => $message['from'], 'input' => $message['body']]);
            $chatbotResponse = $this->chatbotService->processInput($message['from'], $message['body']);
            
            // If response is empty, it means let normal processing handle it
            if (empty($chatbotResponse)) {
                Log::info('🤖 Chatbot returned empty response, continuing with normal processing');
                $shouldProcessNormally = true;
            } else {
                $shouldProcessNormally = false;
            }
        }

        // If chatbot should respond, send SMS and return early
        if ($chatbotResponse) {
            Log::info('🤖 Sending chatbot response', ['response_length' => strlen($chatbotResponse)]);
            echo "🤖 CHATBOT RESPONDING\n";
            
            // Parse media tags from response
            $parsed = $this->chatbotService->parseMediaTags($chatbotResponse);
            $responseMessage = $parsed['message'];
            $mediaUrl = $parsed['media_url'];
            
            // Send response via Twilio (reply from same number customer texted)
            try {
                $twilioResponse = $this->twilioService->sendSms(
                    to: $message['from'],
                    body: $responseMessage,
                    from: $message['to'], // Reply from same number customer texted to
                    mediaUrl: $mediaUrl
                );
                
                Log::info('✅ Chatbot response sent', [
                    'message_sid' => $twilioResponse['message_sid'],
                    'to' => $message['from'],
                    'from' => $message['to'],
                    'has_media' => !empty($mediaUrl)
                ]);
                echo "✅ Chatbot response sent: {$twilioResponse['message_sid']}\n\n";
                
            } catch (\Exception $e) {
                Log::error('❌ Failed to send chatbot response', [
                    'error' => $e->getMessage(),
                    'to' => $message['from'],
                ]);
                echo "❌ Chatbot send error: {$e->getMessage()}\n\n";
            }
            
            // Still save the INBOUND message to database, but don't send email
            // (The chatbot handled it)
        }
        // ========================================
        // END CHATBOT LOGIC
        // ========================================

        // Save to database
        try {
            // Prepare media URLs and types
            $mediaUrlList = '';
            $mediaTypeList = '';
            
            if ($message['num_media'] > 0) {
                $urls = [];
                $types = [];
                foreach ($message['media_urls'] as $media) {
                    $urls[] = $media['url'];
                    $types[] = $media['content_type'];
                }
                $mediaUrlList = implode("\t", $urls);
                $mediaTypeList = implode("\t", $types);
            }

            SmsMessage::create([
                'FROM' => $message['from'],
                'TO' => $message['to'],
                'BODY' => $message['body'],
                'MESSAGESID' => $message['message_sid'],
                'ACCOUNTSID' => $message['account_sid'],
                'MESSAGINGSERVICESID' => $message['messaging_service_sid'] ?? null,
                'NUMMEDIA' => $message['num_media'],
                'NUMSEGMENTS' => $message['num_segments'] ?? 1,
                'MESSAGESTATUS' => $message['status'] ?? 'received',
                'APIVERSION' => $message['api_version'] ?? null,
                'mediaurllist' => $mediaUrlList,
                'mediatypelist' => $mediaTypeList,
                'FROMCITY' => $message['from_city'],
                'FROMSTATE' => $message['from_state'],
                'FROMZIP' => $message['from_zip'],
                'FROMCOUNTRY' => $message['from_country'],
                'TOCITY' => $message['to_city'] ?? null,
                'TOSTATE' => $message['to_state'] ?? null,
                'TOZIP' => $message['to_zip'] ?? null,
                'TOCOUNTRY' => $message['to_country'] ?? null,
            ]);

            Log::info('✅ Message saved to database', ['message_sid' => $message['message_sid']]);
            echo "✅ Saved to database\n\n";
            
            // Send email notification ONLY if not handled by chatbot
            if ($shouldProcessNormally) {
                $this->sendEmailNotification($message);
            } else {
                Log::info('⏭️ Skipping email notification (chatbot handled message)');
                echo "⏭️ Skipping email (chatbot handled)\n\n";
            }
            
        } catch (\Exception $e) {
            Log::error('❌ Failed to save message to database', [
                'error' => $e->getMessage(),
                'message_sid' => $message['message_sid'],
            ]);
            echo "❌ Database error: {$e->getMessage()}\n\n";
        }

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

    /**
     * Send email notification for incoming SMS (matching ColdFusion logic)
     * 
     * @param array $message Parsed message data
     * @return void
     */
    protected function sendEmailNotification(array $message): void
    {
        try {
            // Skip if from internal test number to another internal number
            if ($message['from'] === '+14062152048' && $message['to'] === '+14067524335') {
                Log::info('Skipping email notification for internal test message');
                return;
            }

            // Find the last outbound message to this phone number
            $lastOutbound = SmsMessage::where('TO', $message['from'])
                ->where('user_id', '!=', 0)
                ->where('thetime', '>=', now()->subDay())
                ->orderBy('thetime', 'desc')
                ->first();

            // Determine email recipient
            $sendToEmail = 'support@montanasky.net'; // Default
            $agentName = 'MTSKY';
            $customerName = '';

            if ($lastOutbound) {
                // Check replies_to_support flag (0 = send to agent, 1 = send to support)
                if ($lastOutbound->replies_to_support == 0 && $lastOutbound->user_id) {
                    // Look up agent's email
                    $agent = \App\Models\User::find($lastOutbound->user_id);
                    if ($agent && $agent->email) {
                        $sendToEmail = $agent->email;
                    }
                }
                $agentName = $lastOutbound->fromname ?: 'MTSKY';
                $customerName = $lastOutbound->toname ?: '';
            }

            // Build email body (no need for history - they can click the link!)
            $emailBody = $this->buildEmailBody($message, $agentName, $customerName);

            // Send email using Laravel Mail
            \Mail::send([], [], function ($mail) use ($sendToEmail, $message, $emailBody) {
                $mail->to($sendToEmail)
                     ->from('dash-sms@montanasky.net', 'MontanaSky SMS')
                     ->subject('SMS from ' . $message['from'])
                     ->html($emailBody);
            });

            Log::info('📧 Email notification sent', [
                'to' => $sendToEmail,
                'from_number' => $message['from'],
                'message_sid' => $message['message_sid'],
            ]);
            echo "📧 Email sent to: {$sendToEmail}\n\n";

        } catch (\Exception $e) {
            Log::error('❌ Failed to send email notification', [
                'error' => $e->getMessage(),
                'message_sid' => $message['message_sid'] ?? null,
            ]);
            echo "❌ Email error: {$e->getMessage()}\n\n";
            // Don't fail the webhook if email fails
        }
    }

    /**
     * Build HTML email body - Simple & clean!
     * 
     * @param array $message Current message
     * @param string $agentName Agent name
     * @param string $customerName Customer name
     * @return string HTML email body
     */
    protected function buildEmailBody(array $message, string $agentName, string $customerName): string
    {
        $fromNumber = str_replace('+', '', $message['from']);
        $toNumber = str_replace('+', '', $message['to']);
        $appUrl = config('app.url');
        $conversationUrl = $appUrl . '/conversation/' . ltrim($message['from'], '+');

        $html = '<!DOCTYPE html>';
        $html .= '<html>';
        $html .= '<head><meta charset="UTF-8"></head>';
        $html .= '<body style="font-family: Arial, sans-serif; padding: 20px;">';
        $html .= '<h3 style="color: #007aff;">📱 New SMS Message</h3>';
        $html .= '<p><strong>From:</strong> ' . $fromNumber . ' ' . $customerName . '</p>';
        $html .= '<p><strong>To:</strong> ' . $toNumber . '</p>';
        $html .= '<p><strong>Agent:</strong> ' . $agentName . '</p>';
        $html .= '<p><strong>Message:</strong></p>';
        $html .= '<div style="background: #f5f5f5; padding: 12px; border-radius: 8px; margin-bottom: 15px;">' . htmlspecialchars($message['body']) . '</div>';

        // Add media attachments if any
        if (!empty($message['media_urls'])) {
            $html .= '<p><strong>📎 Media Attachments:</strong></p>';
            $html .= '<ul>';
            foreach ($message['media_urls'] as $media) {
                $html .= '<li><a href="' . $media['url'] . '">' . $media['content_type'] . '</a></li>';
            }
            $html .= '</ul>';
        }

        // Simple text link instead of button
        $html .= '<hr style="margin: 20px 0; border: none; border-top: 1px solid #ddd;">';
        $html .= '<p><a href="' . $conversationUrl . '" style="color: #007aff; text-decoration: none; font-weight: bold;">SMS Conversation</a></p>';
        $html .= '</body>';
        $html .= '</html>';
        
        return $html;
    }
}
