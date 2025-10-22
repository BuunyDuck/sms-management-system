<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ChatbotResponse extends Model
{
    protected $fillable = [
        'menu_number',
        'title',
        'message',
        'template_file',
        'image_path',
        'active',
        'display_order',
    ];

    protected $casts = [
        'active' => 'boolean',
        'menu_number' => 'integer',
        'display_order' => 'integer',
    ];

    /**
     * Scope: Only active responses
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope: Order by menu number
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order')->orderBy('menu_number');
    }

    /**
     * Get the full message with media tag if image exists
     */
    public function getFullMessageAttribute(): string
    {
        $message = $this->message;
        
        if ($this->image_path && Storage::exists($this->image_path)) {
            $imageUrl = Storage::url($this->image_path);
            $message .= "\n\n<media>{$imageUrl}</media>";
        }
        
        return $message;
    }

    /**
     * Get the public URL for the image
     */
    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image_path) {
            return null;
        }
        
        return Storage::exists($this->image_path) 
            ? Storage::url($this->image_path) 
            : null;
    }

    /**
     * Delete the associated image file
     */
    public function deleteImage(): bool
    {
        if ($this->image_path && Storage::exists($this->image_path)) {
            return Storage::delete($this->image_path);
        }
        
        return false;
    }

    /**
     * Parse media tags from message text
     */
    public function getMediaUrl(): ?string
    {
        if (preg_match('/<media>(.*?)<\/media>/s', $this->message, $matches)) {
            return $matches[1] ?? null;
        }
        
        return null;
    }

    /**
     * Get message without media tags
     */
    public function getMessageWithoutMediaAttribute(): string
    {
        return preg_replace('/<media>.*?<\/media>/s', '', $this->message);
    }
}

