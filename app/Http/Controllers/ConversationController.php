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
    public function index(Request $request)
    {
        // Both Montana Sky numbers
        $mtskyNumbers = ['+14062152048', '+14067524335'];
        $filterAgent = $request->input('agent'); // null, 'my', or agent name
        $currentUser = auth()->user();

        // Get recent conversations (last 30 days only for performance with 96K+ messages)
        $since = now()->subDays(30);
        
        // Build query for recent messages
        $query = SmsMessage::where('thetime', '>=', $since)
            ->where(function ($q) use ($mtskyNumbers) {
                $q->whereIn('FROM', $mtskyNumbers)
                  ->orWhereIn('TO', $mtskyNumbers);
            })
            ->whereNotNull('FROM')
            ->whereNotNull('TO');
        
        // Apply agent filter
        if ($filterAgent === 'my') {
            // Show only conversations where current user sent messages
            $query->where('fromname', $currentUser->name);
        } elseif ($filterAgent && $filterAgent !== 'all') {
            // Show conversations for specific agent
            $query->where('fromname', $filterAgent);
        }
        
        $recentMessages = $query->orderBy('thetime', 'desc')->get();
        
        // Group by contact number
        $conversationsData = [];
        foreach ($recentMessages as $message) {
            $contactNumber = (in_array($message->FROM, $mtskyNumbers)) ? $message->TO : $message->FROM;
            
            if (!isset($conversationsData[$contactNumber])) {
                $conversationsData[$contactNumber] = (object)[
                    'contact_number' => $contactNumber,
                    'last_message_date' => $message->thetime,
                    'last_body' => $message->BODY,
                    'is_inbound' => (!in_array($message->FROM, $mtskyNumbers)),
                    'message_count' => 1,
                    'formatted_number' => $this->formatPhoneNumber($contactNumber),
                    'agent_name' => $message->fromname ?? 'System',
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
        
        // Get list of all agents for filter dropdown
        $agents = SmsMessage::where('thetime', '>=', $since)
            ->whereIn('FROM', $mtskyNumbers)
            ->whereNotNull('fromname')
            ->select('fromname')
            ->distinct()
            ->orderBy('fromname')
            ->pluck('fromname');

        return view('conversations.index', compact('conversations', 'agents', 'filterAgent'));
    }

    /**
     * Display a specific conversation
     */
    public function show(Request $request, string $phoneNumber)
    {
        // Normalize phone number: Remove dashes, spaces, parentheses, dots
        $phoneNumber = preg_replace('/[^0-9+]/', '', $phoneNumber);
        
        // Ensure phone number is in E.164 format
        if (!str_starts_with($phoneNumber, '+')) {
            // If it's 10 digits, assume US (+1)
            if (strlen($phoneNumber) === 10) {
                $phoneNumber = '+1' . $phoneNumber;
            } else {
                $phoneNumber = '+' . $phoneNumber;
            }
        }

        // Get timeframe filter (default to 24 hours)
        $timeframe = $request->input('timeframe', '24h');
        
        // Calculate date filter based on timeframe
        $dateFilter = match($timeframe) {
            '24h' => now()->subHours(24),
            '48h' => now()->subHours(48),
            'week' => now()->subWeek(),
            'month' => now()->subMonth(),
            'year' => now()->subYear(),
            'all' => null,
            default => now()->subHours(24),
        };

        // Build query for messages
        $query = SmsMessage::forNumber($phoneNumber);
        
        // Apply date filter if specified
        if ($dateFilter) {
            $query->where('thetime', '>=', $dateFilter);
        }
        
        $messages = $query->oldest()->get();

        if ($messages->isEmpty()) {
            // If no messages in timeframe, show message
            $messages = collect();
        }

        $formattedNumber = $this->formatPhoneNumber($phoneNumber);
        $messageCount = $messages->count();
        
        // Get total message count (all time)
        $totalMessageCount = SmsMessage::forNumber($phoneNumber)->count();

        // If no messages exist at all, redirect to compose with this number
        if ($totalMessageCount === 0) {
            return redirect()
                ->route('conversations.compose')
                ->with('info', '📱 No conversation found for ' . $formattedNumber . '. Start a new one!')
                ->with('prefill_number', $phoneNumber);
        }

        // Get customer information (fetch from all messages, not just filtered)
        $firstMessage = SmsMessage::forNumber($phoneNumber)->oldest()->first();
        $customerInfo = $firstMessage?->getCustomerInfo();

        // Get "Send to Support" preference
        $sendToSupport = DB::table('conversation_preferences')
            ->where('phone_number', $phoneNumber)
            ->value('send_to_support') ?? false;

        return view('conversations.show', compact('messages', 'phoneNumber', 'formattedNumber', 'messageCount', 'totalMessageCount', 'customerInfo', 'timeframe', 'sendToSupport'));
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
            'media_file' => 'nullable|file|mimes:jpg,jpeg,png,gif,mp4,pdf|max:5120',
        ]);

        // Extract <media> tag from body if present (fallback if JavaScript didn't catch it)
        $messageBody = $validated['body'];
        $mediaUrl = $validated['media_url'] ?? null;
        
        if (preg_match('/<media>(.*?)<\/media>/s', $messageBody, $matches)) {
            // If no media_url was already set, use the one from the tag
            if (empty($mediaUrl)) {
                $mediaUrl = trim($matches[1]);
            }
            // Remove <media> tag from message body
            $messageBody = preg_replace('/<media>.*?<\/media>/s', '', $messageBody);
            $messageBody = trim($messageBody);
        }

        // Handle file upload if present (overrides everything)
        
        if ($request->hasFile('media_file')) {
            $file = $request->file('media_file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('media'), $filename);
            $mediaUrl = url('/media/' . $filename);
        }

        // Prepare options
        $options = [
            'mediaUrl' => $mediaUrl,
        ];

        // Only add statusCallback if not in local environment
        $appUrl = config('app.url');
        if (!str_contains($appUrl, 'localhost') && !str_contains($appUrl, '127.0.0.1')) {
            $options['statusCallback'] = route('webhook.twilio.status');
        }

        // Send SMS (use cleaned messageBody without <media> tags)
        $result = $this->twilioService->sendSms(
            $phoneNumber,
            $messageBody,
            $options
        );

        // Save to database if successful
        if ($result['success']) {
            try {
                // Prepare media data (already set from file upload or URL above)
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

                // Get customer name for toname
                $customerInfo = \DB::connection('mysql')
                    ->select("
                        SELECT c.NAME as customer_name, c.SKU as customer_sku
                        FROM cat_customer_to_phone AS p
                        JOIN db_297_netcustomers AS c ON p.customer_sku = c.SKU
                        WHERE p.phone = ?
                        ORDER BY p.is_primary_record_for_cat_sms DESC
                        LIMIT 1
                    ", [ltrim($phoneNumber, '+1')]);
                
                $toName = !empty($customerInfo) ? $customerInfo[0]->customer_name : '';
                $custSku = !empty($customerInfo) ? $customerInfo[0]->customer_sku : null;

                // Get "Send to Support" preference
                $sendToSupport = \DB::table('conversation_preferences')
                    ->where('phone_number', $phoneNumber)
                    ->value('send_to_support') ?? false;
                
                // Convert to replies_to_support format (1 = send to support, 0 = send to agent)
                $repliesToSupport = $sendToSupport ? 1 : 0;

                SmsMessage::create([
                    'FROM' => config('services.twilio.from_number'),
                    'fromname' => auth()->user()->name,  // Agent name
                    'TO' => $phoneNumber,
                    'toname' => $toName,  // Customer name
                    'custsku' => $custSku,
                    'BODY' => $messageBody,  // Use cleaned body without <media> tags
                    'MESSAGESID' => $result['message_sid'],
                    'ACCOUNTSID' => config('services.twilio.account_sid'),
                    'NUMMEDIA' => $numMedia,
                    'MESSAGESTATUS' => $result['status'],
                    'mediaurllist' => $mediaUrlList,
                    'mediatypelist' => $mediaTypeList,
                    'replies_to_support' => $repliesToSupport,
                    'user_id' => auth()->id(),
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
     * Archive selected messages to ticket
     */
    public function archive(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|string',
            'phone_number' => 'required|string',
        ]);

        // Ensure phone number is in E.164 format
        $phoneNumber = $validated['phone_number'];
        if (!str_starts_with($phoneNumber, '+')) {
            $phoneNumber = '+' . $phoneNumber;
        }

        // Get messages by IDs
        $messageIds = explode(',', $validated['ids']);
        $messages = SmsMessage::whereIn('id', $messageIds)
            ->orderBy('thetime', 'desc')
            ->get();

        if ($messages->isEmpty()) {
            abort(404, 'No messages found');
        }

        // Get customer info from first message
        $customerInfo = $messages->first()->getCustomerInfo();
        
        // Build formatted message body (like ColdFusion)
        $formattedBody = $this->buildTicketBody($messages);
        
        // Check for existing tickets using the KhurramTools API
        $ticketInfo = null;
        if ($customerInfo && $customerInfo->SKU) {
            $ticketInfo = $this->checkExistingTicket($customerInfo->SKU);
        }

        return view('conversations.archive', compact('messages', 'phoneNumber', 'customerInfo', 'formattedBody', 'ticketInfo'));
    }

    /**
     * Build formatted ticket body from messages
     */
    private function buildTicketBody($messages)
    {
        $body = '';
        $mtskyNumbers = ['+14062152048', '+14067524335'];

        foreach ($messages as $message) {
            $body .= "\r\nDate: ";
            $body .= $message->thetime->format('Y-m-d h:i A');
            $body .= "\r\n";

            $messageFromUserType = 'Customer';
            $messageFromName = '';

            // Check if message is from agent
            if (in_array($message->FROM, $mtskyNumbers)) {
                $messageFromUserType = 'Agent';
                
                // Extract agent initials from fromname
                $agentName = $message->fromname ?? 'MontanaSky';
                
                // If name contains colon (e.g., "Support: Arnold Bjork"), extract after colon
                if (str_contains($agentName, ':')) {
                    $parts = explode(':', $agentName);
                    $agentName = trim($parts[1] ?? $agentName);
                }
                
                // Create initials
                $nameParts = explode(' ', $agentName);
                $initials = '';
                foreach ($nameParts as $part) {
                    if (!empty($part)) {
                        $initials .= strtoupper($part[0]);
                    }
                }
                
                $messageFromName = 'MontanaSky' . $initials;
            } else {
                // Customer message
                $messageFromName = ($message->fromname ?? 'Unknown') . ' ' . $message->FROM;
            }

            $body .= $messageFromUserType . ': ' . $messageFromName . "\r\n";
            
            // Sanitize message body (replace ! with . like CF does)
            $messageBody = str_replace('!', '.', $message->BODY ?? '');
            $body .= 'Message: ' . $messageBody . "\r\n";
            $body .= "\r\n";
            $body .= "---------------\r\n";
        }

        return $body;
    }

    /**
     * Check for existing ticket using KhurramTools API
     */
    private function checkExistingTicket($customerSku)
    {
        try {
            $url = 'http://www.montanasky.net/KhurramTools/search.tpl?sku=' . urlencode($customerSku);
            
            // Make HTTP request with Basic Auth
            $context = stream_context_create([
                'http' => [
                    'header' => 'Authorization: Basic ' . base64_encode('triggers:l0n3uuo1f')
                ]
            ]);
            
            $response = @file_get_contents($url, false, $context);
            
            if ($response === false) {
                return null;
            }

            // Parse response (format: ticketNumber****ticketStatus)
            $response = trim(str_replace('<!--HAS_WEBDNA_TAGS-->', '', $response));
            
            if (strlen($response) > 0 && str_contains($response, '****')) {
                $parts = explode('****', $response);
                return [
                    'number' => $parts[0] ?? null,
                    'status' => $parts[1] ?? null,
                ];
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Failed to check existing ticket', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Toggle "Send to Support" email forwarding for a conversation
     */
    public function toggleSupport(Request $request, string $phoneNumber)
    {
        // Normalize phone number
        $phoneNumber = preg_replace('/[^0-9+]/', '', $phoneNumber);
        if (!str_starts_with($phoneNumber, '+')) {
            $phoneNumber = '+1' . ltrim($phoneNumber, '1');
        }

        $enabled = $request->input('enabled', false);

        // Upsert the preference
        DB::table('conversation_preferences')->updateOrInsert(
            ['phone_number' => $phoneNumber],
            [
                'send_to_support' => $enabled,
                'updated_at' => now(),
                'created_at' => DB::raw('COALESCE(created_at, NOW())')
            ]
        );

        Log::info('Send to Support toggled', [
            'phone' => $phoneNumber,
            'enabled' => $enabled,
            'user' => auth()->user()->name
        ]);

        return response()->json([
            'success' => true,
            'enabled' => $enabled
        ]);
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

    /**
     * Show compose new message page
     */
    public function compose()
    {
        return view('conversations.compose');
    }

    /**
     * Send message from compose page and redirect to conversation
     */
    public function composeSend(Request $request)
    {
        // Normalize phone number to E.164 format
        $phoneNumber = preg_replace('/[^0-9+]/', '', $request->input('to'));
        if (!str_starts_with($phoneNumber, '+')) {
            $phoneNumber = ltrim($phoneNumber, '1');
            $phoneNumber = '+1' . $phoneNumber;
        }
        
        // If it starts with + but missing country code
        if ($phoneNumber === '+' . ltrim($phoneNumber, '+') && strlen(ltrim($phoneNumber, '+')) === 10) {
            $phoneNumber = '+1' . ltrim($phoneNumber, '+');
        }

        $validated = $request->validate([
            'to' => 'required|string',
            'body' => 'required|string|max:1600',
            'media_url' => 'nullable|url',
            'media_file' => 'nullable|file|mimes:jpg,jpeg,png,gif,mp4,pdf|max:5120',
            'send_to_support' => 'nullable|boolean',
        ]);

        // Extract <media> tag from body if present (fallback if JavaScript didn't catch it)
        $messageBody = $validated['body'];
        $mediaUrl = $validated['media_url'] ?? null;
        
        if (preg_match('/<media>(.*?)<\/media>/s', $messageBody, $matches)) {
            // If no media_url was already set, use the one from the tag
            if (empty($mediaUrl)) {
                $mediaUrl = trim($matches[1]);
            }
            // Remove <media> tag from message body
            $messageBody = preg_replace('/<media>.*?<\/media>/s', '', $messageBody);
            $messageBody = trim($messageBody);
        }

        // Handle file upload if present
        
        if ($request->hasFile('media_file')) {
            $file = $request->file('media_file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('media'), $filename);
            $mediaUrl = url('/media/' . $filename);
        }

        // Prepare options
        $options = [
            'mediaUrl' => $mediaUrl,
        ];

        // Only add statusCallback if not in local environment
        $appUrl = config('app.url');
        if (!str_contains($appUrl, 'localhost') && !str_contains($appUrl, '127.0.0.1')) {
            $options['statusCallback'] = route('webhook.twilio.status');
        }

        // Send SMS (use cleaned messageBody without <media> tags)
        $result = $this->twilioService->sendSms(
            $phoneNumber,
            $messageBody,
            $options
        );

        // Save to database if successful
        if ($result['success']) {
            try {
                // Prepare media data
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

                // Get customer name for toname
                $customerInfo = \DB::connection('mysql')
                    ->select("
                        SELECT c.NAME as customer_name, c.SKU as customer_sku
                        FROM cat_customer_to_phone AS p
                        JOIN db_297_netcustomers AS c ON p.customer_sku = c.SKU
                        WHERE p.phone = ?
                        ORDER BY p.is_primary_record_for_cat_sms DESC
                        LIMIT 1
                    ", [ltrim($phoneNumber, '+1')]);
                
                $toName = !empty($customerInfo) ? $customerInfo[0]->customer_name : '';
                $custSku = !empty($customerInfo) ? $customerInfo[0]->customer_sku : null;

                // Handle "Send to Support" preference
                $sendToSupport = $validated['send_to_support'] ?? false;
                $repliesToSupport = $sendToSupport ? 1 : 0;
                
                // Save preference if set
                if ($sendToSupport) {
                    \DB::table('conversation_preferences')->updateOrInsert(
                        ['phone_number' => $phoneNumber],
                        [
                            'send_to_support' => true,
                            'updated_at' => now(),
                        ]
                    );
                }

                SmsMessage::create([
                    'FROM' => config('services.twilio.from_number'),
                    'fromname' => auth()->user()->name,  // Agent name
                    'TO' => $phoneNumber,
                    'toname' => $toName,  // Customer name
                    'custsku' => $custSku,
                    'BODY' => $messageBody,  // Use cleaned body without <media> tags
                    'MESSAGESID' => $result['message_sid'],
                    'ACCOUNTSID' => config('services.twilio.account_sid'),
                    'NUMMEDIA' => $numMedia,
                    'MESSAGESTATUS' => $result['status'],
                    'mediaurllist' => $mediaUrlList,
                    'mediatypelist' => $mediaTypeList,
                    'replies_to_support' => $repliesToSupport,
                    'user_id' => auth()->id(),
                ]);

                Log::info('✅ Message sent from compose page', ['message_sid' => $result['message_sid']]);
            } catch (\Exception $e) {
                Log::error('❌ Failed to save message from compose page', [
                    'error' => $e->getMessage(),
                    'message_sid' => $result['message_sid'] ?? null,
                ]);
                // Don't fail the redirect if database save fails
            }

            // Redirect to the conversation
            return redirect()
                ->route('conversations.show', ['phoneNumber' => ltrim($phoneNumber, '+')])
                ->with('success', '✅ Message sent successfully!');
        }

        return back()
            ->withInput()
            ->with('error', '❌ Failed to send: ' . ($result['error'] ?? 'Unknown error'));
    }
}

