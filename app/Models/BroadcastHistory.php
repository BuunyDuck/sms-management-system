<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BroadcastHistory extends Model
{
    protected $table = 'broadcast_history';

    protected $fillable = [
        'user_id',
        'user_name',
        'from_number',
        'sent_at',
        'quick_response_id',
        'quick_response_title',
        'message_body',
        'recipients_count',
        'success_count',
        'failure_count',
        'total_cost',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'total_cost' => 'decimal:2',
    ];

    /**
     * Get the user who sent this broadcast
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the quick response if one was used
     */
    public function quickResponse()
    {
        return $this->belongsTo(ChatbotResponse::class, 'quick_response_id');
    }
}
