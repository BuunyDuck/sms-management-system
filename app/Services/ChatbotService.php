<?php

namespace App\Services;

use App\Models\BotSession;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

/**
 * ChatbotService
 * 
 * Handles SMS chatbot logic including:
 * - Keyword detection (MENU, EXIT)
 * - Session management
 * - Menu navigation
 * - Template loading
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

        $option = trim($input);

        // Append option to menu path
        $session->appendOption($option);

        // Get response for new menu state
        return $this->getMenuResponse($session);
    }

    /**
     * Get the main menu response
     */
    protected function getMainMenuResponse(): string
    {
        $response = "BOT:\nSend for Issue:\n";
        $response .= " 1  for SkyConnect\n";
        $response .= " 2  for DSL\n";
        $response .= " 3  for Cable\n";
        $response .= " 4  for Email\n";
        $response .= " 5  for Outages\n";
        $response .= " 6  for Speedtest\n";
        $response .= " 7  for Payments\n";
        $response .= " 8  for MontanaSkyTV\n";
        $response .= " 9  for Voip Phone\n";
        $response .= "10 for Plume Wifi\n";
        $response .= "11 for Fiber GPON\n";
        $response .= "12 for Point to Points\n";
        $response .= "13 for IMAP Settings\n";
        $response .= "14 for POP3 Settings\n";
        $response .= "15 for DSL Walled Garden\n";
        $response .= "16 for SkyConnect DHCP\n";
        $response .= "17 for LTE\n";
        $response .= "18 for MyAccount\n";
        $response .= "19 for Lost Email\n";
        $response .= "20 for Forget Wifi\n\n";
        $response .= "Send EXIT to Quit\n\n";
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
     * Get template content for a menu path
     */
    protected function getTemplateForPath(array $path): ?string
    {
        // Map options to template files
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

        // For now, just handle first level (main menu options)
        // Submenus will be added in refinement
        $firstOption = $path[0] ?? null;

        if (!$firstOption || !isset($templates[$firstOption])) {
            return null;
        }

        return $this->loadTemplate($templates[$firstOption]);
    }

    /**
     * Load a template file
     */
    protected function loadTemplate(string $filename): ?string
    {
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

