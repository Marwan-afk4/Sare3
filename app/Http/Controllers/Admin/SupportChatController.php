<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportRequest;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupportChatController extends Controller
{
    public function index()
    {
        try {
            // Get statistics for the dashboard
            $stats = $this->getSupportStatistics();
            
            return view('admin.support-chat.index', compact('stats'));
        } catch (\Exception $e) {
            // Fallback with empty stats if there's an error
            $stats = [
                'total_requests' => 0,
                'pending_requests' => 0,
                'in_progress_requests' => 0,
                'resolved_requests' => 0,
                'closed_requests' => 0,
                'today_requests' => 0,
                'this_week_requests' => 0,
                'this_month_requests' => 0,
                'total_unread_messages' => 0,
                'user_conversations' => 0,
                'driver_conversations' => 0
            ];
            
            return view('admin.support-chat.index', compact('stats'));
        }
    }

    public function getActiveSupportRequests()
    {
        try {
            $supportRequests = SupportRequest::with(['requester', 'chat'])
                ->whereIn('status', [
                    SupportRequest::STATUS_PENDING,
                    SupportRequest::STATUS_IN_PROGRESS
                ])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($request) {
                    $requester = $request->requester;
                    return [
                        'id' => $request->id,
                        'requester_id' => $request->requester_id,
                        'requester_type' => $request->requester_type,
                        'requester_name' => $requester ? $requester->name : 'Unknown User',
                        'requester_email' => $requester ? $requester->email : null,
                        'requester_phone' => $requester ? $requester->phone : null,
                        'status' => $request->status,
                        'priority' => $request->priority,
                        'subject' => $request->subject,
                        'room_id' => $request->chat ? $request->chat->room_id : null,
                        'last_message' => $request->chat ? $request->chat->last_message : null,
                        'last_message_at' => $request->chat && $request->chat->last_message_at 
                            ? $request->chat->last_message_at->format('Y-m-d H:i:s') : null,
                        'created_at' => $request->created_at->format('Y-m-d H:i:s'),
                        'updated_at' => $request->updated_at->format('Y-m-d H:i:s'),
                        'unread_count' => $this->getUnreadCount($request->chat_id)
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $supportRequests
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get support requests: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getChatMessages($targetId, $targetType)
    {
        try {
            $roomId = "admin_{$targetId}";
            $chat = Chat::where('room_id', $roomId)->first();

            if (!$chat) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'messages' => [],
                        'room_id' => $roomId,
                        'target_info' => $this->getTargetInfo($targetId, $targetType)
                    ]
                ]);
            }

            $messages = ChatMessage::where('chat_id', $chat->id)
                ->orderBy('created_at', 'asc')
                ->limit(100)
                ->get()
                ->map(function ($message) {
                    return [
                        'id' => $message->id,
                        'sender_id' => $message->sender_id,
                        'receiver_id' => $message->receiver_id,
                        'sender_type' => $message->sender_type,
                        'receiver_type' => $message->receiver_type,
                        'message' => $message->message,
                        'timestamp' => $message->created_at->format('Y-m-d H:i:s'),
                        'status' => $message->status,
                        'is_admin_message' => $message->sender_type === 'admin'
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => [
                    'messages' => $messages,
                    'room_id' => $roomId,
                    'chat_id' => $chat->id,
                    'target_info' => $this->getTargetInfo($targetId, $targetType)
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get messages: ' . $e->getMessage()
            ], 500);
        }
    }

    public function sendReply(Request $request)
    {
        $request->validate([
            'target_id' => 'required|integer',
            'target_type' => 'required|in:user,driver',
            'message' => 'required|string|max:1000'
        ]);

        try {
            // Use the API controller to send the message
            $apiController = new \App\Http\Controllers\Api\Admin\AdminSupportChatController(
                new \App\Services\FirebaseChatService()
            );
            
            return $apiController->sendReply($request);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send reply: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateSupportRequestStatus(Request $request, $supportRequestId)
    {
        try {
            // Use the API controller to update status
            $apiController = new \App\Http\Controllers\Api\Admin\AdminSupportChatController(
                new \App\Services\FirebaseChatService()
            );
            
            return $apiController->updateSupportRequestStatus($request, $supportRequestId);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update support request: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getStatistics()
    {
        try {
            $stats = $this->getSupportStatistics();
            
            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get statistics: ' . $e->getMessage()
            ], 500);
        }
    }

    private function getSupportStatistics()
    {
        return [
            'total_requests' => SupportRequest::count(),
            'pending_requests' => SupportRequest::where('status', SupportRequest::STATUS_PENDING)->count(),
            'in_progress_requests' => SupportRequest::where('status', SupportRequest::STATUS_IN_PROGRESS)->count(),
            'resolved_requests' => SupportRequest::where('status', SupportRequest::STATUS_RESOLVED)->count(),
            'closed_requests' => SupportRequest::where('status', SupportRequest::STATUS_CLOSED)->count(),
            'today_requests' => SupportRequest::whereDate('created_at', today())->count(),
            'this_week_requests' => SupportRequest::whereBetween('created_at', [
                now()->startOfWeek(),
                now()->endOfWeek()
            ])->count(),
            'this_month_requests' => SupportRequest::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
            'total_unread_messages' => $this->getTotalUnreadMessages(),
            'user_conversations' => SupportRequest::where('requester_type', 'user')
                ->whereIn('status', [SupportRequest::STATUS_PENDING, SupportRequest::STATUS_IN_PROGRESS])
                ->count(),
            'driver_conversations' => SupportRequest::where('requester_type', 'driver')
                ->whereIn('status', [SupportRequest::STATUS_PENDING, SupportRequest::STATUS_IN_PROGRESS])
                ->count()
        ];
    }

    private function getTotalUnreadMessages()
    {
        // Count messages where admin hasn't replied yet or recent user messages
        return ChatMessage::where('sender_type', '!=', 'admin')
            ->where('created_at', '>', now()->subDays(7))
            ->whereDoesntHave('chat.messages', function($query) {
                $query->where('sender_type', 'admin')
                    ->where('created_at', '>', now()->subHours(1));
            })
            ->count();
    }

    private function getUnreadCount($chatId)
    {
        if (!$chatId) return 0;
        
        return ChatMessage::where('chat_id', $chatId)
            ->where('sender_type', '!=', 'admin')
            ->where('created_at', '>', now()->subDays(1))
            ->count();
    }

    private function getTargetInfo($targetId, $targetType)
    {
        try {
            $user = User::find($targetId);
            
            if (!$user) {
                return null;
            }

            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'type' => $targetType,
                'created_at' => $user->created_at->format('Y-m-d H:i:s')
            ];
        } catch (\Exception $e) {
            return null;
        }
    }
}