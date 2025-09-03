@extends('layouts.app')
@php
    $currentPage = 'support-chat';
@endphp
@section('title', __('Support Chat'))
@section('content')
    <div class="container-fluid">
        <!-- Breadcrumb Navigation -->
        <x-breadcrumb :items="[
            [
                'title' => __('Dashboard'),
                'url' => route('home'),
                'icon' => 'fas fa-home'
            ],
            [
                'title' => __('Support Chat'),
                'icon' => 'fas fa-message-circle'
            ]
        ]" />

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="mb-0">{{ __('Support Chat') }}</h1>
            <div class="d-flex align-items-center">
                <span class="badge bg-primary me-2" id="total-unread">{{ $stats['total_unread_messages'] ?? 0 }}</span>
                <small class="text-muted">{{ __('Unread Messages') }}</small>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="mb-0">{{ ($stats['user_conversations'] ?? 0) + ($stats['driver_conversations'] ?? 0) }}</h4>
                                <p class="mb-0">{{ __('Total Conversations') }}</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-comments fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="mb-0">{{ $stats['total_unread_messages'] ?? 0 }}</h4>
                                <p class="mb-0">{{ __('Unread Messages') }}</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-envelope fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="mb-0">{{ $stats['user_conversations'] }}</h4>
                                <p class="mb-0">{{ __('User Chats') }}</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-users fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="mb-0">{{ $stats['driver_conversations'] }}</h4>
                                <p class="mb-0">{{ __('Driver Chats') }}</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-car fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chat Interface -->
        <div class="row">
            <!-- Chat List Panel -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <ul class="nav nav-tabs card-header-tabs" id="chatTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="users-tab" data-bs-toggle="tab" 
                                        data-bs-target="#users-panel" type="button" role="tab" 
                                        aria-controls="users-panel" aria-selected="true">
                                    {{ __('Users') }}
                                    <span class="badge bg-secondary ms-1" id="user-count">0</span>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="drivers-tab" data-bs-toggle="tab" 
                                        data-bs-target="#drivers-panel" type="button" role="tab" 
                                        aria-controls="drivers-panel" aria-selected="false">
                                    {{ __('Drivers') }}
                                    <span class="badge bg-secondary ms-1" id="driver-count">0</span>
                                </button>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body p-0">
                        <div class="tab-content" id="chatTabsContent">
                            <!-- Users Tab -->
                            <div class="tab-pane fade show active" id="users-panel" role="tabpanel" 
                                 aria-labelledby="users-tab">
                                <div class="p-3">
                                    <div class="input-group mb-3">
                                        <input type="text" class="form-control" id="user-search" 
                                               placeholder="{{ __('Search users...') }}">
                                        <button class="btn btn-outline-secondary" type="button">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                </div>
                                <div id="user-conversations" class="conversation-list">
                                    <!-- Content will be loaded by JavaScript -->
                                </div>
                            </div>
                            
                            <!-- Drivers Tab -->
                            <div class="tab-pane fade" id="drivers-panel" role="tabpanel" 
                                 aria-labelledby="drivers-tab">
                                <div class="p-3">
                                    <div class="input-group mb-3">
                                        <input type="text" class="form-control" id="driver-search" 
                                               placeholder="{{ __('Search drivers...') }}">
                                        <button class="btn btn-outline-secondary" type="button">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                </div>
                                <div id="driver-conversations" class="conversation-list">
                                    <!-- Content will be loaded by JavaScript -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Chat Messages Panel -->
            <div class="col-md-8">
                <div class="card h-100">
                    <div class="card-header d-none" id="chat-header">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" 
                                     style="width: 40px; height: 40px;">
                                    <i class="fas fa-user"></i>
                                </div>
                            </div>
                            <div>
                                <h6 class="mb-0" id="chat-participant-name">{{ __('Select a conversation') }}</h6>
                                <small class="text-muted" id="chat-participant-type"></small>
                            </div>
                            <div class="ms-auto">
                                <button class="btn btn-sm btn-outline-secondary" id="mark-read-btn" type="button">
                                    <i class="fas fa-check-double"></i> {{ __('Mark as Read') }}
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body d-flex flex-column" style="height: 500px;">
                        <!-- Welcome Message -->
                        <div id="welcome-message" class="d-flex align-items-center justify-content-center h-100">
                            <div class="text-center">
                                <i class="fas fa-comments fa-4x text-muted mb-3"></i>
                                <h5 class="text-muted">{{ __('Welcome to Support Chat') }}</h5>
                                <p class="text-muted">{{ __('Select a conversation from the left panel to start chatting') }}</p>
                            </div>
                        </div>

                        <!-- Chat Messages -->
                        <div id="chat-messages" class="flex-grow-1 overflow-auto d-none" style="max-height: 400px;">
                            <!-- Messages will be loaded here -->
                        </div>

                        <!-- Message Input -->
                        <div id="message-input-container" class="mt-3 d-none">
                            <form id="message-form">
                                <div class="input-group">
                                    <input type="text" class="form-control" id="message-input" 
                                           placeholder="{{ __('Type your message...') }}" maxlength="1000">
                                    <button class="btn btn-primary" type="submit" id="send-btn">
                                        <i class="fas fa-paper-plane"></i> {{ __('Send') }}
                                    </button>
                                </div>
                                <small class="text-muted">{{ __('Press Enter to send') }}</small>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Custom CSS -->
    <style>
        .conversation-list {
            max-height: 400px;
            overflow-y: auto;
        }
        
        .conversation-item {
            padding: 12px 16px;
            border-bottom: 1px solid #eee;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        
        .conversation-item:hover {
            background-color: #f8f9fa;
        }
        
        .conversation-item.active {
            background-color: #e3f2fd;
            border-left: 4px solid #2196f3;
        }
        
        .conversation-item.unread {
            background-color: #fff3e0;
        }
        
        .message-bubble {
            max-width: 70%;
            margin-bottom: 10px;
            padding: 8px 12px;
            border-radius: 18px;
            word-wrap: break-word;
        }
        
        .message-bubble.admin {
            background-color: #2196f3;
            color: white;
            margin-left: auto;
            text-align: right;
        }
        
        .message-bubble.user {
            background-color: #f1f3f4;
            color: #333;
            margin-right: auto;
        }
        
        .message-time {
            font-size: 0.75rem;
            opacity: 0.7;
            margin-top: 4px;
        }
        
        .unread-badge {
            background-color: #ff4444;
            color: white;
            border-radius: 10px;
            padding: 2px 6px;
            font-size: 0.75rem;
            min-width: 18px;
            text-align: center;
        }
    </style>

    <!-- JavaScript -->
    <script>

        class SupportChatInterface {
            constructor() {
                this.currentConversation = null;
                this.currentType = 'user';
                this.refreshInterval = null;
            }

            init() {
                this.bindEvents();
                this.loadUserConversations();
                this.startAutoRefresh();
            }

            bindEvents() {
                // Tab switching
                document.getElementById('users-tab').addEventListener('click', () => {
                    this.currentType = 'user';
                    this.loadUserConversations();
                });

                document.getElementById('drivers-tab').addEventListener('click', () => {
                    this.currentType = 'driver';
                    this.loadDriverConversations();
                });

                // Message form
                document.getElementById('message-form').addEventListener('submit', (e) => {
                    e.preventDefault();
                    this.sendMessage();
                });

                // Enter key to send
                document.getElementById('message-input').addEventListener('keypress', (e) => {
                    if (e.key === 'Enter' && !e.shiftKey) {
                        e.preventDefault();
                        this.sendMessage();
                    }
                });

                // Mark as read
                document.getElementById('mark-read-btn').addEventListener('click', () => {
                    this.markAsRead();
                });
            }

            async loadUserConversations() {
                try {
                    // Show loading state
                    this.showLoadingState('user-conversations');
                    
                    const response = await fetch('{{ route("support-chat.active-requests") }}');
                    const data = await response.json();
                    
                    if (data.success) {
                        const userConversations = data.data.filter(conv => conv.requester_type === 'user');
                        this.renderConversations(userConversations, 'user-conversations');
                        document.getElementById('user-count').textContent = userConversations.length;
                    } else {
                        this.showErrorState('user-conversations');
                    }
                } catch (error) {
                    console.error('Error loading user conversations:', error);
                    this.showErrorState('user-conversations');
                }
            }

            async loadDriverConversations() {
                try {
                    // Show loading state
                    this.showLoadingState('driver-conversations');
                    
                    const response = await fetch('{{ route("support-chat.active-requests") }}');
                    const data = await response.json();
                    
                    if (data.success) {
                        const driverConversations = data.data.filter(conv => conv.requester_type === 'driver');
                        this.renderConversations(driverConversations, 'driver-conversations');
                        document.getElementById('driver-count').textContent = driverConversations.length;
                    } else {
                        this.showErrorState('driver-conversations');
                    }
                } catch (error) {
                    console.error('Error loading driver conversations:', error);
                    this.showErrorState('driver-conversations');
                }
            }

            showLoadingState(containerId) {
                const container = document.getElementById(containerId);
                container.innerHTML = `
                    <div class="text-center p-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">{{ __('Loading...') }}</span>
                        </div>
                        <p class="mt-2 text-muted">{{ __('Loading chats...') }}</p>
                    </div>
                `;
            }

            showErrorState(containerId) {
                const container = document.getElementById(containerId);
                container.innerHTML = `
                    <div class="text-center p-4">
                        <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                        <p class="text-muted">{{ __('Failed to load conversations') }}</p>
                        <button class="btn btn-sm btn-outline-primary" onclick="location.reload()">
                            {{ __('Retry') }}
                        </button>
                    </div>
                `;
            }

            renderConversations(conversations, containerId) {
                const container = document.getElementById(containerId);
                
                if (conversations.length === 0) {
                    container.innerHTML = `
                        <div class="text-center p-4">
                            <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-2">{{ __('No chats now') }}</p>
                            <small class="text-muted">{{ __('Conversations will appear here when users start chatting') }}</small>
                        </div>
                    `;
                    return;
                }

                container.innerHTML = conversations.map(conv => `
                    <div class="conversation-item ${conv.unread_count > 0 ? 'unread' : ''}" 
                         data-conversation-id="${conv.id}" 
                         data-requester-id="${conv.requester_id}"
                         data-requester-type="${conv.requester_type}">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <h6 class="mb-1">${conv.requester_name}</h6>
                                <p class="mb-1 text-muted small">${conv.last_message || '{{ __("No messages yet") }}'}</p>
                                <small class="text-muted">${this.formatTime(conv.last_message_at)}</small>
                            </div>
                            <div class="d-flex flex-column align-items-end">
                                ${conv.unread_count > 0 ? `<span class="unread-badge">${conv.unread_count}</span>` : ''}
                                <span class="badge bg-${conv.status === 'pending' ? 'warning' : 'info'} mt-1">${conv.status}</span>
                            </div>
                        </div>
                    </div>
                `).join('');
                
                // Add click event listeners to conversation items
                container.querySelectorAll('.conversation-item').forEach(item => {
                    item.addEventListener('click', () => {
                        const requesterId = item.getAttribute('data-requester-id');
                        const requesterType = item.getAttribute('data-requester-type');
                        this.selectConversation(requesterId, requesterType);
                    });
                });
            }

            async selectConversation(requesterId, requesterType) {
                console.log('Selecting conversation:', requesterId, requesterType);
                
                // Update active state
                document.querySelectorAll('.conversation-item').forEach(item => {
                    item.classList.remove('active');
                });
                
                const selectedItem = document.querySelector(`[data-requester-id="${requesterId}"][data-requester-type="${requesterType}"]`);
                if (selectedItem) {
                    selectedItem.classList.add('active');
                    console.log('Selected item found and activated');
                } else {
                    console.log('Selected item not found');
                }

                this.currentConversation = {
                    requester_id: requesterId,
                    requester_type: requesterType
                };
                
                console.log('Current conversation set:', this.currentConversation);
                
                // Show chat interface immediately
                console.log('Showing chat interface elements...');
                document.getElementById('welcome-message').classList.add('d-none');
                document.getElementById('chat-header').classList.remove('d-none');
                document.getElementById('chat-messages').classList.remove('d-none');
                document.getElementById('message-input-container').classList.remove('d-none');
                
                console.log('Chat interface should now be visible');

                // Load conversation
                await this.loadConversation(requesterId, requesterType);
            }

            async loadConversation(requesterId, requesterType) {
                try {
                    console.log('Loading conversation for:', requesterId, requesterType);
                    const url = `{{ url('admin/support-chat/chat') }}/${requesterId}/${requesterType}`;
                    console.log('Fetching URL:', url);
                    
                    const response = await fetch(url);
                    const data = await response.json();
                    
                    console.log('Conversation response:', data);
                    console.log('Response status:', response.status);
                    console.log('Response headers:', response.headers);
                    
                    if (data.success) {
                        const { messages, target_info, room_id } = data.data;
                        console.log('Messages received from Firebase:', messages);
                        console.log('Messages count:', messages ? messages.length : 0);
                        
                        // Update header
                        if (target_info) {
                            document.getElementById('chat-participant-name').textContent = target_info.name || `User ${requesterId}`;
                            document.getElementById('chat-participant-type').textContent = `${target_info.type || requesterType} - ${target_info.email || 'No email'}`;
                        } else {
                            document.getElementById('chat-participant-name').textContent = `User ${requesterId}`;
                            document.getElementById('chat-participant-type').textContent = requesterType;
                        }
                        
                        // Render messages
                        this.renderMessages(messages);
                    } else {
                        console.error('Failed to load conversation:', data.message);
                        alert('Failed to load conversation: ' + data.message);
                    }
                } catch (error) {
                    console.error('Error loading conversation:', error);
                    alert('Error loading conversation: ' + error.message);
                }
            }

            renderMessages(messages) {
                console.log('Rendering messages:', messages);
                
                const container = document.getElementById('chat-messages');
                
                // Always show the chat interface when renderMessages is called
                document.getElementById('welcome-message').classList.add('d-none');
                document.getElementById('chat-header').classList.remove('d-none');
                document.getElementById('chat-messages').classList.remove('d-none');
                document.getElementById('message-input-container').classList.remove('d-none');
                
                // Handle empty messages
                if (!messages || messages.length === 0) {
                    container.innerHTML = `
                        <div class="text-center p-4">
                            <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-2">{{ __('No messages in this conversation') }}</p>
                            <small class="text-muted">{{ __('Send the first message to start the conversation') }}</small>
                        </div>
                    `;
                    return;
                }

                // Render each message
                const messagesHtml = messages.map(msg => {
                    const messageText = msg.message || msg.text || '';
                    const isAdmin = msg.is_admin_message === true;
                    const timestamp = msg.timestamp || msg.created_at || '';
                    
                    return `
                        <div class="d-flex ${isAdmin ? 'justify-content-end' : 'justify-content-start'} mb-2">
                            <div class="message-bubble ${isAdmin ? 'admin' : 'user'}">
                                <div>${messageText}</div>
                                <div class="message-time">${this.formatTime(timestamp)}</div>
                            </div>
                        </div>
                    `;
                }).join('');

                container.innerHTML = messagesHtml;
                container.scrollTop = container.scrollHeight;
            }

            async sendMessage() {
                const input = document.getElementById('message-input');
                const message = input.value.trim();
                
                if (!message || !this.currentConversation) return;

                try {
                    const response = await fetch('{{ route("support-chat.reply") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            target_id: this.currentConversation.requester_id,
                            target_type: this.currentConversation.requester_type,
                            message: message
                        })
                    });

                    const data = await response.json();
                    
                    if (data.success) {
                        input.value = '';
                        // Reload conversation to show new message
                        await this.loadConversation(this.currentConversation.requester_id, this.currentConversation.requester_type);
                        // Refresh conversation list
                        if (this.currentType === 'user') {
                            this.loadUserConversations();
                        } else {
                            this.loadDriverConversations();
                        }
                    } else {
                        alert('{{ __("Failed to send message") }}');
                    }
                } catch (error) {
                    console.error('Error sending message:', error);
                    alert('{{ __("Failed to send message") }}');
                }
            }



            async markAsRead() {
                if (!this.currentConversation) return;

                try {
                    const response = await fetch(`{{ url('admin/support-chat/requests') }}/${this.currentConversation}/read`, {
                        method: 'PATCH',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    });

                    const data = await response.json();
                    
                    if (data.status === 'success') {
                        // Refresh conversation list
                        if (this.currentType === 'user') {
                            this.loadUserConversations();
                        } else {
                            this.loadDriverConversations();
                        }
                    }
                } catch (error) {
                    console.error('Error marking as read:', error);
                }
            }

            formatTime(timestamp) {
                if (!timestamp) return '';
                const date = new Date(timestamp);
                return date.toLocaleString();
            }

            startAutoRefresh() {
                // Refresh conversations every 30 seconds, but NOT the current conversation
                this.refreshInterval = setInterval(() => {
                    if (this.currentType === 'user') {
                        this.loadUserConversations();
                    } else {
                        this.loadDriverConversations();
                    }
                    
                    // DON'T refresh current conversation automatically to avoid disrupting the view
                    console.log('Auto-refresh: Updated conversation list only');
                }, 30000);
            }
        }

        // Global variable for access from onclick handlers
        let chatInterface;

        document.addEventListener('DOMContentLoaded', function() {
            chatInterface = new SupportChatInterface();
            chatInterface.init();
        });

        // Update sidebar notification badge
        function updateSidebarBadge() {
            fetch('{{ route("support-chat.statistics") }}')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data.total_unread_messages > 0) {
                        const badge = document.getElementById('support-chat-badge');
                        if (badge) {
                            badge.textContent = data.data.total_unread_messages;
                            badge.classList.remove('d-none');
                        }
                    } else {
                        const badge = document.getElementById('support-chat-badge');
                        if (badge) {
                            badge.classList.add('d-none');
                        }
                    }
                })
                .catch(error => console.error('Error updating sidebar badge:', error));
        }

        // Update badge on page load and periodically
        document.addEventListener('DOMContentLoaded', function() {
            updateSidebarBadge();
            setInterval(updateSidebarBadge, 60000); // Update every minute
        });

        // Prevent any unwanted page refreshes
        window.addEventListener('beforeunload', function(e) {
            console.log('Page is about to unload/refresh');
        });
    </script>
@endsection