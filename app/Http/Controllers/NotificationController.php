<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Get all notifications for the current user
     */
    public function index(Request $request): JsonResponse
    {
        $user = auth()->user();
        
        // Get unread count
        $unreadCount = Notification::where('user_id', $user->id)
            ->unread()
            ->count();
        
        // Get recent notifications (last 24 hours or unread)
        $notifications = Notification::where('user_id', $user->id)
            ->where(function ($query) {
                $query->where('read', false)
                      ->orWhere('created_at', '>=', now()->subDay());
            })
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'type' => $notification->type,
                    'phone_number' => $notification->phone_number,
                    'customer_name' => $notification->customer_name,
                    'message_preview' => $notification->message_preview,
                    'read' => $notification->read,
                    'time_ago' => $notification->time_ago,
                    'created_at' => $notification->created_at->toIso8601String(),
                ];
            });
        
        return response()->json([
            'unread_count' => $unreadCount,
            'notifications' => $notifications,
        ]);
    }

    /**
     * Mark a specific notification as read
     */
    public function markAsRead(Request $request, int $id): JsonResponse
    {
        $notification = Notification::where('user_id', auth()->id())
            ->where('id', $id)
            ->firstOrFail();
        
        $notification->markAsRead();
        
        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read',
        ]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $user = auth()->user();
        
        Notification::where('user_id', $user->id)
            ->unread()
            ->update([
                'read' => true,
                'read_at' => now(),
            ]);
        
        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read',
        ]);
    }

    /**
     * Delete a notification
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $notification = Notification::where('user_id', auth()->id())
            ->where('id', $id)
            ->firstOrFail();
        
        $notification->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Notification deleted',
        ]);
    }
}
