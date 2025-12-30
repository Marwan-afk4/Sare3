@extends('layouts.app')
@php
    $currentPage = 'wallet-requests';
@endphp
@section('title', $walletRequest->name)
@section('content')
    <div class="container-fluid">
        <h1>{{ $walletRequest->id }}</h1>
        <div class="mb-3">
            <a href="{{ route('wallet-requests.index') }}" class="btn btn-secondary btn-sm me-1"> <i
                    class="fa fa-arrow-right"></i> {{ __('Back to') }} {{ __('Wallet Requests') }}</a>
            <a href='{{ route('wallet-requests.edit', $walletRequest) }}' class="btn btn-warning btn-sm me-1">{{ __('Edit') }} <i class="fa fa-edit"></i></a>
        </div>
        <div class="card">
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <strong>{{ __('Id') }}:</strong> {{ $walletRequest->id }}
                    </li>
                    <li class="list-group-item">
                        <strong>{{ __('Driver') }}:</strong>
                        @if ($walletRequest->driver)
                            <a
                                href="{{ route('drivers.show', $walletRequest->driver) }}">{{ $walletRequest->driver?->name }}</a>
                        @endif
                    </li>
                    <li class="list-group-item">
                        <strong>{{ __('Driver Wallet') }}:</strong> {{ $walletRequest->driver->wallet ?? '-' }}
                    </li>
                    <li class="list-group-item">
                        <strong>{{ __('Amount') }}:</strong> {{ $walletRequest->amount }}
                    </li>
                    <li class="list-group-item">
                        <strong>{{ __('Type') }}:</strong>
                        <td>{!! $walletRequest->type->badge() !!}</td>

                    </li>
                    <li class="list-group-item">
                        <strong>{{ __('Status') }}:</strong>
                        <td>{!! $walletRequest->status->badge() !!}</td>
                    </li>
                    <li class="list-group-item">
                        <strong>{{ __('Note') }}:</strong> {{ $walletRequest->note ?? '-' }}
                    </li>
                    <li class="list-group-item">
                        <strong>{{ __('Created At') }}:</strong> 
                        @php
                            $timezone = $walletRequest->driver->getTimezone();
                            $createdAt = $walletRequest->created_at->timezone($timezone);
                        @endphp
                        {{ $createdAt->format('M d, Y h:i A') }} ({{ $createdAt->diffForHumans() }})
                    </li>
                    <li class="list-group-item">
                        <strong>{{ __('Updated At') }}:</strong> 
                        @php
                            $updatedAt = $walletRequest->updated_at->timezone($timezone);
                        @endphp
                        {{ $updatedAt->format('M d, Y h:i A') }} ({{ $updatedAt->diffForHumans() }})
                    </li>
                </ul>
            </div>
        </div>

        <!-- Messages Section -->
        <div class="chat-container mt-4">
            <div class="chat-header">
                <div class="d-flex align-items-center">
                    <div class="chat-avatar me-3">
                        <i class="fas fa-comments"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h5 class="mb-0 chat-title">{{ __('Conversation') }}</h5>
                        <small class="chat-subtitle">{{ __('Messages with') }} {{ $walletRequest->driver->name ?? __('Driver') }}</small>
                    </div>
                    <div class="chat-actions">
                        <button class="btn btn-chat-primary me-2" onclick="scrollToBottom(true)" title="Scroll to bottom">
                            <i class="fas fa-arrow-down me-1"></i>{{ __('Scroll Down') }}
                        </button>
                        <button class="btn btn-chat-primary" data-bs-toggle="modal" data-bs-target="#addMessageModal">
                            <i class="fas fa-plus me-1"></i>{{ __('New Message') }}
                        </button>
                    </div>
                </div>
            </div>

            <div class="chat-body">
                @if($walletRequest->messages->count() > 0)
                    <div class="messages-wrapper" id="messagesWrapper">
                        @foreach($walletRequest->messages as $message)
                            @if($message->admin_message)
                                <!-- Admin Message -->
                                <div class="message-group admin-group">
                                    <div class="message-bubble admin-bubble">
                                        <div class="message-header">
                                            <div class="sender-info">
                                                <div class="sender-avatar admin-avatar">
                                                    <i class="fas fa-user-shield"></i>
                                                </div>
                                                <div class="sender-details">
                                                    <span class="sender-name">{{ __('Admin') }}</span>
                                                    @if($message->admin)
                                                        <span class="sender-real-name">{{ $message->admin->name }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="message-time">
                                                @php
                                                    $msgTimezone = $walletRequest->driver->getTimezone();
                                                    $msgTime = $message->created_at->timezone($msgTimezone);
                                                @endphp
                                                {{ $msgTime->format('H:i') }}
                                            </div>
                                        </div>
                                        <div class="message-content">
                                            {{ $message->admin_message }}
                                        </div>
                                        <div class="message-status">
                                            <i class="fas fa-check-double"></i>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if($message->driver_message)
                                <!-- Driver Message -->
                                <div class="message-group driver-group">
                                    <div class="message-bubble driver-bubble">
                                        <div class="message-header">
                                            <div class="message-time">
                                                @php
                                                    $msgTimezone = $walletRequest->driver->getTimezone();
                                                    $msgTime = $message->created_at->timezone($msgTimezone);
                                                @endphp
                                                {{ $msgTime->format('H:i') }}
                                            </div>
                                            <div class="sender-info">
                                                <div class="sender-details">
                                                    <span class="sender-name">{{ __('Driver') }}</span>
                                                    @if($message->driver)
                                                        <span class="sender-real-name">{{ $message->driver->name }}</span>
                                                    @endif
                                                </div>
                                                <div class="sender-avatar driver-avatar">
                                                    <i class="fas fa-user"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="message-content">
                                            {{ $message->driver_message }}
                                        </div>
                                        <div class="message-status">
                                            <i class="fas fa-check-double"></i>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                    <!-- Scroll to bottom button -->
                    <div class="scroll-to-bottom" id="scrollToBottomBtn" style="display: none;">
                        <button type="button" class="btn btn-primary btn-sm rounded-circle" onclick="scrollToBottom(true)">
                            <i class="fas fa-chevron-down"></i>
                        </button>
                    </div>
                @else
                    <div class="empty-chat">
                        <div class="empty-chat-icon">
                            <i class="fas fa-comments"></i>
                        </div>
                        <h6 class="empty-chat-title">{{ __('No messages yet') }}</h6>
                        <p class="empty-chat-text">{{ __('Start a conversation with the driver by sending your first message.') }}</p>
                        <button class="btn btn-chat-primary" data-bs-toggle="modal" data-bs-target="#addMessageModal">
                            <i class="fas fa-paper-plane me-1"></i>{{ __('Send First Message') }}
                        </button>
                    </div>
                @endif
            </div>
        </div>

        <!-- Add Message Modal -->
        <div class="modal fade" id="addMessageModal" tabindex="-1" aria-labelledby="addMessageModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content chat-modal">
                    <div class="modal-header chat-modal-header">
                        <div class="d-flex align-items-center">
                            <div class="modal-avatar me-3">
                                <i class="fas fa-paper-plane"></i>
                            </div>
                            <div>
                                <h5 class="modal-title mb-0" id="addMessageModalLabel">{{ __('Send Message') }}</h5>
                                <small class="modal-subtitle">{{ __('To') }}: {{ $walletRequest->driver->name ?? __('Driver') }}</small>
                            </div>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="POST" action="{{ route('wallet-requests.add-message', $walletRequest->id) }}" id="messageForm">
                        @csrf
                        <div class="modal-body chat-modal-body">
                            <div class="message-compose">
                                <div class="compose-header">
                                    <div class="recipient-info">
                                        <div class="recipient-avatar">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        <div class="recipient-details">
                                            <span class="recipient-name">{{ $walletRequest->driver->name ?? __('Driver') }}</span>
                                            <span class="recipient-role">{{ __('Driver') }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="compose-body">
                                    <textarea class="form-control message-input" id="admin_message" name="admin_message"
                                        placeholder="{{ __('Type your message here...') }}" required></textarea>
                                    <div class="message-tools">
                                        <div class="char-counter">
                                            <span id="charCount">0</span>/1000
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer chat-modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                <i class="fas fa-times me-1"></i>{{ __('Cancel') }}
                            </button>
                            <button type="submit" class="btn btn-chat-primary" id="sendButton">
                                <i class="fas fa-paper-plane me-1"></i>{{ __('Send Message') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="mt-3">
            {{-- <form method='POST' action='{{ route('wallet-requests.destroy', $walletRequest) }}' onsubmit='return confirm("Are you sure you want to delete this item?")'>
			<input type='hidden' name='_method' value='DELETE'>
			<button type='submit' class="btn btn-square btn-danger">{{ __('Delete') }}</button>
		</form> --}}
        </div>
    </div>
@endsection

@push('styles')
<style>
/* Chat Container */
.chat-container {
    background: var(--bs-body-bg);
    border-radius: 16px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    border: 1px solid var(--bs-border-color);
}

/* Chat Header */
.chat-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 20px 24px;
    border-bottom: none;
}

.chat-avatar {
    width: 48px;
    height: 48px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
}

.chat-title {
    color: white;
    font-weight: 600;
    font-size: 1.1rem;
}

.chat-subtitle {
    color: rgba(255, 255, 255, 0.8);
    font-size: 0.875rem;
}

.btn-chat-primary {
    background: rgba(255, 255, 255, 0.2);
    border: 1px solid rgba(255, 255, 255, 0.3);
    color: white;
    border-radius: 8px;
    padding: 8px 16px;
    font-size: 0.875rem;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn-chat-primary:hover {
    background: rgba(255, 255, 255, 0.3);
    border-color: rgba(255, 255, 255, 0.4);
    color: white;
    transform: translateY(-1px);
}

/* Chat Body */
.chat-body {
    background: var(--bs-body-bg);
    height: 500px;
    position: relative;
    overflow: hidden;
}

.messages-wrapper {
    padding: 20px;
    height: 100%;
    overflow-y: auto;
    scroll-behavior: smooth;
    display: flex;
    flex-direction: column;
}

/* Scroll to bottom button */
.scroll-to-bottom {
    position: absolute;
    bottom: 20px;
    right: 20px;
    z-index: 10;
    animation: fadeInUp 0.3s ease-out;
}

.scroll-to-bottom button {
    width: 40px;
    height: 40px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    border: none;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    transition: all 0.3s ease;
}

.scroll-to-bottom button:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
}

/* Message Groups */
.message-group {
    margin-bottom: 16px;
    animation: messageSlideIn 0.4s ease-out;
}

.admin-group {
    display: flex;
    justify-content: flex-start;
}

.driver-group {
    display: flex;
    justify-content: flex-end;
}

/* Message Bubbles */
.message-bubble {
    max-width: 70%;
    position: relative;
    border-radius: 18px;
    padding: 12px 16px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
}

.message-bubble:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.admin-bubble {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-bottom-left-radius: 6px;
}

.driver-bubble {
    background: var(--bs-gray-100);
    color: var(--bs-body-color);
    border-bottom-right-radius: 6px;
    border: 1px solid var(--bs-border-color);
}

/* Dark mode support for driver bubble */
[data-bs-theme="dark"] .driver-bubble {
    background: var(--bs-gray-800);
    border-color: var(--bs-gray-700);
}

/* Message Header */
.message-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 8px;
    font-size: 0.75rem;
}

.sender-info {
    display: flex;
    align-items: center;
    gap: 8px;
}

.sender-avatar {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
}

.admin-avatar {
    background: rgba(255, 255, 255, 0.2);
    color: white;
}

.driver-avatar {
    background: var(--bs-primary);
    color: white;
}

.sender-details {
    display: flex;
    flex-direction: column;
    line-height: 1.2;
}

.sender-name {
    font-weight: 600;
    font-size: 0.75rem;
}

.sender-real-name {
    font-size: 0.7rem;
    opacity: 0.8;
}

.message-time {
    font-size: 0.7rem;
    opacity: 0.8;
    font-weight: 500;
}

/* Message Content */
.message-content {
    font-size: 0.9rem;
    line-height: 1.4;
    word-wrap: break-word;
    margin-bottom: 4px;
}

.message-status {
    text-align: right;
    font-size: 0.7rem;
    opacity: 0.6;
}

/* Empty Chat State */
.empty-chat {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 400px;
    text-align: center;
    padding: 40px;
}

.empty-chat-icon {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 32px;
    color: white;
    margin-bottom: 20px;
}

.empty-chat-title {
    color: var(--bs-body-color);
    margin-bottom: 8px;
    font-weight: 600;
}

.empty-chat-text {
    color: var(--bs-secondary-color);
    margin-bottom: 24px;
    max-width: 300px;
}

/* Modal Styles */
.chat-modal {
    border-radius: 16px;
    border: none;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
    overflow: hidden;
}

.chat-modal-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    padding: 20px 24px;
}

.modal-avatar {
    width: 40px;
    height: 40px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
}

.modal-subtitle {
    color: rgba(255, 255, 255, 0.8);
    font-size: 0.8rem;
}

.chat-modal-body {
    padding: 24px;
    background: var(--bs-body-bg);
}

.message-compose {
    border-radius: 12px;
    border: 1px solid var(--bs-border-color);
    overflow: hidden;
}

.compose-header {
    background: var(--bs-gray-50);
    padding: 12px 16px;
    border-bottom: 1px solid var(--bs-border-color);
}

[data-bs-theme="dark"] .compose-header {
    background: var(--bs-gray-800);
}

.recipient-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.recipient-avatar {
    width: 32px;
    height: 32px;
    background: var(--bs-primary);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 12px;
}

.recipient-details {
    display: flex;
    flex-direction: column;
    line-height: 1.2;
}

.recipient-name {
    font-weight: 600;
    font-size: 0.875rem;
    color: var(--bs-body-color);
}

.recipient-role {
    font-size: 0.75rem;
    color: var(--bs-secondary-color);
}

.compose-body {
    position: relative;
}

.message-input {
    border: none;
    border-radius: 0;
    padding: 16px;
    min-height: 120px;
    resize: none;
    font-size: 0.9rem;
    line-height: 1.5;
}

.message-input:focus {
    box-shadow: none;
    border-color: transparent;
}

.message-tools {
    position: absolute;
    bottom: 8px;
    right: 12px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.char-counter {
    font-size: 0.75rem;
    color: var(--bs-secondary-color);
    background: var(--bs-body-bg);
    padding: 4px 8px;
    border-radius: 12px;
    border: 1px solid var(--bs-border-color);
}

.chat-modal-footer {
    background: var(--bs-gray-50);
    border: none;
    padding: 16px 24px;
}

[data-bs-theme="dark"] .chat-modal-footer {
    background: var(--bs-gray-800);
}

/* Scrollbar Styling */
.messages-wrapper::-webkit-scrollbar {
    width: 6px;
}

.messages-wrapper::-webkit-scrollbar-track {
    background: transparent;
}

.messages-wrapper::-webkit-scrollbar-thumb {
    background: var(--bs-border-color);
    border-radius: 3px;
}

.messages-wrapper::-webkit-scrollbar-thumb:hover {
    background: var(--bs-secondary-color);
}

/* Animations */
@keyframes messageSlideIn {
    from {
        opacity: 0;
        transform: translateY(20px) scale(0.95);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

/* Responsive Design */
@media (max-width: 768px) {
    .message-bubble {
        max-width: 85%;
    }

    .chat-header {
        padding: 16px 20px;
    }

    .messages-wrapper {
        padding: 16px;
    }

    .empty-chat {
        padding: 20px;
        height: 300px;
    }

    .empty-chat-icon {
        width: 60px;
        height: 60px;
        font-size: 24px;
    }
}

/* Loading State */
.message-sending {
    opacity: 0.7;
    pointer-events: none;
}

.message-sending .message-status::after {
    content: "...";
    animation: dots 1.5s infinite;
}

@keyframes dots {
    0%, 20% { content: ""; }
    40% { content: "."; }
    60% { content: ".."; }
    80%, 100% { content: "..."; }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-scroll to bottom of messages - Multiple approaches for reliability
    function scrollToBottom(smooth = false) {
        const messagesWrapper = document.getElementById('messagesWrapper');
        if (messagesWrapper) {
            console.log('Scrolling to bottom. ScrollHeight:', messagesWrapper.scrollHeight, 'ClientHeight:', messagesWrapper.clientHeight);

            // Method 1: Direct scroll
            messagesWrapper.scrollTop = messagesWrapper.scrollHeight;

            // Method 2: Smooth scroll (if supported)
            if (smooth && messagesWrapper.scrollTo) {
                messagesWrapper.scrollTo({
                    top: messagesWrapper.scrollHeight,
                    behavior: 'smooth'
                });
            }

            // Method 3: Find last message and scroll into view
            const lastMessage = messagesWrapper.querySelector('.message-group:last-child');
            if (lastMessage && lastMessage.scrollIntoView) {
                lastMessage.scrollIntoView({
                    behavior: smooth ? 'smooth' : 'auto',
                    block: 'end'
                });
            }

            console.log('After scroll - ScrollTop:', messagesWrapper.scrollTop);
        } else {
            console.log('Messages wrapper not found!');
        }
    }

    // Initial scroll after a short delay to ensure DOM is fully rendered
    setTimeout(() => {
        scrollToBottom(false);
    }, 100);

    // Also try after images/content load
    window.addEventListener('load', () => {
        setTimeout(() => {
            scrollToBottom(false);
        }, 200);
    });

    // Modal and form elements
    const addMessageModal = document.getElementById('addMessageModal');
    const messageForm = document.getElementById('messageForm');
    const textarea = document.getElementById('admin_message');
    const charCount = document.getElementById('charCount');
    const sendButton = document.getElementById('sendButton');

    // Character counter
    if (textarea && charCount) {
        textarea.addEventListener('input', function() {
            const count = this.value.length;
            charCount.textContent = count;

            // Update counter color based on limit
            if (count > 900) {
                charCount.style.color = '#dc3545';
            } else if (count > 800) {
                charCount.style.color = '#fd7e14';
            } else {
                charCount.style.color = 'var(--bs-secondary-color)';
            }

            // Auto-resize textarea
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 200) + 'px';

            // Enable/disable send button
            if (sendButton) {
                sendButton.disabled = count === 0 || count > 1000;
            }
        });
    }

    // Clear form and reset modal state
    if (addMessageModal) {
        addMessageModal.addEventListener('hidden.bs.modal', function () {
            if (textarea) {
                textarea.value = '';
                textarea.style.height = 'auto';
            }
            if (charCount) {
                charCount.textContent = '0';
                charCount.style.color = 'var(--bs-secondary-color)';
            }
            if (sendButton) {
                sendButton.disabled = true;
            }
        });

        // Focus textarea when modal opens
        addMessageModal.addEventListener('shown.bs.modal', function () {
            if (textarea) {
                textarea.focus();
            }
        });
    }

    // Form submission with loading state
    if (messageForm) {
        messageForm.addEventListener('submit', function(e) {
            if (sendButton) {
                sendButton.disabled = true;
                sendButton.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>{{ __("Sending...") }}';
            }
        });
    }

    // Keyboard shortcuts
    if (textarea) {
        textarea.addEventListener('keydown', function(e) {
            // Ctrl/Cmd + Enter to send
            if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
                e.preventDefault();
                if (!sendButton.disabled) {
                    messageForm.submit();
                }
            }
        });
    }

    // Force scroll after modal closes (when new message is added)
    if (addMessageModal) {
        addMessageModal.addEventListener('hidden.bs.modal', function () {
            // Scroll after modal closes and page potentially refreshes
            setTimeout(() => {
                scrollToBottom(true);
            }, 500);
        });
    }

    // Mutation Observer to detect new messages and auto-scroll
    const messagesWrapper = document.getElementById('messagesWrapper');
    if (messagesWrapper && window.MutationObserver) {
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                    // New message added, scroll to bottom
                    setTimeout(() => {
                        scrollToBottom(true);
                    }, 100);
                }
            });
        });

        observer.observe(messagesWrapper, {
            childList: true,
            subtree: true
        });
    }

    // Scroll detection for showing/hiding scroll-to-bottom button
    const scrollToBottomBtn = document.getElementById('scrollToBottomBtn');
    if (messagesWrapper && scrollToBottomBtn) {
        messagesWrapper.addEventListener('scroll', function() {
            const isAtBottom = this.scrollTop >= (this.scrollHeight - this.clientHeight - 50);

            if (isAtBottom) {
                scrollToBottomBtn.style.display = 'none';
            } else {
                scrollToBottomBtn.style.display = 'block';
            }
        });
    }

    // Make scrollToBottom function global for button onclick
    window.scrollToBottom = scrollToBottom;

    // Add typing indicator (visual enhancement)
    let typingTimer;
    if (textarea) {
        textarea.addEventListener('input', function() {
            clearTimeout(typingTimer);
            // Could add typing indicator here for real-time features
        });
    }

    // Message hover effects
    document.querySelectorAll('.message-bubble').forEach(bubble => {
        bubble.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
        });

        bubble.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });

    // Auto-refresh messages (optional - for real-time updates)
    // Uncomment if you want to periodically check for new messages
    /*
    setInterval(function() {
        // Could implement AJAX polling here for real-time messages
        // fetch('/wallet-requests/{{ $walletRequest->id }}/messages')
        //     .then(response => response.json())
        //     .then(data => {
        //         // Update messages if new ones exist
        //     });
    }, 30000); // Check every 30 seconds
    */
});

// Utility function for showing toast notifications
function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type} border-0`;
    toast.setAttribute('role', 'alert');
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">${message}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;

    // Add to page (you might need to create a toast container)
    document.body.appendChild(toast);
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();

    // Remove after hiding
    toast.addEventListener('hidden.bs.toast', function() {
        document.body.removeChild(toast);
    });
}
</script>
@endpush
