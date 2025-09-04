<div class="chat-container">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">
                <i class="fas fa-comments me-2"></i>
                {{ __('Chat with') }} {{ $selectedUser->name }}
            </h4>
        </div>

        <div class="card-body">
            <!-- Messages Container -->
            <div class="messages-container mb-3" style="height: 400px; overflow-y: auto; border: 1px solid #dee2e6; padding: 15px; border-radius: 8px; background-color: #f8f9fa;">
                @if($messages && $messages->count() > 0)
                    @foreach($messages as $message)
                        <div class="message-wrapper mb-3 d-flex {{ $message->sender_id == auth()->id() ? 'justify-content-end' : 'justify-content-start' }}">
                            <div class="message-bubble {{ $message->sender_id == auth()->id() ? 'sent' : 'received' }}" style="max-width: 70%;">
                                <div class="message-header mb-1">
                                    <small class="text-muted">
                                        <strong>
                                            @if ($message->sender_id == auth()->id())
                                                {{ __('You') }}
                                            @else
                                                {{ $message->sender->name ?? 'Unknown User' }}
                                            @endif
                                        </strong>
                                        @if(isset($message->timestamp))
                                            <span class="ms-2">{{ date('H:i', strtotime($message->timestamp)) }}</span>
                                        @endif
                                    </small>
                                </div>
                                <div class="message-content p-2 rounded" style="background-color: {{ $message->sender_id == auth()->id() ? '#007bff' : '#e9ecef' }}; color: {{ $message->sender_id == auth()->id() ? 'white' : '#333' }};">
                                    @if ($message->message == 'a')
                                        <a href="{{ $message->id }}" target="_blank" class="text-decoration-none">
                                            <i class="fas fa-image"></i> {{ __('Image') }}
                                        </a>
                                    @else
                                        {{ $message->message }}
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="text-center text-muted py-5">
                        <i class="fas fa-comments fa-3x mb-3"></i>
                        <p>{{ __('No messages yet. Start the conversation!') }}</p>
                    </div>
                @endif
            </div>

            <!-- Message Input Form -->
            <form wire:submit="submit" class="d-flex gap-2">
                <input
                    type="text"
                    wire:model="newMessage"
                    class="form-control"
                    placeholder="Type your message..."
                    maxlength="500"
                />
                <button
                    type="submit"
                    class="btn btn-primary"
                    {{ empty($newMessage) ? 'disabled' : '' }}
                >
                    <i class="fas fa-paper-plane"></i>
                    {{ __('Send') }}
                </button>
            </form>

            @if($newMessage)
                <small class="text-muted mt-1 d-block">{{ strlen($newMessage) }} {{ __('/500 characters') }}</small>
            @endif
        </div>
    </div>

    <style>
        .messages-container::-webkit-scrollbar {
            width: 6px;
        }
        .messages-container::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 3px;
        }
        .messages-container::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 3px;
        }
        .messages-container::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }

        .message-bubble.sent .message-content {
            background-color: #007bff !important;
            color: white !important;
        }

        .message-bubble.received .message-content {
            background-color: #e9ecef !important;
            color: #333 !important;
        }
    </style>

    <script>
        document.addEventListener('livewire:updated', function () {
            // Scroll to bottom when new messages are added
            const messagesContainer = document.querySelector('.messages-container');
            if (messagesContainer) {
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            }
        });

        // Initial scroll to bottom
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(() => {
                const messagesContainer = document.querySelector('.messages-container');
                if (messagesContainer) {
                    messagesContainer.scrollTop = messagesContainer.scrollHeight;
                }
            }, 100);
        });
    </script>
</div>
