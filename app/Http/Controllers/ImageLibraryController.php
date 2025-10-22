<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Models\ChatbotResponse;

class ImageLibraryController extends Controller
{
    /**
     * Check if user is admin
     */
    protected function checkAdmin(): void
    {
        if (!auth()->check() || !auth()->user()->is_admin) {
            abort(403, 'Unauthorized. Admin access required.');
        }
    }

    /**
     * Display image library gallery
     */
    public function index()
    {
        $this->checkAdmin();
        
        // Get all images from storage
        $imageFiles = Storage::disk('public')->files('chatbot-images');
        
        $images = collect($imageFiles)->map(function ($path) {
            $filename = basename($path);
            $url = Storage::disk('public')->url($path);
            $size = Storage::disk('public')->size($path);
            
            // Check which responses use this image
            $usedBy = ChatbotResponse::where('image_path', $path)->get(['id', 'menu_number', 'title']);
            
            return [
                'filename' => $filename,
                'path' => $path,
                'url' => $url,
                'size' => $this->formatBytes($size),
                'used_by' => $usedBy,
                'in_use' => $usedBy->isNotEmpty(),
            ];
        })->sortBy('filename')->values();
        
        return view('admin.chatbot.images.index', compact('images'));
    }

    /**
     * Upload new image
     */
    public function store(Request $request)
    {
        $this->checkAdmin();
        
        $validated = $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB max
        ]);
        
        $file = $request->file('image');
        $originalName = $file->getClientOriginalName();
        
        // Clean filename (remove special chars, spaces)
        $cleanName = $this->cleanFilename($originalName);
        
        // Check if file exists, add number suffix if needed
        $finalName = $this->getUniqueFilename($cleanName);
        
        // Store with original filename
        $path = $file->storeAs('chatbot-images', $finalName, 'public');
        
        Log::info('📸 Image uploaded to library', [
            'original' => $originalName,
            'stored_as' => $finalName,
            'path' => $path,
            'user' => auth()->user()->name,
        ]);
        
        return redirect()
            ->route('admin.chatbot.images.index')
            ->with('success', "✅ Image uploaded: {$finalName}");
    }

    /**
     * Delete image
     */
    public function destroy(Request $request)
    {
        $this->checkAdmin();
        
        $filename = $request->input('filename');
        $path = 'chatbot-images/' . $filename;
        
        // Check if image is in use
        $usedBy = ChatbotResponse::where('image_path', $path)->get();
        
        if ($usedBy->isNotEmpty()) {
            return back()->with('error', '⚠️ Cannot delete image - it is currently used by ' . $usedBy->count() . ' response(s)');
        }
        
        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
            
            Log::info('🗑️ Image deleted from library', [
                'filename' => $filename,
                'user' => auth()->user()->name,
            ]);
            
            return back()->with('success', "✅ Image deleted: {$filename}");
        }
        
        return back()->with('error', '❌ Image not found');
    }

    /**
     * Get list of images for API/AJAX
     */
    public function list()
    {
        $this->checkAdmin();
        
        $imageFiles = Storage::disk('public')->files('chatbot-images');
        
        $images = collect($imageFiles)->map(function ($path) {
            return [
                'filename' => basename($path),
                'path' => $path,
                'url' => Storage::disk('public')->url($path),
            ];
        })->sortBy('filename')->values();
        
        return response()->json($images);
    }

    /**
     * Clean filename (remove special chars, keep extension)
     */
    protected function cleanFilename(string $filename): string
    {
        $info = pathinfo($filename);
        $name = $info['filename'];
        $ext = $info['extension'] ?? 'jpg';
        
        // Replace spaces with hyphens, remove special chars
        $name = preg_replace('/[^a-zA-Z0-9-_]/', '-', $name);
        $name = preg_replace('/-+/', '-', $name); // Remove duplicate hyphens
        $name = trim($name, '-');
        $name = strtolower($name);
        
        return $name . '.' . strtolower($ext);
    }

    /**
     * Get unique filename (add suffix if exists)
     */
    protected function getUniqueFilename(string $filename): string
    {
        $path = 'chatbot-images/' . $filename;
        
        if (!Storage::disk('public')->exists($path)) {
            return $filename;
        }
        
        // File exists, add number suffix
        $info = pathinfo($filename);
        $name = $info['filename'];
        $ext = $info['extension'] ?? 'jpg';
        
        $counter = 2;
        while (Storage::disk('public')->exists("chatbot-images/{$name}-{$counter}.{$ext}")) {
            $counter++;
        }
        
        return "{$name}-{$counter}.{$ext}";
    }

    /**
     * Format bytes to human readable
     */
    protected function formatBytes(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes . ' B';
        } elseif ($bytes < 1048576) {
            return round($bytes / 1024, 2) . ' KB';
        } else {
            return round($bytes / 1048576, 2) . ' MB';
        }
    }
}
