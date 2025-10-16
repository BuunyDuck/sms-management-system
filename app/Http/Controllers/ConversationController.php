<?php

namespace App\Http\Controllers;

use App\Models\SmsMessage;
use App\Services\TwilioService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ConversationController extends Controller
{
    public function __construct(
        private TwilioService $twilioService
    ) {}

    /**
     * Display list of all conversations
     */
    public function index()
    {
        $twilioNumber = config('services.twilio.from_number');

        // Get recent conversations (last 30 days only for performance with 96K+ messages)
        $since = now()->subDays(30);
        
        // Get all recent messages involving our Twilio number
        $recentMessages = SmsMessage::where('thetime', '>=', $since)
            ->where(function ($query) use ($twilioNumber) {
                $query->where('FROM', $twilioNumber)
                      ->orWhere('TO', $twilioNumber);
            })
            ->whereNotNull('FROM')
            ->whereNotNull('TO')
            ->orderBy('thetime', 'desc')
            ->get();
        
        // Group by contact number
        $conversationsData = [];
        foreach ($recentMessages as $message) {
            $contactNumber = ($message->FROM === $twilioNumber) ? $message->TO : $message->FROM;
            
            if (!isset($conversationsData[$contactNumber])) {
                $conversationsData[$contactNumber] = (object)[
                    'contact_number' => $contactNumber,
                    'last_message_date' => $message->thetime,
                    'last_body' => $message->BODY,
                    'is_inbound' => ($message->FROM !== $twilioNumber),
                    'message_count' => 1,
                    'formatted_number' => $this->formatPhoneNumber($contactNumber),
                ];
            } else {
                $conversationsData[$contactNumber]->message_count++;
            }
        }
        
        // Convert to collection and sort
        $conversations = collect($conversationsData)
            ->sortByDesc(function($conv) {
                return $conv->last_message_date;
            })
            ->take(50)
            ->values();

        return view('conversations.index', compact('conversations'));
    }

    /**
     * Display a specific conversation
     */
    public function show(string $phoneNumber)
    {
        // Ensure phone number is in E.164 format
        if (!str_starts_with($phoneNumber, '+')) {
            $phoneNumber = '+' . $phoneNumber;
        }

        // Get all messages for this conversation
        $messages = SmsMessage::forNumber($phoneNumber)
            ->oldest()
            ->get();

        if ($messages->isEmpty()) {
            abort(404, 'No conversation found for this number');
        }

        $formattedNumber = $this->formatPhoneNumber($phoneNumber);
        $messageCount = $messages->count();

        return view('conversations.show', compact('messages', 'phoneNumber', 'formattedNumber', 'messageCount'));
    }

    /**
     * Send a message from the conversation view
     */
    public function send(Request $request, string $phoneNumber)
    {
        // Normalize phone number to E.164 format
        $phoneNumber = preg_replace('/[^0-9+]/', '', $phoneNumber);
        if (!str_starts_with($phoneNumber, '+')) {
            $phoneNumber = ltrim($phoneNumber, '1');
            $phoneNumber = '+1' . $phoneNumber;
        }
        
        // If it starts with + but missing country code
        if ($phoneNumber === '+' . ltrim($phoneNumber, '+') && strlen(ltrim($phoneNumber, '+')) === 10) {
            $phoneNumber = '+1' . ltrim($phoneNumber, '+');
        }

        $validated = $request->validate([
            'body' => 'required|string|max:1600',
            'media_url' => 'nullable|url',
        ]);

        // Prepare options
        $options = [
            'mediaUrl' => $validated['media_url'] ?? null,
        ];

        // Only add statusCallback if not in local environment
        $appUrl = config('app.url');
        if (!str_contains($appUrl, 'localhost') && !str_contains($appUrl, '127.0.0.1')) {
            $options['statusCallback'] = route('webhook.twilio.status');
        }

        // Send SMS
        $result = $this->twilioService->sendSms(
            $phoneNumber,
            $validated['body'],
            $options
        );

        // Save to database if successful
        if ($result['success']) {
            try {
                // Prepare media data
                $mediaUrl = $validated['media_url'] ?? null;
                $numMedia = $mediaUrl ? 1 : 0;
                $mediaUrlList = $mediaUrl ? $mediaUrl : '';
                $mediaTypeList = '';
                
                // Try to determine content type from URL
                if ($mediaUrl) {
                    $extension = strtolower(pathinfo(parse_url($mediaUrl, PHP_URL_PATH), PATHINFO_EXTENSION));
                    $mediaTypeList = match($extension) {
                        'jpg', 'jpeg' => 'image/jpeg',
                        'png' => 'image/png',
                        'gif' => 'image/gif',
                        'mp4' => 'video/mp4',
                        'pdf' => 'application/pdf',
                        default => 'application/octet-stream',
                    };
                }

                SmsMessage::create([
                    'FROM' => config('services.twilio.from_number'),
                    'TO' => $phoneNumber,
                    'BODY' => $validated['body'],
                    'MESSAGESID' => $result['message_sid'],
                    'ACCOUNTSID' => config('services.twilio.account_sid'),
                    'NUMMEDIA' => $numMedia,
                    'MESSAGESTATUS' => $result['status'],
                    'mediaurllist' => $mediaUrlList,
                    'mediatypelist' => $mediaTypeList,
                ]);

                Log::info('✅ Outbound message saved to database from conversation', ['message_sid' => $result['message_sid']]);
            } catch (\Exception $e) {
                Log::error('❌ Failed to save outbound message to database', [
                    'error' => $e->getMessage(),
                    'message_sid' => $result['message_sid'] ?? null,
                ]);
                // Don't fail the redirect if database save fails
            }

            return redirect()
                ->route('conversations.show', ['phoneNumber' => ltrim($phoneNumber, '+')])
                ->with('success', '✅ Message sent successfully!');
        }

        return back()
            ->withInput()
            ->with('error', '❌ Failed to send: ' . ($result['error'] ?? 'Unknown error'));
    }

    /**
     * Format phone number for display
     */
    private function formatPhoneNumber(string $number): string
    {
        // Format +14065551234 as (406) 555-1234
        if (preg_match('/^\+1(\d{3})(\d{3})(\d{4})$/', $number, $matches)) {
            return "({$matches[1]}) {$matches[2]}-{$matches[3]}";
        }
        
        return $number;
    }
}

