<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\SmsMessage;
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
            
            // Send email notification (matching ColdFusion logic)
            $this->sendEmailNotification($message);
            
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

            // Get recent conversation history (last 24 hours)
            $recentMessages = SmsMessage::where(function($query) use ($message) {
                $query->where('FROM', $message['from'])
                      ->orWhere('TO', $message['from']);
            })
            ->where('thetime', '>=', now()->subDay())
            ->orderBy('thetime', 'asc')
            ->get();

            // Build email body
            $emailBody = $this->buildEmailBody($message, $recentMessages, $agentName, $customerName);

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
     * Build HTML email body (matching ColdFusion format)
     * 
     * @param array $message Current message
     * @param \Illuminate\Database\Eloquent\Collection $recentMessages Recent conversation
     * @param string $agentName Agent name
     * @param string $customerName Customer name
     * @return string HTML email body
     */
    protected function buildEmailBody(array $message, $recentMessages, string $agentName, string $customerName): string
    {
        $fromNumber = str_replace('+', '', $message['from']);
        $toNumber = str_replace('+', '', $message['to']);
        $appUrl = config('app.url');
        $conversationUrl = $appUrl . '/conversation/' . ltrim($message['from'], '+');

        $html = "<html><body>";
        $html .= "<strong>From:</strong> {$fromNumber} {$customerName}<br>";
        $html .= "<strong>To:</strong> {$toNumber}<br>";
        $html .= "<strong>Agent:</strong> {$agentName}<br><br>";
        $html .= "<strong>SMS from:</strong> {$message['from']}<br>";
        $html .= "<a href=\"{$conversationUrl}\">Open SMS Conversation</a><br><br>";
        $html .= "<strong>Message:</strong> <em>" . htmlspecialchars($message['body']) . "</em><br>";

        // Add media attachments if any
        if (!empty($message['media_urls'])) {
            $html .= "<br><strong>Media Attachments:</strong><br>";
            foreach ($message['media_urls'] as $media) {
                $html .= "- <a href=\"{$media['url']}\">{$media['content_type']}</a><br>";
            }
        }

        // Add conversation history
        if ($recentMessages->count() > 1) {
            $html .= "<br><br>----- Recent Conversation History -----<br><br>";
            foreach ($recentMessages as $msg) {
                $date = \Carbon\Carbon::parse($msg->thetime)->format('m/d H:i');
                $html .= "<strong>{$date}</strong> From {$msg->fromname}:<br>";
                $html .= htmlspecialchars($msg->BODY) . "<br>";
                if ($msg->ticketid && $msg->ticketid != '0') {
                    $html .= "<br><a href=\"http://www.montanasky.net/MyAccount/TicketTracker/ViewTicket.tpl?ticketid={$msg->ticketid}\">View Ticket</a><br>";
                }
                $html .= "<br>";
            }
        }

        $html .= "</body></html>";
        return $html;
    }
}
