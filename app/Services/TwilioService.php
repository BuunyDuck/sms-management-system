<?php

namespace App\Services;

use Twilio\Rest\Client;
use Illuminate\Support\Facades\Log;
use Exception;

class TwilioService
{
    protected Client $client;
    protected string $fromNumber;

    public function __construct()
    {
        $accountSid = config('services.twilio.account_sid');
        $authToken = config('services.twilio.auth_token');
        $this->fromNumber = config('services.twilio.from_number');

        // Validate credentials
        if (empty($accountSid) || empty($authToken) || empty($this->fromNumber)) {
            throw new Exception('Twilio credentials not configured. Check your .env file.');
        }

        $this->client = new Client($accountSid, $authToken);
    }

    /**
     * Send an SMS message
     *
     * @param string $to Phone number in E.164 format (+1234567890)
     * @param string $body Message content
     * @param array $options Additional options (from, mediaUrl, statusCallback, etc.)
     * @return array Response with success status and message details
     */
    public function sendSms(string $to, string $body, array $options = []): array
    {
        try {
            // Clean and validate phone number
            $to = $this->formatPhoneNumber($to);
            
            // Determine FROM number (allow override via options)
            $fromNumber = $options['from'] ?? $this->fromNumber;
            
            // Prepare message data
            $messageData = [
                'from' => $fromNumber,
                'body' => $body,
            ];

            // Add optional media URL
            if (!empty($options['mediaUrl'])) {
                $messageData['mediaUrl'] = $options['mediaUrl'];
            }

            // Add status callback
            if (!empty($options['statusCallback'])) {
                $messageData['statusCallback'] = $options['statusCallback'];
            }

            // Send via Twilio
            $message = $this->client->messages->create($to, $messageData);

            // Log success
            Log::info('SMS Sent Successfully', [
                'to' => $to,
                'from' => $fromNumber,
                'message_sid' => $message->sid,
                'status' => $message->status,
                'body_preview' => substr($body, 0, 50) . '...',
            ]);

            return [
                'success' => true,
                'message_sid' => $message->sid,
                'status' => $message->status,
                'to' => $to,
                'from' => $fromNumber,
                'body' => $body,
            ];

        } catch (Exception $e) {
            // Log error
            Log::error('SMS Send Failed', [
                'to' => $to,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'to' => $to,
            ];
        }
    }

    /**
     * Format phone number to E.164 format
     *
     * @param string $phone Phone number in any format
     * @return string Formatted phone number
     */
    protected function formatPhoneNumber(string $phone): string
    {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // If it's 10 digits, assume US and add +1
        if (strlen($phone) === 10) {
            $phone = '1' . $phone;
        }

        // Add + prefix if not present
        if (!str_starts_with($phone, '+')) {
            $phone = '+' . $phone;
        }

        // Validate length (E.164 is typically 11-15 digits with +)
        if (strlen($phone) < 11 || strlen($phone) > 16) {
            throw new Exception('Invalid phone number format: ' . $phone);
        }

        return $phone;
    }

    /**
     * Parse incoming webhook from Twilio
     *
     * @param array $payload Raw webhook data from Twilio
     * @return array Normalized message data
     */
    public function parseIncomingMessage(array $payload): array
    {
        return [
            'message_sid' => $payload['MessageSid'] ?? $payload['SmsSid'] ?? null,
            'account_sid' => $payload['AccountSid'] ?? null,
            'messaging_service_sid' => $payload['MessagingServiceSid'] ?? null,
            'from' => $payload['From'] ?? null,
            'to' => $payload['To'] ?? null,
            'body' => $payload['Body'] ?? '',
            'num_media' => (int)($payload['NumMedia'] ?? 0),
            'num_segments' => (int)($payload['NumSegments'] ?? 1),
            'status' => $payload['SmsStatus'] ?? 'received',
            'api_version' => $payload['ApiVersion'] ?? null,
            'media_urls' => $this->extractMediaUrls($payload),
            'from_city' => $payload['FromCity'] ?? null,
            'from_state' => $payload['FromState'] ?? null,
            'from_zip' => $payload['FromZip'] ?? null,
            'from_country' => $payload['FromCountry'] ?? null,
            'to_city' => $payload['ToCity'] ?? null,
            'to_state' => $payload['ToState'] ?? null,
            'to_zip' => $payload['ToZip'] ?? null,
            'to_country' => $payload['ToCountry'] ?? null,
        ];
    }

    /**
     * Extract media URLs from webhook payload
     *
     * @param array $payload Webhook data
     * @return array Array of media URLs
     */
    protected function extractMediaUrls(array $payload): array
    {
        $mediaUrls = [];
        $numMedia = (int)($payload['NumMedia'] ?? 0);

        for ($i = 0; $i < $numMedia; $i++) {
            if (isset($payload["MediaUrl{$i}"])) {
                $mediaUrls[] = [
                    'url' => $payload["MediaUrl{$i}"],
                    'content_type' => $payload["MediaContentType{$i}"] ?? 'unknown',
                ];
            }
        }

        return $mediaUrls;
    }

    /**
     * Validate Twilio webhook signature
     *
     * @param string $url Full URL of the webhook endpoint
     * @param array $params POST parameters from Twilio
     * @param string $signature X-Twilio-Signature header
     * @return bool True if signature is valid
     */
    public function validateWebhookSignature(string $url, array $params, string $signature): bool
    {
        $authToken = config('services.twilio.auth_token');
        
        // Sort parameters
        ksort($params);
        
        // Create data string
        $data = $url;
        foreach ($params as $key => $value) {
            $data .= $key . $value;
        }
        
        // Create signature
        $expectedSignature = base64_encode(hash_hmac('sha1', $data, $authToken, true));
        
        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Get account information
     *
     * @return array Account details
     */
    public function getAccountInfo(): array
    {
        try {
            $account = $this->client->api->v2010->accounts(config('services.twilio.account_sid'))->fetch();
            
            return [
                'success' => true,
                'account_sid' => $account->sid,
                'friendly_name' => $account->friendlyName,
                'status' => $account->status,
                'type' => $account->type,
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}

