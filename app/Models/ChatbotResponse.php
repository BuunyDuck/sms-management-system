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
        'footer',
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
     * Get the full message with media tag if image exists (FOR QUICK RESPONSES - NO FOOTER)
     */
    public function getFullMessageAttribute(): string
    {
        $message = $this->message;
        
        if ($this->image_path && Storage::disk('public')->exists($this->image_path)) {
            $imageUrl = Storage::disk('public')->url($this->image_path);
            $message .= "\n\n<media>{$imageUrl}</media>";
        }
        
        return $message;
    }

    /**
     * Get the full message with footer and media tag (FOR CHATBOT)
     */
    public function getFullMessageWithFooterAttribute(): string
    {
        $message = $this->message;
        
        // Add footer if present
        if (!empty($this->footer)) {
            $message .= "\n\n" . $this->footer;
        }
        
        // Add media tag if image exists
        if ($this->image_path && Storage::disk('public')->exists($this->image_path)) {
            $imageUrl = Storage::disk('public')->url($this->image_path);
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
        
        return Storage::disk('public')->exists($this->image_path) 
            ? Storage::disk('public')->url($this->image_path) 
            : null;
    }

    /**
     * Delete the associated image file
     */
    public function deleteImage(): bool
    {
        if ($this->image_path && Storage::disk('public')->exists($this->image_path)) {
            return Storage::disk('public')->delete($this->image_path);
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

