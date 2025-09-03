<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\SupportRequest;
use App\Models\User;
use App\Services\FirebaseChatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SupportChatController extends Controller
{
    protected $firebaseService;

    public function __construct(FirebaseChatService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    public function sendMessage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'message' => 'required|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        $userType = $user->hasRole('driver') ? 'driver' : 'user';

        try {
            $roomId = "admin_{$user->id}";

            // Prepare message data for Firebase
            $messageData = [
                'senderId' => (int) $user->id,
                'receiverId' => 1, // Admin ID (already integer)
                'senderType' => $userType,
                'receiverType' => 'admin',
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

            // Create Firebase support request entry
            $this->firebaseService->createSupportRequest($user->id, $userType);

            return response()->json([
                'success' => true,
                'message' => 'Message sent successfully',
                'data' => [
                    'room_id' => $roomId,
                    'firebase_message_id' => $firebaseMessageId,
                    'message_data' => $messageData
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send message: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getMessages(Request $request)
    {
        $user = Auth::user();
        $userType = $user->hasRole('driver') ? 'driver' : 'user';

        try {
            $roomId = "admin_{$user->id}";
            
            // Get messages directly from Firebase
            $firebaseMessages = $this->firebaseService->getMessages($roomId, 50);
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
                        'status' => $messageData['status'] ?? 'sent'
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
                    'room_id' => $roomId
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get messages: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getChatStatus()
    {
        $user = Auth::user();
        $userType = $user->hasRole('driver') ? 'driver' : 'user';

        try {
            $roomId = "admin_{$user->id}";
            
            // Check if chat exists in Firebase
            $chatData = $this->firebaseService->getChatByRoomId($roomId);
            $hasActiveChat = !empty($chatData) && isset($chatData['messages']);

            return response()->json([
                'success' => true,
                'data' => [
                    'has_active_chat' => $hasActiveChat,
                    'room_id' => $roomId,
                    'status' => $hasActiveChat ? 'pending' : null
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get chat status: ' . $e->getMessage()
            ], 500);
        }
    }
}