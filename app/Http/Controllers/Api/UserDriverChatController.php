<?php

namespace App\Http\Controllers\Api;

use App\Events\UserDriverChatMessageSeen;
use App\Events\UserDriverChatMessageSent;
use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\Ride;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UserDriverChatController extends Controller
{
    /**
     * Send a message in a user-driver chat scoped to a ride.
     */
    public function sendMessage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ride_id' => 'required|exists:rides,id',
            'receiver_id' => 'required|exists:users,id',
            'message' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $sender = Auth::user();
        $senderType = $sender->isDriver() ? 'driver' : 'user';

        $receiver = User::find($request->receiver_id);
        $receiverType = $receiver->isDriver() ? 'driver' : 'user';

        if ($senderType === $receiverType) {
            return response()->json([
                'success' => false,
                'message' => 'Chats are only allowed between a user and a driver.',
            ], 400);
        }

        $ride = Ride::findOrFail($request->ride_id);
        $participantError = $this->validateRideParticipants($ride, $sender, $receiver);

        if ($participantError) {
            return response()->json([
                'success' => false,
                'message' => $participantError,
            ], 403);
        }

        try {
            $chat = Chat::findOrCreateForRide($ride);

            if (!$chat->allowsSending()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Chat is closed for this ride.',
                ], 403);
            }

            $message = ChatMessage::create([
                'chat_id' => $chat->id,
                'sender_id' => $sender->id,
                'sender_type' => $senderType,
                'receiver_id' => $receiver->id,
                'receiver_type' => $receiverType,
                'message' => $request->message,
                'status' => 'sent',
            ]);

            $chat->update([
                'last_message' => $request->message,
                'last_message_at' => now(),
            ]);

            broadcast(new UserDriverChatMessageSent($message))->toOthers();

            return response()->json([
                'success' => true,
                'message' => 'Message sent successfully',
                'data' => $this->formatMessage($message, $chat),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send message: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get chat messages for a specific ride.
     */
    public function getMessages(Request $request, int $id)
    {
        $user = Auth::user();
        $ride = Ride::find($id);

        if (!$ride) {
            return response()->json([
                'success' => false,
                'message' => 'Ride not found.',
            ], 404);
        }

        $participantError = $this->validateRideParticipant($ride, $user);

        if ($participantError) {
            return response()->json([
                'success' => false,
                'message' => $participantError,
            ], 403);
        }

        $userId = $ride->user_id;
        $driverId = $ride->driver_id;
        $userType = $user->isDriver() ? 'driver' : 'user';
        $roomId = Chat::createRoomId('user', $userId, 'driver', $driverId, $ride->id);

        try {
            $chat = Chat::findForRide($ride->id, $userId, $driverId);

            if (!$chat) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'ride_id' => $ride->id,
                        'room_id' => $roomId,
                        'is_active' => false,
                        'messages' => [],
                        'pagination' => null,
                    ],
                ]);
            }

            $unreadUpdated = ChatMessage::where('chat_id', $chat->id)
                ->where('receiver_id', $user->id)
                ->where('status', '!=', 'read')
                ->update(['status' => 'read']);

            if ($unreadUpdated > 0) {
                broadcast(new UserDriverChatMessageSeen($chat->id, $chat->room_id, $user->id, $userType))->toOthers();
            }

            $messages = ChatMessage::where('chat_id', $chat->id)
                ->orderBy('created_at', 'desc')
                ->paginate(30);

            $formattedMessages = collect($messages->items())->map(function ($msg) use ($chat) {
                return $this->formatMessage($msg, $chat);
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'ride_id' => $ride->id,
                    'room_id' => $chat->room_id,
                    'is_active' => $chat->is_active && $chat->allowsSending(),
                    'messages' => $formattedMessages,
                    'pagination' => [
                        'current_page' => $messages->currentPage(),
                        'last_page' => $messages->lastPage(),
                        'per_page' => $messages->perPage(),
                        'total' => $messages->total(),
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve messages: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mark all unread messages received from the receiver as read.
     */
    public function markAsRead(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ride_id' => 'required|exists:rides,id',
            'receiver_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = Auth::user();
        $userType = $user->isDriver() ? 'driver' : 'user';

        $receiver = User::find($request->receiver_id);
        $receiverType = $receiver->isDriver() ? 'driver' : 'user';

        if ($userType === $receiverType) {
            return response()->json([
                'success' => false,
                'message' => 'Chats are only allowed between a user and a driver.',
            ], 400);
        }

        $ride = Ride::findOrFail($request->ride_id);
        $participantError = $this->validateRideParticipants($ride, $user, $receiver);

        if ($participantError) {
            return response()->json([
                'success' => false,
                'message' => $participantError,
            ], 403);
        }

        $userId = $userType === 'user' ? $user->id : $receiver->id;
        $driverId = $userType === 'driver' ? $user->id : $receiver->id;

        try {
            $chat = Chat::findForRide($ride->id, $userId, $driverId);

            if (!$chat) {
                return response()->json([
                    'success' => true,
                    'message' => 'No chat room exists for this ride.',
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
                'message' => 'Messages marked as read successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark messages as read: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function validateRideParticipant(Ride $ride, User $user): ?string
    {
        if (!$ride->user_id || !$ride->driver_id) {
            return 'This ride does not have both a user and a driver assigned yet.';
        }

        if ((int) $user->id !== (int) $ride->user_id && (int) $user->id !== (int) $ride->driver_id) {
            return 'You are not a participant in this ride.';
        }

        return null;
    }

    private function validateRideParticipants(Ride $ride, User $participantA, User $participantB): ?string
    {
        $participantError = $this->validateRideParticipant($ride, $participantA);

        if ($participantError) {
            return $participantError;
        }

        $participantIds = [$participantA->id, $participantB->id];

        if (!in_array($ride->user_id, $participantIds, true) || !in_array($ride->driver_id, $participantIds, true)) {
            return 'You are not a participant in this ride.';
        }

        return null;
    }

    private function formatMessage(ChatMessage $message, Chat $chat): array
    {
        return [
            'id' => $message->id,
            'chat_id' => $message->chat_id,
            'ride_id' => $chat->ride_id,
            'room_id' => $chat->room_id,
            'sender_id' => $message->sender_id,
            'sender_type' => $message->sender_type,
            'receiver_id' => $message->receiver_id,
            'receiver_type' => $message->receiver_type,
            'message' => $message->message,
            'status' => $message->status,
            'created_at' => $message->created_at->toIso8601String(),
        ];
    }
}
