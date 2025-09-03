<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\SupportRequest;
use App\Models\User;
use App\Services\FirebaseChatService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AdminSupportChatController extends Controller
{
    protected $firebaseService;

    public function __construct(FirebaseChatService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    public function getActiveSupportRequests()
    {
        try {
            \Log::info('AdminSupportChatController: getActiveSupportRequests called', [
                'user_id' => Auth::id(),
                'user_email' => Auth::user()->email ?? 'N/A'
            ]);
            
            // Get all chats from Firebase
            $firebaseChats = $this->firebaseService->getAllChats();
            $activeSupportRequests = [];
            
            \Log::info('Firebase chats retrieved', [
                'count' => count($firebaseChats ?? [])
            ]);

            if (!empty($firebaseChats)) {
                foreach ($firebaseChats as $roomId => $chatData) {
                    
                    // Only process admin chats (room_id format: admin_3)
                    if (strpos($roomId, 'admin_') === 0) {
                        $userId = str_replace('admin_', '', $roomId);
                        
                        // Get user info from database
                        $user = User::find($userId);
                        
                        // If user doesn't exist, create a placeholder
                        if (!$user) {
                            $userName = "Unknown User (ID: {$userId})";
                            $userEmail = null;
                            $userPhone = null;
                            $userType = 'user'; // Default to user type
                        } else {
                            $userName = $user->name;
                            $userEmail = $user->email;
                            $userPhone = $user->phone;
                            // Determine user type based on role
                            $userType = $user->isDriver() ? 'driver' : 'user';
                        }

                        // Get last message from chat data
                        $lastMessage = null;
                        $lastMessageAt = null;
                        
                        if (isset($chatData['messages']) && is_array($chatData['messages'])) {
                            $messages = $chatData['messages'];
                            $lastMessageKey = array_key_last($messages);
                            if ($lastMessageKey && isset($messages[$lastMessageKey])) {
                                $lastMessageData = $messages[$lastMessageKey];
                                $lastMessage = $lastMessageData['text'] ?? null;
                                $lastMessageAt = isset($lastMessageData['timestamp']) 
                                    ? date('Y-m-d H:i:s', $lastMessageData['timestamp'] / 1000) 
                                    : null;
                            }
                        }

                        $activeSupportRequests[] = [
                            'id' => $roomId,
                            'requester_id' => $userId,
                            'requester_type' => $userType,
                            'requester_name' => $userName,
                            'requester_email' => $userEmail,
                            'requester_phone' => $userPhone,
                            'status' => 'pending', // Default status for Firebase chats
                            'priority' => 'medium', // Default priority
                            'subject' => 'Support Request',
                            'room_id' => $roomId,
                            'last_message' => $lastMessage,
                            'last_message_at' => $lastMessageAt,
                            'created_at' => $lastMessageAt, // Use last message time as created time
                            'updated_at' => $lastMessageAt
                        ];
                    }
                }

                // Sort by last message time (most recent first)
                usort($activeSupportRequests, function($a, $b) {
                    return strtotime($b['last_message_at'] ?? '1970-01-01') - strtotime($a['last_message_at'] ?? '1970-01-01');
                });
            }

            \Log::info('Returning support requests', [
                'count' => count($activeSupportRequests)
            ]);

            return response()->json([
                'success' => true,
                'data' => $activeSupportRequests
            ]);

        } catch (\Exception $e) {
            \Log::error('AdminSupportChatController error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to get support requests: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getChatMessages($targetId, $targetType)
    {
        $validator = Validator::make([
            'target_id' => $targetId,
            'target_type' => $targetType
        ], [
            'target_id' => 'required|integer',
            'target_type' => 'required|in:user,driver'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid parameters',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $roomId = "admin_{$targetId}";
            
            // Get messages directly from Firebase
            $firebaseMessages = $this->firebaseService->getMessages($roomId, 100);
            $messages = [];

            if (!empty($firebaseMessages)) {
                foreach ($firebaseMessages as $messageId => $messageData) {
                    $messages[] = [
                        'id' => $messageId,
                        'senderId' => $messageData['senderId'] ?? null,
                        'receiverId' => $messageData['receiverId'] ?? null,
                        'senderType' => $messageData['senderType'] ?? null,
                        'receiverType' => $messageData['receiverType'] ?? null,
                        'text' => $messageData['text'] ?? '',
                        'timestamp' => $messageData['timestamp'] ?? null,
                        'status' => $messageData['status'] ?? 'sent',
                        'created_at' => isset($messageData['timestamp']) 
                            ? date('Y-m-d H:i:s', $messageData['timestamp'] / 1000) 
                            : null
                    ];
                }

                // Sort messages by timestamp
                usort($messages, function($a, $b) {
                    return ($a['timestamp'] ?? 0) - ($b['timestamp'] ?? 0);
                });
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'messages' => $messages,
                    'room_id' => $roomId,
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
        $validator = Validator::make($request->all(), [
            'target_id' => 'required|integer',
            'target_type' => 'required|in:user,driver',
            'message' => 'required|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $admin = Auth::user();

        try {
            $roomId = "admin_{$request->target_id}";

            // Prepare message data for Firebase
            $messageData = [
                'senderId' => $admin->id,
                'receiverId' => $request->target_id,
                'senderType' => 'admin',
                'receiverType' => $request->target_type,
                'text' => $request->message,
                'timestamp' => now()->timestamp * 1000,
                'status' => 'sent'
            ];

            // Send to Firebase
            $firebaseMessageId = $this->firebaseService->sendMessage($roomId, $messageData);

            if (!$firebaseMessageId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to send message to Firebase'
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Reply sent successfully',
                'data' => [
                    'room_id' => $roomId,
                    'firebase_message_id' => $firebaseMessageId,
                    'message_data' => $messageData
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send reply: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateSupportRequestStatus(Request $request, $supportRequestId)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,in_progress,resolved,closed',
            'priority' => 'sometimes|in:low,medium,high,urgent'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $supportRequest = SupportRequest::findOrFail($supportRequestId);
            
            $updateData = ['status' => $request->status];
            
            if ($request->has('priority')) {
                $updateData['priority'] = $request->priority;
            }

            if ($request->status === SupportRequest::STATUS_RESOLVED || 
                $request->status === SupportRequest::STATUS_CLOSED) {
                $updateData['resolved_at'] = now();
                $updateData['resolved_by'] = Auth::id();
                
                // Remove from Firebase support requests
                $this->firebaseService->removeSupportRequest(
                    $supportRequest->requester_id, 
                    $supportRequest->requester_type
                );
            }

            $supportRequest->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Support request updated successfully',
                'data' => $supportRequest
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update support request: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getSupportStatistics()
    {
        try {
            // Get all chats from Firebase
            $firebaseChats = $this->firebaseService->getAllChats();
            $totalRequests = 0;
            $todayRequests = 0;
            $thisWeekRequests = 0;
            $thisMonthRequests = 0;

            $today = now()->startOfDay();
            $weekStart = now()->startOfWeek();
            $monthStart = now()->startOfMonth();

            if (!empty($firebaseChats)) {
                foreach ($firebaseChats as $roomId => $chatData) {
                    // Only count admin chats
                    if (strpos($roomId, 'admin_') === 0) {
                        $totalRequests++;

                        // Get the first message timestamp to determine creation date
                        if (isset($chatData['messages']) && is_array($chatData['messages'])) {
                            $messages = $chatData['messages'];
                            $firstMessage = reset($messages);
                            
                            if (isset($firstMessage['timestamp'])) {
                                $messageDate = \Carbon\Carbon::createFromTimestamp($firstMessage['timestamp'] / 1000);
                                
                                if ($messageDate->gte($today)) {
                                    $todayRequests++;
                                }
                                
                                if ($messageDate->gte($weekStart)) {
                                    $thisWeekRequests++;
                                }
                                
                                if ($messageDate->gte($monthStart)) {
                                    $thisMonthRequests++;
                                }
                            }
                        }
                    }
                }
            }

            $stats = [
                'total_requests' => $totalRequests,
                'pending_requests' => $totalRequests, // All Firebase chats are considered pending
                'in_progress_requests' => 0,
                'resolved_requests' => 0,
                'closed_requests' => 0,
                'today_requests' => $todayRequests,
                'this_week_requests' => $thisWeekRequests,
                'this_month_requests' => $thisMonthRequests
            ];

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
                'created_at' => $user->created_at
            ];
        } catch (\Exception $e) {
            return null;
        }
    }
}