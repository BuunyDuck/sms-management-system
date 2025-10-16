<?php

namespace App\Http\Controllers;

use App\Models\SmsMessage;
use App\Services\TwilioService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        // Get all unique phone numbers (excluding our Twilio number)
        $conversations = SmsMessage::select(
                DB::raw('CASE 
                    WHEN `From` = ? THEN `To` 
                    ELSE `From` 
                END as contact_number'),
                DB::raw('MAX(DateCreated) as last_message_date'),
                DB::raw('COUNT(*) as message_count')
            )
            ->where(function ($query) use ($twilioNumber) {
                $query->where('From', $twilioNumber)
                      ->orWhere('To', $twilioNumber);
            })
            ->setBindings([$twilioNumber])
            ->groupBy('contact_number')
            ->orderBy('last_message_date', 'desc')
            ->get();

        // Enhance each conversation with last message details
        foreach ($conversations as $conversation) {
            $lastMessage = SmsMessage::forNumber($conversation->contact_number)
                ->latest()
                ->first();
            
            $conversation->last_message = $lastMessage;
            $conversation->formatted_number = $this->formatPhoneNumber($conversation->contact_number);
        }

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
        // Ensure phone number is in E.164 format
        if (!str_starts_with($phoneNumber, '+')) {
            $phoneNumber = '+' . $phoneNumber;
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

        if ($result['success']) {
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

