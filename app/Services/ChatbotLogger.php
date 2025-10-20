<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

/**
 * ChatbotLogger Service
 * 
 * Tracks chatbot sessions and interactions for analytics
 */
class ChatbotLogger
{
    /**
     * Current session ID
     */
    protected ?string $sessionId = null;

    /**
     * Session start time
     */
    protected ?Carbon $sessionStart = null;

    /**
     * Last interaction time
     */
    protected ?Carbon $lastInteractionTime = null;

    /**
     * Start a new chatbot session
     */
    public function startSession(string $phone, string $fromNumber): string
    {
        $this->sessionId = (string) Str::uuid();
        $this->sessionStart = Carbon::now();
        $this->lastInteractionTime = $this->sessionStart;

        DB::table('chatbot_sessions_log')->insert([
            'session_id' => $this->sessionId,
            'phone' => $this->normalizePhone($phone),
            'menu_path' => 'm',
            'user_input' => 'MENU',
            'bot_response' => 'Main menu displayed',
            'response_template' => 'main_menu',
            'session_start' => $this->sessionStart,
            'interaction_time' => $this->sessionStart,
            'time_in_menu' => 0,
            'exit_type' => 'active',
            'from_number' => $fromNumber,
            'has_media' => true, // Main menu has logo
            'created_at' => Carbon::now(),
        ]);

        return $this->sessionId;
    }

    /**
     * Log a chatbot interaction
     */
    public function logInteraction(
        string $phone,
        string $menuPath,
        string $userInput,
        string $botResponse,
        ?string $responseTemplate = null,
        bool $hasMedia = false,
        ?int $messageId = null
    ): void {
        // Get or create session ID
        if (!$this->sessionId) {
            $this->sessionId = $this->getOrCreateSessionId($phone);
        }

        $now = Carbon::now();
        $timeInMenu = $this->lastInteractionTime 
            ? $now->diffInSeconds($this->lastInteractionTime) 
            : 0;

        DB::table('chatbot_sessions_log')->insert([
            'session_id' => $this->sessionId,
            'phone' => $this->normalizePhone($phone),
            'menu_path' => $menuPath,
            'user_input' => $userInput,
            'bot_response' => substr($botResponse, 0, 1000), // Limit length
            'response_template' => $responseTemplate,
            'session_start' => $this->sessionStart ?? $now,
            'interaction_time' => $now,
            'time_in_menu' => $timeInMenu,
            'exit_type' => 'active',
            'from_number' => $this->extractFromNumber($botResponse),
            'has_media' => $hasMedia,
            'message_id' => $messageId,
            'created_at' => $now,
        ]);

        $this->lastInteractionTime = $now;
    }

    /**
     * End a chatbot session
     */
    public function endSession(string $phone, string $exitType = 'explicit'): void
    {
        if (!$this->sessionId) {
            $this->sessionId = $this->getCurrentSessionId($phone);
        }

        if ($this->sessionId) {
            DB::table('chatbot_sessions_log')
                ->where('session_id', $this->sessionId)
                ->update([
                    'session_end' => Carbon::now(),
                    'exit_type' => $exitType,
                    'completed_successfully' => ($exitType === 'explicit'),
                ]);
        }

        // Reset session
        $this->sessionId = null;
        $this->sessionStart = null;
        $this->lastInteractionTime = null;
    }

    /**
     * Get current active session ID for a phone number
     */
    protected function getCurrentSessionId(string $phone): ?string
    {
        $session = DB::table('chatbot_sessions_log')
            ->where('phone', $this->normalizePhone($phone))
            ->where('exit_type', 'active')
            ->where('session_start', '>=', Carbon::now()->subMinutes(30))
            ->orderBy('session_start', 'desc')
            ->first();

        return $session->session_id ?? null;
    }

    /**
     * Get or create session ID
     */
    protected function getOrCreateSessionId(string $phone): string
    {
        $sessionId = $this->getCurrentSessionId($phone);
        
        if (!$sessionId) {
            $sessionId = (string) Str::uuid();
            $this->sessionStart = Carbon::now();
        }

        return $sessionId;
    }

    /**
     * Mark timed out sessions
     */
    public function markTimedOutSessions(): int
    {
        return DB::table('chatbot_sessions_log')
            ->where('exit_type', 'active')
            ->where('session_start', '<', Carbon::now()->subMinutes(30))
            ->update([
                'session_end' => Carbon::now(),
                'exit_type' => 'timeout',
            ]);
    }

    /**
     * Normalize phone number to 10 digits
     */
    protected function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        return substr($phone, -10);
    }

    /**
     * Extract from number from bot response (if logged)
     */
    protected function extractFromNumber(string $response): string
    {
        // Default to primary number
        return '+14067524335';
    }

    /**
     * Get session statistics
     */
    public function getSessionStats(string $phone): array
    {
        $stats = DB::table('chatbot_sessions_log')
            ->where('phone', $this->normalizePhone($phone))
            ->selectRaw('
                COUNT(DISTINCT session_id) as total_sessions,
                AVG(TIMESTAMPDIFF(SECOND, session_start, session_end)) as avg_session_duration,
                SUM(CASE WHEN exit_type = "explicit" THEN 1 ELSE 0 END) as explicit_exits,
                SUM(CASE WHEN exit_type = "timeout" THEN 1 ELSE 0 END) as timeouts
            ')
            ->first();

        return (array) $stats;
    }

    /**
     * Get popular menu options
     */
    public function getPopularMenus(int $days = 7): array
    {
        return DB::table('chatbot_sessions_log')
            ->where('session_start', '>=', Carbon::now()->subDays($days))
            ->whereNotNull('response_template')
            ->where('response_template', '!=', 'main_menu')
            ->select('response_template', DB::raw('COUNT(*) as count'))
            ->groupBy('response_template')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get()
            ->toArray();
    }
}

