<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsMessage extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'cat_sms_dev';

    /**
     * The primary key associated with the table.
     */
    protected $primaryKey = 'ID';

    /**
     * Indicates if the model should be timestamped.
     * The table uses DateCreated instead of created_at/updated_at
     */
    public $timestamps = false;

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'DateCreated' => 'datetime',
        'nummedia' => 'integer',
    ];

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'From',
        'To',
        'Body',
        'MessageSid',
        'AccountSid',
        'MessagingServiceSid',
        'NumMedia',
        'NumSegments',
        'Status',
        'Direction',
        'ApiVersion',
        'nummedia',
        'mediaurllist',
        'mediatypelist',
        'FromCity',
        'FromState',
        'FromZip',
        'FromCountry',
        'ToCity',
        'ToState',
        'ToZip',
        'ToCountry',
    ];

    /**
     * Get media URLs as an array
     */
    public function getMediaUrlsAttribute(): array
    {
        if (empty($this->mediaurllist)) {
            return [];
        }
        return explode("\t", $this->mediaurllist);
    }

    /**
     * Get media types as an array
     */
    public function getMediaTypesAttribute(): array
    {
        if (empty($this->mediatypelist)) {
            return [];
        }
        return explode("\t", $this->mediatypelist);
    }

    /**
     * Get media attachments as array of objects
     */
    public function getMediaAttachmentsAttribute(): array
    {
        $urls = $this->media_urls;
        $types = $this->media_types;
        
        $attachments = [];
        for ($i = 0; $i < count($urls); $i++) {
            $attachments[] = [
                'url' => $urls[$i] ?? null,
                'type' => $types[$i] ?? 'unknown',
            ];
        }
        
        return $attachments;
    }

    /**
     * Check if message is outbound (sent by us)
     */
    public function isOutbound(): bool
    {
        // Messages from our Twilio number are outbound
        return $this->From === config('services.twilio.from_number');
    }

    /**
     * Check if message is inbound (received from customer)
     */
    public function isInbound(): bool
    {
        return !$this->isOutbound();
    }

    /**
     * Get the other party's phone number (not our Twilio number)
     */
    public function getContactNumberAttribute(): string
    {
        return $this->isInbound() ? $this->From : $this->To;
    }

    /**
     * Format phone number for display
     */
    public function getFormattedContactNumberAttribute(): string
    {
        $number = $this->contact_number;
        
        // Format +14065551234 as (406) 555-1234
        if (preg_match('/^\+1(\d{3})(\d{3})(\d{4})$/', $number, $matches)) {
            return "({$matches[1]}) {$matches[2]}-{$matches[3]}";
        }
        
        return $number;
    }

    /**
     * Scope: Get messages for a specific phone number (conversation)
     */
    public function scopeForNumber($query, string $phoneNumber)
    {
        $twilioNumber = config('services.twilio.from_number');
        
        return $query->where(function ($q) use ($phoneNumber, $twilioNumber) {
            // Inbound: From customer to us
            $q->where(function ($subQ) use ($phoneNumber, $twilioNumber) {
                $subQ->where('From', $phoneNumber)
                     ->where('To', $twilioNumber);
            })
            // Outbound: From us to customer
            ->orWhere(function ($subQ) use ($phoneNumber, $twilioNumber) {
                $subQ->where('From', $twilioNumber)
                     ->where('To', $phoneNumber);
            });
        });
    }

    /**
     * Scope: Order by date created
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('DateCreated', 'desc');
    }

    /**
     * Scope: Order oldest first
     */
    public function scopeOldest($query)
    {
        return $query->orderBy('DateCreated', 'asc');
    }
}

