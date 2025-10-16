<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\SmsMessage;
use App\Services\TwilioService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;

class SmsController extends Controller
{
    public function __construct(
        private TwilioService $twilioService
    ) {}

    /**
     * Send an SMS message
     *
     * POST /api/sms/send
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function send(Request $request): JsonResponse
    {
        // Validate request
        $validated = $request->validate([
            'to' => 'required|string',
            'body' => 'required|string|max:1600',
            'media_url' => 'nullable|url',
            'media_file' => 'nullable|file|mimes:jpg,jpeg,png,gif,mp4,mov,pdf|max:5120', // 5MB max
        ]);

        // Normalize phone number to E.164 format (+1XXXXXXXXXX)
        $phoneNumber = $validated['to'];
        $phoneNumber = preg_replace('/[^0-9+]/', '', $phoneNumber); // Remove all non-digits except +
        
        // If it doesn't start with +, add +1 for US
        if (!str_starts_with($phoneNumber, '+')) {
            // Remove leading 1 if present
            $phoneNumber = ltrim($phoneNumber, '1');
            $phoneNumber = '+1' . $phoneNumber;
        }
        
        // If it starts with + but missing country code
        if ($phoneNumber === '+' . ltrim($phoneNumber, '+') && strlen(ltrim($phoneNumber, '+')) === 10) {
            $phoneNumber = '+1' . ltrim($phoneNumber, '+');
        }
        
        $validated['to'] = $phoneNumber;

        $mediaUrl = $validated['media_url'] ?? null;

        // Handle file upload
        if ($request->hasFile('media_file') && $request->file('media_file')->isValid()) {
            try {
                $file = $request->file('media_file');
                
                // Get file info BEFORE moving (important!)
                $originalName = $file->getClientOriginalName();
                $fileSize = $file->getSize();
                $mimeType = $file->getMimeType();
                
                // Generate unique filename
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                
                // Store in public/media directory
                $file->move(public_path('media'), $filename);
                
                // Generate public URL
                $mediaUrl = url('media/' . $filename);
                
                Log::info('File uploaded successfully', [
                    'original_name' => $originalName,
                    'filename' => $filename,
                    'url' => $mediaUrl,
                    'size' => $fileSize,
                    'mime_type' => $mimeType,
                ]);
                
            } catch (\Exception $e) {
                Log::error('File upload failed', ['error' => $e->getMessage()]);
                return response()->json([
                    'success' => false,
                    'message' => 'File upload failed',
                    'error' => $e->getMessage(),
                ], 500);
            }
        }

        // Prepare options
        $options = [
            'mediaUrl' => $mediaUrl,
        ];

        // Only add statusCallback if not in local environment (Twilio can't reach localhost)
        $appUrl = config('app.url');
        if (!str_contains($appUrl, 'localhost') && !str_contains($appUrl, '127.0.0.1')) {
            $options['statusCallback'] = route('webhook.twilio.status');
        }

        // Send SMS
        $result = $this->twilioService->sendSms(
            $validated['to'],
            $validated['body'],
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

                SmsMessage::create([
                    'FROM' => config('services.twilio.from_number'),
                    'TO' => $validated['to'],
                    'BODY' => $validated['body'],
                    'MESSAGESID' => $result['message_sid'],
                    'ACCOUNTSID' => config('services.twilio.account_sid'),
                    'NUMMEDIA' => $numMedia,
                    'MESSAGESTATUS' => $result['status'],
                    'mediaurllist' => $mediaUrlList,
                    'mediatypelist' => $mediaTypeList,
                ]);

                Log::info('✅ Outbound message saved to database', ['message_sid' => $result['message_sid']]);
            } catch (\Exception $e) {
                Log::error('❌ Failed to save outbound message to database', [
                    'error' => $e->getMessage(),
                    'message_sid' => $result['message_sid'] ?? null,
                ]);
                // Don't fail the API call if database save fails
            }

            return response()->json([
                'success' => true,
                'message' => 'SMS sent successfully',
                'data' => $result,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to send SMS',
            'error' => $result['error'],
        ], 500);
    }

    /**
     * Test Twilio connection
     *
     * GET /api/sms/test-connection
     * 
     * @return JsonResponse
     */
    public function testConnection(): JsonResponse
    {
        try {
            $accountInfo = $this->twilioService->getAccountInfo();
            
            if ($accountInfo['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Twilio connection successful',
                    'account' => $accountInfo,
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Twilio connection failed',
                'error' => $accountInfo['error'],
            ], 500);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Twilio credentials not configured',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Send a test SMS
     *
     * POST /api/sms/send-test
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function sendTest(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'to' => 'required|string',
        ]);

        $testMessage = "🎉 Test message from SMS Management System!\n\n" .
                      "Time: " . now()->format('g:i A') . "\n" .
                      "If you receive this, the system is working!";

        // Prepare options
        $options = [];
        
        // Only add statusCallback if not in local environment
        $appUrl = config('app.url');
        if (!str_contains($appUrl, 'localhost') && !str_contains($appUrl, '127.0.0.1')) {
            $options['statusCallback'] = route('webhook.twilio.status');
        }

        $result = $this->twilioService->sendSms(
            $validated['to'],
            $testMessage,
            $options
        );

        if ($result['success']) {
            Log::info('Test SMS sent', ['to' => $validated['to']]);
            
            return response()->json([
                'success' => true,
                'message' => 'Test SMS sent! Check your phone.',
                'data' => $result,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to send test SMS',
            'error' => $result['error'],
        ], 500);
    }
}
