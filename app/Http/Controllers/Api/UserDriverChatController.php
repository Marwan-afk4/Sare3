<?php

namespace App\Http\Controllers\Api;

use App\Events\UserDriverChatMessageSeen;
use App\Events\UserDriverChatMessageSent;
use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UserDriverChatController extends Controller
{
    /**
     * Send a message in a user-driver chat.
     */
    public function sendMessage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'receiver_id' => 'required|exists:users,id',
            'message' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $sender = Auth::user();
        $senderType = $sender->isDriver() ? 'driver' : 'user';

        $receiver = User::find($request->receiver_id);
        $receiverType = $receiver->isDriver() ? 'driver' : 'user';

        // Prevent chat between same roles (e.g. user-user or driver-driver)
        if ($senderType === $receiverType) {
            return response()->json([
                'success' => false,
                'message' => 'Chats are only allowed between a user and a driver.'
            ], 400);
        }

        // Form deterministic room ID: user ID first, then driver ID
        $userId = $senderType === 'user' ? $sender->id : $receiver->id;
        $driverId = $senderType === 'driver' ? $sender->id : $receiver->id;

        try {
            // Find or create the polymorphic chat
            $chat = Chat::findOrCreateChat('user', $userId, 'driver', $driverId);

            // Create message
            $message = ChatMessage::create([
                'chat_id' => $chat->id,
                'sender_id' => $sender->id,
                'sender_type' => $senderType,
                'receiver_id' => $receiver->id,
                'receiver_type' => $receiverType,
                'message' => $request->message,
                'status' => 'sent'
            ]);

            // Update chat room details
            $chat->update([
                'last_message' => $request->message,
                'last_message_at' => now()
            ]);

            // Broadcast real-time message via Reverb presence channel
            broadcast(new UserDriverChatMessageSent($message))->toOthers();

            return response()->json([
                'success' => true,
                'message' => 'Message sent successfully',
                'data' => [
                    'id' => $message->id,
                    'chat_id' => $message->chat_id,
                    'room_id' => $chat->room_id,
                    'sender_id' => $message->sender_id,
                    'sender_type' => $message->sender_type,
                    'receiver_id' => $message->receiver_id,
                    'receiver_type' => $message->receiver_type,
                    'message' => $message->message,
                    'status' => $message->status,
                    'created_at' => $message->created_at->toIso8601String(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send message: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get chat messages between authenticated user and another user.
     */
    public function getMessages(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'receiver_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        $userType = $user->isDriver() ? 'driver' : 'user';

        $receiver = User::find($request->receiver_id);
        $receiverType = $receiver->isDriver() ? 'driver' : 'user';

        if ($userType === $receiverType) {
            return response()->json([
                'success' => false,
                'message' => 'Chats are only allowed between a user and a driver.'
            ], 400);
        }

        $userId = $userType === 'user' ? $user->id : $receiver->id;
        $driverId = $userType === 'driver' ? $user->id : $receiver->id;
        $roomId = "{$userId}_{$driverId}";

        try {
            $chat = Chat::where('room_id', $roomId)->first();

            if (!$chat) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'room_id' => $roomId,
                        'messages' => [],
                        'pagination' => null
                    ]
                ]);
            }

            // Mark unread messages sent by the receiver as read
            $unreadUpdated = ChatMessage::where('chat_id', $chat->id)
                ->where('receiver_id', $user->id)
                ->where('status', '!=', 'read')
                ->update(['status' => 'read']);

            // Broadcast seen event if messages status changed
            if ($unreadUpdated > 0) {
                broadcast(new UserDriverChatMessageSeen($chat->id, $chat->room_id, $user->id, $userType))->toOthers();
            }

            $messages = ChatMessage::where('chat_id', $chat->id)
                ->orderBy('created_at', 'desc')
                ->paginate(30);

            // Format message objects for response
            $formattedMessages = collect($messages->items())->map(function ($msg) {
                return [
                    'id' => $msg->id,
                    'chat_id' => $msg->chat_id,
                    'sender_id' => $msg->sender_id,
                    'sender_type' => $msg->sender_type,
                    'receiver_id' => $msg->receiver_id,
                    'receiver_type' => $msg->receiver_type,
                    'message' => $msg->message,
                    'status' => $msg->status,
                    'created_at' => $msg->created_at->toIso8601String(),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'room_id' => $chat->room_id,
                    'messages' => $formattedMessages,
                    'pagination' => [
                        'current_page' => $messages->currentPage(),
                        'last_page' => $messages->lastPage(),
                        'per_page' => $messages->perPage(),
                        'total' => $messages->total(),
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve messages: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark all unread messages received from the receiver as read.
     */
    public function markAsRead(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'receiver_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        $userType = $user->isDriver() ? 'driver' : 'user';

        $receiver = User::find($request->receiver_id);
        $receiverType = $receiver->isDriver() ? 'driver' : 'user';

        if ($userType === $receiverType) {
            return response()->json([
                'success' => false,
                'message' => 'Chats are only allowed between a user and a driver.'
            ], 400);
        }

        $userId = $userType === 'user' ? $user->id : $receiver->id;
        $driverId = $userType === 'driver' ? $user->id : $receiver->id;
        $roomId = "{$userId}_{$driverId}";

        try {
            $chat = Chat::where('room_id', $roomId)->first();

            if (!$chat) {
                return response()->json([
                    'success' => true,
                    'message' => 'No active chat room exists between the users.'
                ]);
            }

            $unreadUpdated = ChatMessage::where('chat_id', $chat->id)
                ->where('receiver_id', $user->id)
                ->where('status', '!=', 'read')
                ->update(['status' => 'read']);

            if ($unreadUpdated > 0) {
                broadcast(new UserDriverChatMessageSeen($chat->id, $chat->room_id, $user->id, $userType))->toOthers();
            }

            return response()->json([
                'success' => true,
                'message' => 'Messages marked as read successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark messages as read: ' . $e->getMessage()
            ], 500);
        }
    }
}
