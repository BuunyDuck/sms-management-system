<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

/**
 * BotSession Model
 * 
 * Manages chatbot session state in the smsbot table.
 * Tracks which menu a user is currently viewing and session activity.
 */
class BotSession extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'smsbot';

    /**
     * The primary key for the model.
     */
    protected $primaryKey = 'phone';

    /**
     * Indicates if the IDs are auto-incrementing.
     */
    public $incrementing = false;

    /**
     * The data type of the primary key.
     */
    protected $keyType = 'string';

    /**
     * The storage format of the model's date columns.
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    /**
     * The name of the "created at" column.
     */
    const CREATED_AT = null;

    /**
     * The name of the "updated at" column.
     */
    const UPDATED_AT = 'updated_dt';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'phone',
        'menu',
        'updated_dt',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'updated_dt' => 'datetime',
    ];

    /**
     * Session timeout in minutes (30 minutes default)
     */
    const SESSION_TIMEOUT_MINUTES = 30;

    /**
     * Check if the session is still active (within timeout window)
     */
    public function isActive(): bool
    {
        if (empty($this->menu)) {
            return false;
        }

        $timeoutThreshold = Carbon::now()->subMinutes(self::SESSION_TIMEOUT_MINUTES);
        
        return $this->updated_dt && $this->updated_dt->greaterThan($timeoutThreshold);
    }

    /**
     * Check if the session has expired
     */
    public function isExpired(): bool
    {
        return !$this->isActive();
    }

    /**
     * Get the menu path as an array
     */
    public function getMenuPath(): array
    {
        if (empty($this->menu)) {
            return [];
        }

        return explode(',', $this->menu);
    }

    /**
     * Set the menu path from an array
     */
    public function setMenuPath(array $path): void
    {
        $this->menu = implode(',', $path);
    }

    /**
     * Clear the session (for EXIT or timeout)
     */
    public function clear(): void
    {
        $this->menu = '';
        $this->updated_dt = Carbon::now();
        $this->save();
    }

    /**
     * Get the current menu level
     * 
     * @return int Number of levels deep (0 = no session, 1 = main menu, etc.)
     */
    public function getCurrentLevel(): int
    {
        return count($this->getMenuPath());
    }

    /**
     * Check if user is at main menu
     */
    public function isAtMainMenu(): bool
    {
        $path = $this->getMenuPath();
        return count($path) === 1 && $path[0] === 'm';
    }

    /**
     * Get the last menu option selected
     */
    public function getLastOption(): ?string
    {
        $path = $this->getMenuPath();
        return empty($path) ? null : end($path);
    }

    /**
     * Append an option to the menu path
     */
    public function appendOption(string $option): void
    {
        $path = $this->getMenuPath();
        $path[] = $option;
        $this->setMenuPath($path);
        $this->updated_dt = Carbon::now();
        $this->save();
    }

    /**
     * Start a new session at main menu
     */
    public function startMainMenu(): void
    {
        $this->menu = 'm';
        $this->updated_dt = Carbon::now();
        $this->save();
    }

    /**
     * Get a session by phone number, or create a new one
     */
    public static function findOrCreateByPhone(string $phone): self
    {
        return self::firstOrCreate(
            ['phone' => $phone],
            [
                'menu' => '',
                'updated_dt' => Carbon::now()
            ]
        );
    }

    /**
     * Find an active session by phone number
     */
    public static function findActiveByPhone(string $phone): ?self
    {
        $session = self::find($phone);
        
        if (!$session || $session->isExpired()) {
            return null;
        }

        return $session;
    }
}

