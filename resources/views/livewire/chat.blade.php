<div>
    <h1>Chat with {{ $selectedUser->name }}</h1>
    <div class="">
        <div class="">
            @foreach($messages as $message)
                <div class="mb-2">
                    <strong>@if ($message->sender_id == auth()->id()) You @else {{ $message->sender->name }} @endif</strong>
                    <p>@if ($message->message=='a') <a href="{{ $message->id }}" target="_blank"><span class="fa-solid fa-image"></span></a> @else {{ $message->message }} @endif</p>
                </div>
            @endforeach
        </div>
        <form wire:submit="submit" class="">
            <input type="text" wire:model="newMessage" class="flex-1 border rounded p-2" placeholder="Type your message..."
            wire:wire:model="newMessage"
            />
            <button type="submit" class="ml-2 bg-blue-500 text-white rounded p-2">Send</button>
        </form>
    </div>
</div>