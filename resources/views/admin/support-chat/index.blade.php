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
                <span class="badge bg-primary me-2" id="total-unread">{{ $stats['unread_messages'] }}</span>
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
                                <h4 class="mb-0">{{ $stats['total_conversations'] }}</h4>
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
                                <h4 class="mb-0">{{ $stats['unread_messages'] }}</h4>
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
        document.addEventListener('DOMContentLoaded', function() {
            const chatInterface = new SupportChatInterface();
            chatInterface.init();
        });

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
                    
                    const response = await fetch('{{ route("admin.support-chat.conversations.users") }}');
                    const data = await response.json();
                    
                    if (data.status === 'success') {
                        this.renderConversations(data.data, 'user-conversations');
                        document.getElementById('user-count').textContent = data.data.length;
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
                    
                    const response = await fetch('{{ route("admin.support-chat.conversations.drivers") }}');
                    const data = await response.json();
                    
                    if (data.status === 'success') {
                        this.renderConversations(data.data, 'driver-conversations');
                        document.getElementById('driver-count').textContent = data.data.length;
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
                         data-conversation-id="${conv.id}" onclick="chatInterface.selectConversation('${conv.id}')">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <h6 class="mb-1">${conv.participant_name}</h6>
                                <p class="mb-1 text-muted small">${conv.last_message || '{{ __("No messages yet") }}'}</p>
                                <small class="text-muted">${this.formatTime(conv.last_message_time)}</small>
                            </div>
                            ${conv.unread_count > 0 ? `<span class="unread-badge">${conv.unread_count}</span>` : ''}
                        </div>
                    </div>
                `).join('');
            }

            async selectConversation(conversationId) {
                // Update active state
                document.querySelectorAll('.conversation-item').forEach(item => {
                    item.classList.remove('active');
                });
                document.querySelector(`[data-conversation-id="${conversationId}"]`).classList.add('active');

                this.currentConversation = conversationId;
                
                // Show chat interface
                document.getElementById('welcome-message').classList.add('d-none');
                document.getElementById('chat-header').classList.remove('d-none');
                document.getElementById('chat-messages').classList.remove('d-none');
                document.getElementById('message-input-container').classList.remove('d-none');

                // Load conversation
                await this.loadConversation(conversationId);
            }

            async loadConversation(conversationId) {
                try {
                    const response = await fetch(`{{ url('admin/support-chat/conversation') }}/${conversationId}`);
                    const data = await response.json();
                    
                    if (data.status === 'success') {
                        const { conversation, messages } = data.data;
                        
                        // Update header
                        document.getElementById('chat-participant-name').textContent = conversation.participant_name;
                        document.getElementById('chat-participant-type').textContent = conversation.participant_type;
                        
                        // Render messages
                        this.renderMessages(messages);
                    }
                } catch (error) {
                    console.error('Error loading conversation:', error);
                }
            }

            renderMessages(messages) {
                const container = document.getElementById('chat-messages');
                
                if (messages.length === 0) {
                    container.innerHTML = `
                        <div class="text-center p-4">
                            <p class="text-muted">{{ __('No messages in this conversation') }}</p>
                        </div>
                    `;
                    return;
                }

                container.innerHTML = messages.map(msg => `
                    <div class="d-flex ${msg.is_admin_message ? 'justify-content-end' : 'justify-content-start'} mb-2">
                        <div class="message-bubble ${msg.is_admin_message ? 'admin' : 'user'}">
                            <div>${msg.message}</div>
                            <div class="message-time">${this.formatTime(msg.timestamp)}</div>
                        </div>
                    </div>
                `).join('');

                // Scroll to bottom
                container.scrollTop = container.scrollHeight;
            }

            async sendMessage() {
                const input = document.getElementById('message-input');
                const message = input.value.trim();
                
                if (!message || !this.currentConversation) return;

                try {
                    const response = await fetch('{{ route("admin.support-chat.message.send") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            conversation_id: this.currentConversation,
                            message: message
                        })
                    });

                    const data = await response.json();
                    
                    if (data.status === 'success') {
                        input.value = '';
                        // Reload conversation to show new message
                        await this.loadConversation(this.currentConversation);
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
                    const response = await fetch(`{{ url('admin/support-chat/conversation') }}/${this.currentConversation}/read`, {
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
                // Refresh conversations every 30 seconds
                this.refreshInterval = setInterval(() => {
                    if (this.currentType === 'user') {
                        this.loadUserConversations();
                    } else {
                        this.loadDriverConversations();
                    }
                    
                    // Refresh current conversation if one is selected
                    if (this.currentConversation) {
                        this.loadConversation(this.currentConversation);
                    }
                }, 30000);
            }
        }

        // Global variable for access from onclick handlers
        let chatInterface;

        // Update sidebar notification badge
        function updateSidebarBadge() {
            fetch('{{ route("admin.support-chat.stats") }}')
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success' && data.data.total_unread_messages > 0) {
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
    </script>
@endsection