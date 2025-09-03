<?php

namespace App\Livewire;

use App\Models\User;
use App\Models\ChatMessage;
use App\Events\MessageSent;
use App\Services\FirebaseChatService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Chat extends Component
{
    public $selectedUser;
    public $newMessage;
    public $messages;
    public $authId;
    protected $firebaseService;

    public function mount()
    {
        $this->firebaseService = app(FirebaseChatService::class);
        $this->authId = Auth::id();
        $targetId = $this->authId == 1 ? 2 : 1;

        // $targetId = $this->authId == 1 ? 472 : 1;

        $this->selectedUser = User::find($targetId);

        $this->loadMessages();
    }

    public function submit()
    {
        if (!$this->newMessage) {
            return;
        }

        // Create room ID for Firebase
        $roomId = "admin_{$this->selectedUser->id}";
        
        // Send message to Firebase
        $messageData = [
            'senderId' => $this->authId,
            'receiverId' => $this->selectedUser->id,
            'senderType' => 'admin', // or determine based on user role
            'receiverType' => 'user', // or determine based on receiver
            'text' => $this->newMessage,
            'timestamp' => now()->timestamp * 1000, // Firebase expects milliseconds
            'status' => 'sent'
        ];
        
        $firebaseMessageId = $this->firebaseService->sendMessage($roomId, $messageData);
        
        // Also save to local database for backup/consistency
        $message = ChatMessage::create([
            'sender_id' => $this->authId,
            'receiver_id' => $this->selectedUser->id,
            'message' => $this->newMessage,
            'firebase_message_id' => $firebaseMessageId,
        ]);

        $this->newMessage = '';
        
        // Reload messages from Firebase
        $this->loadMessages();

        broadcast(new MessageSent($message))->toOthers();
    }

    public function getListeners()
    {
        return [
            "echo-private:chat.{$this->authId},MessageSent" => 'newChatMessageNotification',
        ];
    }

    public function newChatMessageNotification($message)
    {
        // Only push if the message is from the currently selected user
        if ($message['sender_id'] == $this->selectedUser->id) {
            $messageObj = ChatMessage::find($message['id']);
            if ($messageObj) {
                $this->messages->push($messageObj);
            }
        }
    }

    public function loadMessages()
    {
        // Load messages from Firebase
        $roomId = "admin_{$this->selectedUser->id}";
        $firebaseMessages = $this->firebaseService->getMessages($roomId, 50);
        
        $this->messages = collect();
        
        if (!empty($firebaseMessages)) {
            foreach ($firebaseMessages as $messageId => $messageData) {
                $this->messages->push((object) [
                    'id' => $messageId,
                    'sender_id' => $messageData['senderId'] ?? null,
                    'receiver_id' => $messageData['receiverId'] ?? null,
                    'message' => $messageData['text'] ?? '',
                    'timestamp' => isset($messageData['timestamp']) 
                        ? date('Y-m-d H:i:s', $messageData['timestamp'] / 1000) 
                        : null,
                    'sender' => (object) [
                        'name' => $this->getSenderName($messageData['senderId'] ?? null)
                    ]
                ]);
            }
            
            // Sort messages by timestamp
            $this->messages = $this->messages->sortBy('timestamp');
        }
    }
    
    private function getSenderName($senderId)
    {
        if ($senderId == $this->authId) {
            return auth()->user()->name;
        } elseif ($senderId == $this->selectedUser->id) {
            return $this->selectedUser->name;
        }
        
        $user = User::find($senderId);
        return $user ? $user->name : 'Unknown User';
    }

    public function render()
    {
        return view('livewire.chat');
    }
}
