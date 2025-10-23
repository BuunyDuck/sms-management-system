<?php

namespace App\Services;

use App\Models\BotSession;
use App\Models\ChatbotResponse;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

/**
 * ChatbotService
 * 
 * Handles SMS chatbot logic including:
 * - Keyword detection (MENU, EXIT)
 * - Session management
 * - Menu navigation
 * - Template loading from database
 * - Media parsing
 */
class ChatbotService
{
    /**
     * Keywords that trigger chatbot
     */
    const KEYWORD_MENU = 'menu';
    const KEYWORD_EXIT = 'exit';

    /**
     * Menu state constants
     */
    const STATE_MAIN_MENU = 'm';

    /**
     * Template directory path
     */
    protected string $templatePath;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->templatePath = storage_path('chatbot/templates');
    }

    /**
     * Check if a message is a chatbot keyword
     */
    public function isKeyword(string $message): bool
    {
        $normalized = $this->normalizeInput($message);
        return in_array($normalized, [self::KEYWORD_MENU, self::KEYWORD_EXIT]);
    }

    /**
     * Check if message is MENU keyword
     */
    public function isMenuKeyword(string $message): bool
    {
        return $this->normalizeInput($message) === self::KEYWORD_MENU;
    }

    /**
     * Check if message is EXIT keyword
     */
    public function isExitKeyword(string $message): bool
    {
        return $this->normalizeInput($message) === self::KEYWORD_EXIT;
    }

    /**
     * Normalize user input (trim, lowercase)
     */
    protected function normalizeInput(string $input): string
    {
        return strtolower(trim($input));
    }

    /**
     * Get or create session for a phone number
     */
    public function getSession(string $phone): BotSession
    {
        // Normalize phone to 10 digits
        $normalizedPhone = $this->normalizePhone($phone);
        
        return BotSession::findOrCreateByPhone($normalizedPhone);
    }

    /**
     * Normalize phone number to 10 digits
     */
    protected function normalizePhone(string $phone): string
    {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Take last 10 digits
        return substr($phone, -10);
    }

    /**
     * Start a new chatbot session (user sent MENU)
     */
    public function startSession(string $phone): string
    {
        $session = $this->getSession($phone);
        $session->startMainMenu();

        Log::info("Chatbot session started", ['phone' => $phone, 'menu' => $session->menu]);

        return $this->getMainMenuResponse();
    }

    /**
     * End a chatbot session (user sent EXIT)
     */
    public function endSession(string $phone): string
    {
        $session = $this->getSession($phone);
        $session->clear();

        Log::info("Chatbot session ended", ['phone' => $phone]);

        return "BOT: Goodbye";
    }

    /**
     * Process user input during active session
     */
    public function processInput(string $phone, string $input): string
    {
        $session = BotSession::findActiveByPhone($this->normalizePhone($phone));

        // If no active session, treat as normal message (shouldn't happen if called correctly)
        if (!$session) {
            Log::warning("No active session found for phone", ['phone' => $phone]);
            return '';  // Empty string means let normal processing handle it
        }

        // Check for EXIT
        if ($this->isExitKeyword($input)) {
            return $this->endSession($phone);
        }

        // Check for MENU (restart at main menu)
        if ($this->isMenuKeyword($input)) {
            return $this->startSession($phone);
        }

        // Validate input is numeric
        if (!is_numeric($input)) {
            return "BOT: Not valid entry. Please type MENU or type EXIT to resume normal texting.";
        }

        $option = (int) trim($input);
        
        // Reject agent-only menu items (100-199) from customer input
        if ($option >= 100 && $option <= 199) {
            Log::info('🔒 Customer attempted to access agent-only menu item', [
                'phone' => $phone,
                'option' => $option
            ]);
            return "BOT: Invalid option. Please type MENU to see available options or EXIT to resume normal texting.";
        }

        // Append option to menu path
        $session->appendOption($option);

        // Get response for new menu state
        return $this->getMenuResponse($session);
    }

    /**
     * Get the main menu response (from database)
     * Only shows customer-facing menu items (1-99)
     * Agent-only items (100-199) are hidden from customers
     */
    protected function getMainMenuResponse(): string
    {
        // Fetch all active responses from database (only 1-99 for customer menu)
        $responses = ChatbotResponse::active()
            ->where('menu_number', '>=', 1)
            ->where('menu_number', '<=', 99)
            ->ordered()
            ->get();
        
        if ($responses->isEmpty()) {
            // Fallback if no responses in database
            return "BOT: Chatbot is currently unavailable. Please contact support. Send EXIT to quit.";
        }
        
        $response = "BOT:\nSend for Issue:\n";
        
        foreach ($responses as $item) {
            $number = str_pad($item->menu_number, 2, ' ', STR_PAD_LEFT);
            $response .= "{$number} for {$item->title}\n";
        }
        
        $response .= "\nSend EXIT to Quit\n\n";
        $response .= "<media>http://dash.montanasky.net/sms/logo.png</media>";

        return $response;
    }

    /**
     * Get menu response based on current session state
     */
    protected function getMenuResponse(BotSession $session): string
    {
        $path = $session->getMenuPath();

        // Remove the 'm' prefix for processing
        array_shift($path);

        // If empty path, return main menu
        if (empty($path)) {
            return $this->getMainMenuResponse();
        }

        // Get the template based on path
        $template = $this->getTemplateForPath($path);

        if ($template) {
            // For single-level menu items (1-20), reset back to main menu
            // so next selection is treated as new (not as submenu)
            if (count($path) === 1) {
                $session->startMainMenu();  // Reset to 'm' for next selection
            }
            
            return "BOT: " . $template;
        }

        // Invalid option
        $session->clear();
        return "BOT: Invalid option. Session ended. Text MENU to start again.";
    }

    /**
     * Get template content for a menu path (from database)
     */
    protected function getTemplateForPath(array $path): ?string
    {
        // For now, just handle first level (main menu options)
        // Submenus will be added in refinement
        $menuNumber = $path[0] ?? null;

        if (!$menuNumber) {
            return null;
        }

        // Fetch response from database
        $response = ChatbotResponse::where('menu_number', $menuNumber)
            ->where('active', true)
            ->first();

        if (!$response) {
            Log::warning('Chatbot response not found or inactive', [
                'menu_number' => $menuNumber,
            ]);
            
            // Fallback to template files if database doesn't have it yet
            return $this->loadTemplateFromFile($menuNumber);
        }

        // Get the full message with footer
        $message = $response->full_message_with_footer;
        
        // Process dynamic includes if URL is set
        if (!empty($response->include_url)) {
            $message = $this->processDynamicIncludes($message, $response->include_url);
        }

        return $message;
    }

    /**
     * Process dynamic includes by fetching URL content and replacing placeholder
     */
    protected function processDynamicIncludes(string $message, string $url): string
    {
        // Check if message contains the placeholder
        if (!str_contains($message, '{CHATBOT_INCLUDE}')) {
            Log::info('🔗 Include URL set but no placeholder found', ['url' => $url]);
            return $message;
        }

        try {
            Log::info('🔗 Fetching dynamic include content', ['url' => $url]);
            
            // Fetch content from URL with timeout
            $context = stream_context_create([
                'http' => [
                    'timeout' => 10, // 10 seconds timeout
                    'method' => 'GET',
                    'header' => 'User-Agent: MontanaSky-SMS-Chatbot/1.0',
                ],
            ]);
            
            $content = @file_get_contents($url, false, $context);
            
            if ($content === false) {
                Log::error('❌ Failed to fetch include URL', ['url' => $url]);
                return str_replace(
                    '{CHATBOT_INCLUDE}',
                    '[Content unavailable]',
                    $message
                );
            }
            
            // Strip HTML tags and decode entities (show plain text like browser)
            $content = strip_tags($content);
            $content = html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $content = trim($content);
            
            // Replace placeholder
            $processedMessage = str_replace('{CHATBOT_INCLUDE}', $content, $message);
            
            Log::info('✅ Dynamic include processed', [
                'url' => $url,
                'content_length' => strlen($content),
            ]);
            
            return $processedMessage;
            
        } catch (\Exception $e) {
            Log::error('❌ Exception while fetching include URL', [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);
            
            // Replace with error message
            return str_replace(
                '{CHATBOT_INCLUDE}',
                '[Content unavailable]',
                $message
            );
        }
    }

    /**
     * Load a template file (fallback for migration period)
     */
    protected function loadTemplateFromFile(string $menuNumber): ?string
    {
        // Map options to template files (legacy fallback)
        $templates = [
            '1' => 'SKYCONNECT.txt',
            '2' => 'DSL.txt',
            '3' => 'cable.txt',
            '4' => 'email.txt',
            '5' => 'outage.txt',
            '6' => 'speedtest.txt',
            '7' => 'payment.txt',
            '8' => 'mstv.txt',
            '9' => 'voipphone.txt',
            '10' => 'plume.txt',
            '11' => 'fiber.txt',
            '12' => 'p2p.txt',
            '13' => '13_IMAP_Settings.txt',
            '14' => '14_POP3_Settings.txt',
            '15' => '15_DSL_Walled_Garden.txt',
            '16' => '16_SkyConnect_DHCP.txt',
            '17' => '17_LTE.txt',
            '18' => '18_MyAccount.txt',
            '19' => '19_Lost_Email.txt',
            '20' => '20_Forget_Wifi.txt',
        ];
        
        if (!isset($templates[$menuNumber])) {
            return null;
        }
        
        $filename = $templates[$menuNumber];
        $filepath = $this->templatePath . '/' . $filename;

        if (!File::exists($filepath)) {
            Log::warning("Template file not found", ['file' => $filename, 'path' => $filepath]);
            return null;
        }

        $content = File::get($filepath);

        // Clean up any extra whitespace
        return trim($content);
    }

    /**
     * Check if phone has an active session
     */
    public function hasActiveSession(string $phone): bool
    {
        $normalizedPhone = $this->normalizePhone($phone);
        $session = BotSession::findActiveByPhone($normalizedPhone);
        
        return $session !== null;
    }

    /**
     * Parse media tags from response and return array
     * 
     * @return array ['message' => string, 'media_url' => string|null]
     */
    public function parseMediaTags(string $response): array
    {
        $mediaUrl = null;
        $message = $response;

        // Extract media URL
        if (preg_match('/<media>(.*?)<\/media>/', $response, $matches)) {
            $mediaUrl = $matches[1];
            // Remove media tag from message
            $message = preg_replace('/<media>.*?<\/media>/', '', $response);
            $message = trim($message);
        }

        return [
            'message' => $message,
            'media_url' => $mediaUrl,
        ];
    }
}

