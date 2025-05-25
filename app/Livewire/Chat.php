<?php

namespace App\Livewire;

use App\Models\User;
use App\Models\ChatMessage;
use App\Events\MessageSent;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Chat extends Component
{
    public $selectedUser;
    public $newMessage;
    public $messages;

    public $authId;

    public function mount()
    {
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

        $message = ChatMessage::create([
            'sender_id' => $this->authId,
            'receiver_id' => $this->selectedUser->id,
            'message' => $this->newMessage,
        ]);

        $this->messages->push($message);

        $this->newMessage = '';

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
        $this->messages = ChatMessage::where(function ($query) {
            $query->where('sender_id', $this->authId)
                  ->where('receiver_id', $this->selectedUser->id);
        })->orWhere(function ($query) {
            $query->where('sender_id', $this->selectedUser->id)
                  ->where('receiver_id', $this->authId);
        })->orderBy('created_at')->get(); //to commet
    }

    public function render()
    {
        return view('livewire.chat');
    }
}
