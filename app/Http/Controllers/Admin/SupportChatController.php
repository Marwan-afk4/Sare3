<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\FirebaseChatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SupportChatController extends Controller
{
    protected $firebaseService;

    public function __construct(FirebaseChatService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    public function index()
    {
        try {
            // Get statistics for the dashboard
            $stats = $this->getSupportStatistics();
            
            return view('admin.support-chat.index', compact('stats'));
        } catch (\Exception $e) {
            Log::error('Support chat index error: ' . $e->getMessage());
            
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
            Log::info('SupportChatController: getActiveSupportRequests called');
            
            // Get all chats from Firebase
            $firebaseChats = $this->firebaseService->getAllChats();
            $activeSupportRequests = [];
            
            Log::info('Firebase chats retrieved', [
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
                        $unreadCount = 0;
                        
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
                            
                            // Count unread messages (messages from user/driver, not admin)
                            foreach ($messages as $message) {
                                if (isset($message['senderType']) && $message['senderType'] !== 'admin') {
                                    $unreadCount++;
                                }
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
                            'updated_at' => $lastMessageAt,
                            'unread_count' => $unreadCount
                        ];
                    }
                }

                // Sort by last message time (most recent first)
                usort($activeSupportRequests, function($a, $b) {
                    return strtotime($b['last_message_at'] ?? '1970-01-01') - strtotime($a['last_message_at'] ?? '1970-01-01');
                });
            }

            Log::info('Returning support requests', [
                'count' => count($activeSupportRequests)
            ]);

            return response()->json([
                'success' => true,
                'data' => $activeSupportRequests
            ]);

        } catch (\Exception $e) {
            Log::error('SupportChatController error', [
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
        try {
            $roomId = "admin_{$targetId}";
            
            Log::info('getChatMessages called', [
                'targetId' => $targetId,
                'targetType' => $targetType,
                'roomId' => $roomId
            ]);
            
            // Get messages directly from Firebase
            $firebaseMessages = $this->firebaseService->getMessages($roomId, 100);
            $messages = [];

            Log::info('Firebase messages retrieved', [
                'roomId' => $roomId,
                'firebaseMessages' => $firebaseMessages,
                'isEmpty' => empty($firebaseMessages),
                'count' => is_array($firebaseMessages) ? count($firebaseMessages) : 0
            ]);

            if (!empty($firebaseMessages)) {
                foreach ($firebaseMessages as $messageId => $messageData) {
                    Log::info('Processing message', [
                        'messageId' => $messageId,
                        'messageData' => $messageData
                    ]);
                    
                    $messages[] = [
                        'id' => $messageId,
                        'sender_id' => $messageData['senderId'] ?? null,
                        'receiver_id' => $messageData['receiverId'] ?? null,
                        'sender_type' => $messageData['senderType'] ?? null,
                        'receiver_type' => $messageData['receiverType'] ?? null,
                        'message' => $messageData['text'] ?? '',
                        'timestamp' => isset($messageData['timestamp']) 
                            ? date('Y-m-d H:i:s', $messageData['timestamp'] / 1000) 
                            : null,
                        'status' => $messageData['status'] ?? 'sent',
                        'is_admin_message' => ($messageData['senderType'] ?? null) === 'admin'
                    ];
                }

                // Sort messages by timestamp
                usort($messages, function($a, $b) {
                    return strtotime($a['timestamp'] ?? '1970-01-01') - strtotime($b['timestamp'] ?? '1970-01-01');
                });
            } else {
                Log::warning('No messages found in Firebase for room', [
                    'roomId' => $roomId,
                    'firebaseMessages' => $firebaseMessages
                ]);
            }

            Log::info('Final messages to return', [
                'messagesCount' => count($messages),
                'messages' => $messages
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'messages' => $messages,
                    'room_id' => $roomId,
                    'target_info' => $this->getTargetInfo($targetId, $targetType)
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('getChatMessages error: ' . $e->getMessage(), [
                'targetId' => $targetId,
                'targetType' => $targetType,
                'trace' => $e->getTraceAsString()
            ]);
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
            Log::error('sendReply error: ' . $e->getMessage());
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
        try {
            // Get all chats from Firebase
            $firebaseChats = $this->firebaseService->getAllChats();
            $totalRequests = 0;
            $todayRequests = 0;
            $thisWeekRequests = 0;
            $thisMonthRequests = 0;
            $totalUnreadMessages = 0;
            $userConversations = 0;
            $driverConversations = 0;

            $today = now()->startOfDay();
            $weekStart = now()->startOfWeek();
            $monthStart = now()->startOfMonth();

            if (!empty($firebaseChats)) {
                foreach ($firebaseChats as $roomId => $chatData) {
                    // Only count admin chats
                    if (strpos($roomId, 'admin_') === 0) {
                        $totalRequests++;
                        
                        $userId = str_replace('admin_', '', $roomId);
                        $user = User::find($userId);
                        $userType = 'user';
                        
                        if ($user && $user->isDriver()) {
                            $userType = 'driver';
                            $driverConversations++;
                        } else {
                            $userConversations++;
                        }

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
                            
                            // Count unread messages (messages from user/driver, not admin)
                            foreach ($messages as $message) {
                                if (isset($message['senderType']) && $message['senderType'] !== 'admin') {
                                    $totalUnreadMessages++;
                                }
                            }
                        }
                    }
                }
            }

            return [
                'total_requests' => $totalRequests,
                'pending_requests' => $totalRequests, // All Firebase chats are considered pending
                'in_progress_requests' => 0,
                'resolved_requests' => 0,
                'closed_requests' => 0,
                'today_requests' => $todayRequests,
                'this_week_requests' => $thisWeekRequests,
                'this_month_requests' => $thisMonthRequests,
                'total_unread_messages' => $totalUnreadMessages,
                'user_conversations' => $userConversations,
                'driver_conversations' => $driverConversations
            ];
        } catch (\Exception $e) {
            Log::error('getSupportStatistics error: ' . $e->getMessage());
            
            return [
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
        }
    }

    public function testFirebaseConnection()
    {
        try {
            // Test Firebase connection by trying to send a test message
            $roomId = "admin_3"; // Test room
            $testMessage = [
                'senderId' => 1,
                'receiverId' => 3,
                'senderType' => 'admin',
                'receiverType' => 'user',
                'text' => 'Test message from admin - ' . now()->format('Y-m-d H:i:s'),
                'timestamp' => now()->timestamp * 1000,
                'status' => 'sent'
            ];
            
            Log::info('Sending test message to Firebase', [
                'roomId' => $roomId,
                'message' => $testMessage
            ]);
            
            $messageId = $this->firebaseService->sendMessage($roomId, $testMessage);
            
            Log::info('Test message sent', ['messageId' => $messageId]);
            
            // Now try to retrieve messages
            $messages = $this->firebaseService->getMessages($roomId, 10);
            
            return response()->json([
                'success' => true,
                'message' => 'Firebase test completed',
                'data' => [
                    'sent_message_id' => $messageId,
                    'retrieved_messages' => $messages,
                    'messages_count' => is_array($messages) ? count($messages) : 0
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Firebase test failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Firebase test failed: ' . $e->getMessage()
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
                'created_at' => $user->created_at->format('Y-m-d H:i:s')
            ];
        } catch (\Exception $e) {
            return null;
        }
    }
}