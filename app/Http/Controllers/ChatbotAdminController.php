<?php

namespace App\Http\Controllers;

use App\Models\ChatbotResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ChatbotAdminController extends Controller
{
    /**
     * Ensure user is admin
     */
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!auth()->user()->is_admin) {
                abort(403, 'Unauthorized. Admin access required.');
            }
            return $next($request);
        });
    }

    /**
     * Display listing of all chatbot responses
     */
    public function index()
    {
        $responses = ChatbotResponse::ordered()->get();
        
        return view('admin.chatbot.index', compact('responses'));
    }

    /**
     * Show create form
     */
    public function create()
    {
        // Get available menu numbers (1-20)
        $usedNumbers = ChatbotResponse::pluck('menu_number')->toArray();
        $availableNumbers = array_diff(range(1, 20), $usedNumbers);
        
        return view('admin.chatbot.create', compact('availableNumbers'));
    }

    /**
     * Store new response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'menu_number' => 'required|integer|between:1,20|unique:chatbot_responses,menu_number',
            'title' => 'required|string|max:100',
            'message' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB max
            'active' => 'boolean',
        ]);

        $validated['active'] = $request->has('active');
        $validated['display_order'] = $validated['menu_number']; // Default to menu number

        // Handle image upload
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('chatbot-images', 'public');
            $validated['image_path'] = $path;
        }

        $response = ChatbotResponse::create($validated);

        Log::info('🤖 Chatbot response created by admin', [
            'admin' => auth()->user()->name,
            'menu_number' => $response->menu_number,
            'title' => $response->title,
        ]);

        return redirect()
            ->route('admin.chatbot.index')
            ->with('success', '✅ Response created successfully!');
    }

    /**
     * Show edit form
     */
    public function edit(ChatbotResponse $chatbotResponse)
    {
        // Get available menu numbers (including current)
        $usedNumbers = ChatbotResponse::where('id', '!=', $chatbotResponse->id)
            ->pluck('menu_number')
            ->toArray();
        $availableNumbers = array_diff(range(1, 20), $usedNumbers);
        
        return view('admin.chatbot.edit', compact('chatbotResponse', 'availableNumbers'));
    }

    /**
     * Update response
     */
    public function update(Request $request, ChatbotResponse $chatbotResponse)
    {
        $validated = $request->validate([
            'menu_number' => 'required|integer|between:1,20|unique:chatbot_responses,menu_number,' . $chatbotResponse->id,
            'title' => 'required|string|max:100',
            'message' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'remove_image' => 'boolean',
            'active' => 'boolean',
        ]);

        $validated['active'] = $request->has('active');

        // Handle image removal
        if ($request->input('remove_image')) {
            $chatbotResponse->deleteImage();
            $validated['image_path'] = null;
        }

        // Handle new image upload
        if ($request->hasFile('image')) {
            // Delete old image
            $chatbotResponse->deleteImage();
            
            // Upload new image
            $path = $request->file('image')->store('chatbot-images', 'public');
            $validated['image_path'] = $path;
        }

        $chatbotResponse->update($validated);

        Log::info('🤖 Chatbot response updated by admin', [
            'admin' => auth()->user()->name,
            'menu_number' => $chatbotResponse->menu_number,
            'title' => $chatbotResponse->title,
        ]);

        return redirect()
            ->route('admin.chatbot.index')
            ->with('success', '✅ Response updated successfully!');
    }

    /**
     * Delete response
     */
    public function destroy(ChatbotResponse $chatbotResponse)
    {
        $menuNumber = $chatbotResponse->menu_number;
        $title = $chatbotResponse->title;

        // Delete associated image
        $chatbotResponse->deleteImage();

        // Delete response
        $chatbotResponse->delete();

        Log::info('🤖 Chatbot response deleted by admin', [
            'admin' => auth()->user()->name,
            'menu_number' => $menuNumber,
            'title' => $title,
        ]);

        return redirect()
            ->route('admin.chatbot.index')
            ->with('success', '✅ Response deleted successfully!');
    }

    /**
     * Preview response (AJAX)
     */
    public function preview(Request $request)
    {
        $message = $request->input('message', '');
        $hasImage = $request->boolean('has_image');

        $preview = $message;
        if ($hasImage) {
            $preview .= "\n\n[Image will be attached]";
        }

        return response()->json([
            'preview' => $preview,
        ]);
    }

    /**
     * Reorder responses
     */
    public function reorder(Request $request)
    {
        $order = $request->input('order', []);

        foreach ($order as $index => $id) {
            ChatbotResponse::where('id', $id)->update([
                'display_order' => $index + 1,
            ]);
        }

        Log::info('🤖 Chatbot responses reordered by admin', [
            'admin' => auth()->user()->name,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Order updated successfully',
        ]);
    }
}

