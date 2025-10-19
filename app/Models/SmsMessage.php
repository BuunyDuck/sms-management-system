<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsMessage extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'cat_sms';

    /**
     * The primary key associated with the table.
     */
    protected $primaryKey = 'id';

    /**
     * Indicates if the model should be timestamped.
     * The table uses thetime instead of created_at/updated_at
     */
    public $timestamps = false;

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'thetime' => 'datetime',
        'NUMMEDIA' => 'integer',
    ];
    
    /**
     * Date column for ordering
     */
    const CREATED_AT = 'thetime';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'FROM',
        'fromname',
        'TO',
        'toname',
        'BODY',
        'MESSAGESID',
        'ACCOUNTSID',
        'MESSAGINGSERVICESID',
        'NUMMEDIA',
        'NUMSEGMENTS',
        'MESSAGESTATUS',
        'SMSSTATUS',
        'APIVERSION',
        'mediaurllist',
        'mediatypelist',
        'FROMCITY',
        'FROMSTATE',
        'FROMZIP',
        'FROMCOUNTRY',
        'TOCITY',
        'TOSTATE',
        'TOZIP',
        'TOCOUNTRY',
        'custsku',
        'user_id',
        'ticketid',
        'replies_to_support',
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
        $twilioNumber = config('services.twilio.from_number');
        return $this->FROM && $this->FROM === $twilioNumber;
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
    public function getContactNumberAttribute(): ?string
    {
        return $this->isInbound() ? ($this->FROM ?? 'Unknown') : ($this->TO ?? 'Unknown');
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
                $subQ->where('FROM', $phoneNumber)
                     ->where('TO', $twilioNumber);
            })
            // Outbound: From us to customer
            ->orWhere(function ($subQ) use ($phoneNumber, $twilioNumber) {
                $subQ->where('FROM', $twilioNumber)
                     ->where('TO', $phoneNumber);
            });
        });
    }

    /**
     * Scope: Order by date created
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('thetime', 'desc');
    }

    /**
     * Scope: Order oldest first
     */
    public function scopeOldest($query)
    {
        return $query->orderBy('thetime', 'asc');
    }

    /**
     * Get customer information associated with this message's contact number
     */
    public function getCustomerInfo(): ?object
    {
        // Get the contact's phone number (not our Twilio number)
        $phoneNumber = $this->contact_number;
        
        if (!$phoneNumber || $phoneNumber === 'Unknown') {
            return null;
        }
        
        // Get last 10 digits of phone number
        $last10 = substr(preg_replace('/[^0-9]/', '', $phoneNumber), -10);
        
        // Query cat_customer_to_phone to find customer SKU
        $customerPhone = \DB::table('cat_customer_to_phone')
            ->where('phone', $last10)
            ->orderBy('is_primary_record_for_cat_sms', 'DESC')
            ->first();
        
        if (!$customerPhone) {
            return null;
        }
        
        // Get customer details from db_297_netcustomers
        $customer = \DB::table('db_297_netcustomers')
            ->where('sku', $customerPhone->customer_sku)
            ->first();
        
        return $customer;
    }
}

