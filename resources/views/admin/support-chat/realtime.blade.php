@extends('layouts.app')
@php
    $currentPage = 'support-chat';
@endphp
@section('title', __('Support Chat - Real-time'))
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
            <h1 class="mb-0">{{ __('Support Chat - Real-time') }}</h1>
            <div class="d-flex align-items-center">
                <div class="me-3">
                    <span class="badge bg-success" id="connection-status">{{ __('Connecting...') }}</span>
                </div>
                <span class="badge bg-primary me-2" id="total-unread">0</span>
                <small class="text-muted">{{ __('Unread Messages') }}</small>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-2">
                <div class="card bg-primary text-white">
                    <div class="card-body text-center">
                        <h4 class="mb-0" id="stat-pending">0</h4>
                        <p class="mb-0 small">{{ __('Pending') }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card bg-warning text-white">
                    <div class="card-body text-center">
                        <h4 class="mb-0" id="stat-in-progress">0</h4>
                        <p class="mb-0 small">{{ __('In Progress') }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card bg-info text-white">
                    <div class="card-body text-center">
                        <h4 class="mb-0" id="stat-users">0</h4>
                        <p class="mb-0 small">{{ __('Users') }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card bg-success text-white">
                    <div class="card-body text-center">
                        <h4 class="mb-0" id="stat-drivers">0</h4>
                        <p class="mb-0 small">{{ __('Drivers') }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card bg-secondary text-white">
                    <div class="card-body text-center">
                        <h4 class="mb-0" id="stat-today">0</h4>
                        <p class="mb-0 small">{{ __('Today') }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card bg-dark text-white">
                    <div class="card-body text-center">
                        <h4 class="mb-0" id="stat-total">0</h4>
                        <p class="mb-0 small">{{ __('Total') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chat Interface -->
        <div class="row">
            <!-- Support Requests Panel -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">{{ __('Active Support Requests') }}</h6>
                            <button class="btn btn-sm btn-outline-primary" onclick="realtimeChat.refreshRequests()">
                                <i class="fas fa-refresh"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <!-- Filter Tabs -->
                        <div class="border-bottom">
                            <ul class="nav nav-tabs nav-tabs-sm" role="tablist">
                                <li class="nav-item">
                                    <button class="nav-link active" data-filter="all" onclick="realtimeChat.filterRequests('all')">
                                        {{ __('All') }} <span class="badge bg-secondary ms-1" id="count-all">0</span>
                                    </button>
                                </li>
                                <li class="nav-item">
                                    <button class="nav-link" data-filter="user" onclick="realtimeChat.filterRequests('user')">
                                        {{ __('Users') }} <span class="badge bg-info ms-1" id="count-users">0</span>
                                    </button>
                                </li>
                                <li class="nav-item">
                                    <button class="nav-link" data-filter="driver" onclick="realtimeChat.filterRequests('driver')">
                                        {{ __('Drivers') }} <span class="badge bg-success ms-1" id="count-drivers">0</span>
                                    </button>
                                </li>
                            </ul>
                        </div>

                        <!-- Search -->
                        <div class="p-3 border-bottom">
                            <input type="text" class="form-control form-control-sm" id="search-requests" 
                                   placeholder="{{ __('Search by name or email...') }}" 
                                   onkeyup="realtimeChat.searchRequests(this.value)">
                        </div>

                        <!-- Requests List -->
                        <div id="support-requests-list" class="support-requests-list">
                            <!-- Content will be loaded by JavaScript -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- Chat Messages Panel -->
            <div class="col-md-8">
                <div class="card h-100">
                    <!-- Chat Header -->
                    <div class="card-header d-none" id="chat-header">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" 
                                         style="width: 40px; height: 40px;">
                                        <i class="fas fa-user" id="chat-avatar-icon"></i>
                                    </div>
                                </div>
                                <div>
                                    <h6 class="mb-0" id="chat-participant-name">{{ __('Select a conversation') }}</h6>
                                    <small class="text-muted" id="chat-participant-info"></small>
                                </div>
                            </div>
                            <div class="d-flex align-items-center">
                                <div class="dropdown me-2">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" 
                                            data-bs-toggle="dropdown">
                                        <i class="fas fa-cog"></i> {{ __('Actions') }}
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="#" onclick="realtimeChat.updateStatus('in_progress')">
                                            <i class="fas fa-play text-warning"></i> {{ __('Mark In Progress') }}
                                        </a></li>
                                        <li><a class="dropdown-item" href="#" onclick="realtimeChat.updateStatus('resolved')">
                                            <i class="fas fa-check text-success"></i> {{ __('Mark Resolved') }}
                                        </a></li>
                                        <li><a class="dropdown-item" href="#" onclick="realtimeChat.updateStatus('closed')">
                                            <i class="fas fa-times text-danger"></i> {{ __('Close Ticket') }}
                                        </a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item" href="#" onclick="realtimeChat.setPriority('high')">
                                            <i class="fas fa-exclamation text-danger"></i> {{ __('High Priority') }}
                                        </a></li>
                                    </ul>
                                </div>
                                <span class="badge" id="chat-status-badge">{{ __('Status') }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Chat Body -->
                    <div class="card-body d-flex flex-column" style="height: 600px;">
                        <!-- Welcome Message -->
                        <div id="welcome-message" class="d-flex align-items-center justify-content-center h-100">
                            <div class="text-center">
                                <i class="fas fa-comments fa-4x text-muted mb-3"></i>
                                <h5 class="text-muted">{{ __('Welcome to Real-time Support Chat') }}</h5>
                                <p class="text-muted">{{ __('Select a support request from the left panel to start chatting') }}</p>
                                <div class="mt-3">
                                    <small class="text-muted">
                                        <i class="fas fa-circle text-success"></i> {{ __('Real-time updates enabled') }}
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Chat Messages -->
                        <div id="chat-messages" class="flex-grow-1 overflow-auto d-none chat-messages-container">
                            <!-- Messages will be loaded here -->
                        </div>

                        <!-- Typing Indicator -->
                        <div id="typing-indicator" class="d-none">
                            <small class="text-muted">
                                <i class="fas fa-circle-notch fa-spin"></i> {{ __('User is typing...') }}
                            </small>
                        </div>

                        <!-- Message Input -->
                        <div id="message-input-container" class="mt-3 d-none">
                            <form id="message-form">
                                <div class="input-group">
                                    <input type="text" class="form-control" id="message-input" 
                                           placeholder="{{ __('Type your message...') }}" maxlength="1000">
                                    <button class="btn btn-outline-secondary" type="button" onclick="realtimeChat.toggleEmojiPicker()">
                                        <i class="fas fa-smile"></i>
                                    </button>
                                    <button class="btn btn-primary" type="submit" id="send-btn">
                                        <i class="fas fa-paper-plane"></i>
                                    </button>
                                </div>
                                <div class="d-flex justify-content-between mt-1">
                                    <small class="text-muted">{{ __('Press Enter to send, Shift+Enter for new line') }}</small>
                                    <small class="text-muted">
                                        <span id="char-count">0</span>/1000
                                    </small>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Custom CSS -->
    <style>
        .support-requests-list {
            max-height: 500px;
            overflow-y: auto;
        }
        
        .support-request-item {
            padding: 12px 16px;
            border-bottom: 1px solid #eee;
            cursor: pointer;
            transition: all 0.2s;
            position: relative;
        }
        
        .support-request-item:hover {
            background-color: #f8f9fa;
            transform: translateX(2px);
        }
        
        .support-request-item.active {
            background-color: #e3f2fd;
            border-left: 4px solid #2196f3;
        }
        
        .support-request-item.unread {
            background-color: #fff3e0;
        }
        
        .support-request-item.urgent {
            border-left: 4px solid #f44336;
        }
        
        .chat-messages-container {
            background: linear-gradient(to bottom, #f8f9fa 0%, #ffffff 100%);
            padding: 15px;
        }
        
        .message-bubble {
            max-width: 75%;
            margin-bottom: 15px;
            padding: 10px 15px;
            border-radius: 20px;
            word-wrap: break-word;
            position: relative;
            animation: fadeInUp 0.3s ease;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .message-bubble.admin {
            background: linear-gradient(135deg, #2196f3 0%, #1976d2 100%);
            color: white;
            margin-left: auto;
            text-align: right;
            border-bottom-right-radius: 5px;
        }
        
        .message-bubble.user {
            background: linear-gradient(135deg, #f1f3f4 0%, #e8eaf6 100%);
            color: #333;
            margin-right: auto;
            border-bottom-left-radius: 5px;
        }
        
        .message-time {
            font-size: 0.75rem;
            opacity: 0.7;
            margin-top: 5px;
        }
        
        .message-status {
            font-size: 0.7rem;
            opacity: 0.6;
            margin-top: 2px;
        }
        
        .unread-badge {
            background: linear-gradient(135deg, #ff4444 0%, #cc0000 100%);
            color: white;
            border-radius: 12px;
            padding: 3px 8px;
            font-size: 0.7rem;
            min-width: 20px;
            text-align: center;
            font-weight: bold;
        }
        
        .priority-badge {
            position: absolute;
            top: 8px;
            right: 8px;
            font-size: 0.6rem;
            padding: 2px 6px;
        }
        
        .nav-tabs-sm .nav-link {
            padding: 8px 12px;
            font-size: 0.875rem;
        }
        
        .connection-indicator {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 5px;
        }
        
        .connection-indicator.connected {
            background-color: #4caf50;
            animation: pulse 2s infinite;
        }
        
        .connection-indicator.disconnected {
            background-color: #f44336;
        }
        
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }
        
        .typing-indicator {
            padding: 10px 15px;
            background-color: #f1f3f4;
            border-radius: 20px;
            margin-bottom: 10px;
            animation: fadeIn 0.3s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
    </style>

    <!-- Firebase SDK -->
    <script src="https://www.gstatic.com/firebasejs/9.0.0/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.0.0/firebase-database-compat.js"></script>

    <!-- JavaScript -->
    <script>
        // Firebase Configuration
        const firebaseConfig = {
            databaseURL: "{{ config('services.firebase.database_url') }}"
        };

        // Initialize Firebase
        firebase.initializeApp(firebaseConfig);
        const database = firebase.database();

        class RealtimeSupportChat {
            constructor() {
                this.currentRequest = null;
                this.supportRequests = [];
                this.filteredRequests = [];
                this.currentFilter = 'all';
                this.messageListeners = new Map();
                this.isConnected = false;
            }

            async init() {
                this.bindEvents();
                await this.loadSupportRequests();
                await this.loadStatistics();
                this.setupFirebaseListeners();
                this.startPeriodicUpdates();
                this.updateConnectionStatus();
            }

            bindEvents() {
                // Message form
                document.getElementById('message-form').addEventListener('submit', (e) => {
                    e.preventDefault();
                    this.sendMessage();
                });

                // Character count
                document.getElementById('message-input').addEventListener('input', (e) => {
                    document.getElementById('char-count').textContent = e.target.value.length;
                });

                // Enter key handling
                document.getElementById('message-input').addEventListener('keypress', (e) => {
                    if (e.key === 'Enter' && !e.shiftKey) {
                        e.preventDefault();
                        this.sendMessage();
                    }
                });

                // Connection status
                database.ref('.info/connected').on('value', (snapshot) => {
                    this.isConnected = snapshot.val();
                    this.updateConnectionStatus();
                });
            }

            updateConnectionStatus() {
                const statusElement = document.getElementById('connection-status');
                if (this.isConnected) {
                    statusElement.textContent = '{{ __("Connected") }}';
                    statusElement.className = 'badge bg-success';
                } else {
                    statusElement.textContent = '{{ __("Disconnected") }}';
                    statusElement.className = 'badge bg-danger';
                }
            }

            async loadSupportRequests() {
                try {
                    const response = await fetch('{{ route("support-chat.active-requests") }}');
                    const data = await response.json();
                    
                    if (data.success) {
                        this.supportRequests = data.data;
                        this.filterRequests(this.currentFilter);
                        this.updateCounts();
                    }
                } catch (error) {
                    console.error('Error loading support requests:', error);
                }
            }

            async loadStatistics() {
                try {
                    const response = await fetch('{{ route("support-chat.statistics") }}');
                    const data = await response.json();
                    
                    if (data.success) {
                        const stats = data.data;
                        document.getElementById('stat-pending').textContent = stats.pending_requests;
                        document.getElementById('stat-in-progress').textContent = stats.in_progress_requests;
                        document.getElementById('stat-users').textContent = stats.user_conversations;
                        document.getElementById('stat-drivers').textContent = stats.driver_conversations;
                        document.getElementById('stat-today').textContent = stats.today_requests;
                        document.getElementById('stat-total').textContent = stats.total_requests;
                        document.getElementById('total-unread').textContent = stats.total_unread_messages;
                    }
                } catch (error) {
                    console.error('Error loading statistics:', error);
                }
            }

            setupFirebaseListeners() {
                // Listen for new support requests
                database.ref('support_requests').on('child_added', (snapshot) => {
                    this.loadSupportRequests();
                    this.loadStatistics();
                });

                database.ref('support_requests').on('child_removed', (snapshot) => {
                    this.loadSupportRequests();
                    this.loadStatistics();
                });
            }

            filterRequests(filter) {
                this.currentFilter = filter;
                
                // Update active tab
                document.querySelectorAll('[data-filter]').forEach(btn => {
                    btn.classList.remove('active');
                });
                document.querySelector(`[data-filter="${filter}"]`).classList.add('active');

                // Filter requests
                if (filter === 'all') {
                    this.filteredRequests = this.supportRequests;
                } else {
                    this.filteredRequests = this.supportRequests.filter(req => req.requester_type === filter);
                }

                this.renderSupportRequests();
            }

            searchRequests(query) {
                if (!query.trim()) {
                    this.filterRequests(this.currentFilter);
                    return;
                }

                const searchTerm = query.toLowerCase();
                this.filteredRequests = this.supportRequests.filter(req => 
                    req.requester_name.toLowerCase().includes(searchTerm) ||
                    (req.requester_email && req.requester_email.toLowerCase().includes(searchTerm))
                );

                this.renderSupportRequests();
            }

            renderSupportRequests() {
                const container = document.getElementById('support-requests-list');
                
                if (this.filteredRequests.length === 0) {
                    container.innerHTML = `
                        <div class="text-center p-4">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <p class="text-muted">{{ __('No support requests found') }}</p>
                        </div>
                    `;
                    return;
                }

                container.innerHTML = this.filteredRequests.map(req => `
                    <div class="support-request-item ${req.unread_count > 0 ? 'unread' : ''} ${req.priority === 'urgent' ? 'urgent' : ''}" 
                         data-request-id="${req.id}"
                         onclick="realtimeChat.selectRequest(${req.requester_id}, '${req.requester_type}', ${req.id})">
                        
                        ${req.priority === 'urgent' ? '<div class="priority-badge badge bg-danger">URGENT</div>' : ''}
                        
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center mb-1">
                                    <i class="fas fa-${req.requester_type === 'driver' ? 'car' : 'user'} me-2 text-${req.requester_type === 'driver' ? 'success' : 'info'}"></i>
                                    <h6 class="mb-0">${req.requester_name}</h6>
                                </div>
                                <p class="mb-1 text-muted small">${req.last_message || '{{ __("No messages yet") }}'}</p>
                                <div class="d-flex align-items-center">
                                    <small class="text-muted me-2">${this.formatTime(req.last_message_at)}</small>
                                    <span class="badge bg-${this.getStatusColor(req.status)} badge-sm">${req.status}</span>
                                </div>
                            </div>
                            <div class="d-flex flex-column align-items-end">
                                ${req.unread_count > 0 ? `<span class="unread-badge">${req.unread_count}</span>` : ''}
                            </div>
                        </div>
                    </div>
                `).join('');
            }

            async selectRequest(requesterId, requesterType, requestId) {
                // Update active state
                document.querySelectorAll('.support-request-item').forEach(item => {
                    item.classList.remove('active');
                });
                document.querySelector(`[data-request-id="${requestId}"]`).classList.add('active');

                this.currentRequest = {
                    requester_id: requesterId,
                    requester_type: requesterType,
                    request_id: requestId
                };
                
                // Show chat interface
                document.getElementById('welcome-message').classList.add('d-none');
                document.getElementById('chat-header').classList.remove('d-none');
                document.getElementById('chat-messages').classList.remove('d-none');
                document.getElementById('message-input-container').classList.remove('d-none');

                // Load conversation
                await this.loadConversation(requesterId, requesterType);
                
                // Setup real-time message listener
                this.setupMessageListener(requesterId);
            }

            async loadConversation(requesterId, requesterType) {
                try {
                    const response = await fetch(`{{ url('admin/support-chat/chat') }}/${requesterId}/${requesterType}`);
                    const data = await response.json();
                    
                    if (data.success) {
                        const { messages, target_info } = data.data;
                        
                        // Update header
                        if (target_info) {
                            document.getElementById('chat-participant-name').textContent = target_info.name;
                            document.getElementById('chat-participant-info').textContent = `${target_info.type} - ${target_info.email}`;
                            document.getElementById('chat-avatar-icon').className = `fas fa-${target_info.type === 'driver' ? 'car' : 'user'}`;
                        }
                        
                        // Update status badge
                        const request = this.supportRequests.find(r => r.requester_id == requesterId);
                        if (request) {
                            const statusBadge = document.getElementById('chat-status-badge');
                            statusBadge.textContent = request.status.toUpperCase();
                            statusBadge.className = `badge bg-${this.getStatusColor(request.status)}`;
                        }
                        
                        // Render messages
                        this.renderMessages(messages);
                    }
                } catch (error) {
                    console.error('Error loading conversation:', error);
                }
            }

            setupMessageListener(requesterId) {
                const roomId = `admin_${requesterId}`;
                
                // Remove existing listener
                if (this.messageListeners.has(roomId)) {
                    this.messageListeners.get(roomId).off();
                }
                
                // Setup new listener
                const messagesRef = database.ref(`chats/${roomId}/messages`);
                messagesRef.on('child_added', (snapshot) => {
                    if (this.currentRequest && this.currentRequest.requester_id == requesterId) {
                        this.loadConversation(requesterId, this.currentRequest.requester_type);
                    }
                });
                
                this.messageListeners.set(roomId, messagesRef);
            }

            renderMessages(messages) {
                const container = document.getElementById('chat-messages');
                
                if (messages.length === 0) {
                    container.innerHTML = `
                        <div class="text-center p-4">
                            <i class="fas fa-comment-dots fa-3x text-muted mb-3"></i>
                            <p class="text-muted">{{ __('No messages in this conversation') }}</p>
                            <small class="text-muted">{{ __('Start the conversation by sending a message') }}</small>
                        </div>
                    `;
                    return;
                }

                container.innerHTML = messages.map(msg => `
                    <div class="d-flex ${msg.is_admin_message ? 'justify-content-end' : 'justify-content-start'} mb-3">
                        <div class="message-bubble ${msg.is_admin_message ? 'admin' : 'user'}">
                            <div class="message-content">${this.formatMessage(msg.message)}</div>
                            <div class="message-time">${this.formatTime(msg.timestamp)}</div>
                            ${msg.is_admin_message ? '<div class="message-status">✓ Sent</div>' : ''}
                        </div>
                    </div>
                `).join('');

                // Scroll to bottom
                container.scrollTop = container.scrollHeight;
            }

            async sendMessage() {
                const input = document.getElementById('message-input');
                const message = input.value.trim();
                
                if (!message || !this.currentRequest) return;

                // Disable send button
                const sendBtn = document.getElementById('send-btn');
                sendBtn.disabled = true;
                sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

                try {
                    const response = await fetch('{{ route("support-chat.reply") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            target_id: this.currentRequest.requester_id,
                            target_type: this.currentRequest.requester_type,
                            message: message
                        })
                    });

                    const data = await response.json();
                    
                    if (data.success) {
                        input.value = '';
                        document.getElementById('char-count').textContent = '0';
                        // Message will be updated via Firebase listener
                    } else {
                        alert('{{ __("Failed to send message") }}');
                    }
                } catch (error) {
                    console.error('Error sending message:', error);
                    alert('{{ __("Failed to send message") }}');
                } finally {
                    // Re-enable send button
                    sendBtn.disabled = false;
                    sendBtn.innerHTML = '<i class="fas fa-paper-plane"></i>';
                }
            }

            async updateStatus(status) {
                if (!this.currentRequest) return;

                try {
                    const response = await fetch(`{{ url('admin/support-chat/requests') }}/${this.currentRequest.request_id}/status`, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({ status })
                    });

                    const data = await response.json();
                    
                    if (data.success) {
                        await this.loadSupportRequests();
                        await this.loadStatistics();
                        
                        // Update status badge
                        const statusBadge = document.getElementById('chat-status-badge');
                        statusBadge.textContent = status.toUpperCase();
                        statusBadge.className = `badge bg-${this.getStatusColor(status)}`;
                        
                        // Show success message
                        this.showNotification(`{{ __('Status updated to') }} ${status}`, 'success');
                    }
                } catch (error) {
                    console.error('Error updating status:', error);
                    this.showNotification('{{ __("Failed to update status") }}', 'error');
                }
            }

            async setPriority(priority) {
                if (!this.currentRequest) return;

                try {
                    const response = await fetch(`{{ url('admin/support-chat/requests') }}/${this.currentRequest.request_id}/status`, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({ priority })
                    });

                    const data = await response.json();
                    
                    if (data.success) {
                        await this.loadSupportRequests();
                        this.showNotification(`{{ __('Priority set to') }} ${priority}`, 'success');
                    }
                } catch (error) {
                    console.error('Error setting priority:', error);
                    this.showNotification('{{ __("Failed to set priority") }}', 'error');
                }
            }

            refreshRequests() {
                this.loadSupportRequests();
                this.loadStatistics();
            }

            updateCounts() {
                const allCount = this.supportRequests.length;
                const userCount = this.supportRequests.filter(r => r.requester_type === 'user').length;
                const driverCount = this.supportRequests.filter(r => r.requester_type === 'driver').length;

                document.getElementById('count-all').textContent = allCount;
                document.getElementById('count-users').textContent = userCount;
                document.getElementById('count-drivers').textContent = driverCount;
            }

            startPeriodicUpdates() {
                // Refresh data every 30 seconds
                setInterval(() => {
                    this.loadSupportRequests();
                    this.loadStatistics();
                }, 30000);
            }

            formatTime(timestamp) {
                if (!timestamp) return '';
                const date = new Date(timestamp);
                const now = new Date();
                const diff = now - date;
                
                if (diff < 60000) return '{{ __("Just now") }}';
                if (diff < 3600000) return Math.floor(diff / 60000) + '{{ __("m ago") }}';
                if (diff < 86400000) return Math.floor(diff / 3600000) + '{{ __("h ago") }}';
                
                return date.toLocaleDateString();
            }

            formatMessage(message) {
                // Basic message formatting
                return message
                    .replace(/\n/g, '<br>')
                    .replace(/(https?:\/\/[^\s]+)/g, '<a href="$1" target="_blank">$1</a>');
            }

            getStatusColor(status) {
                const colors = {
                    'pending': 'warning',
                    'in_progress': 'info',
                    'resolved': 'success',
                    'closed': 'secondary'
                };
                return colors[status] || 'secondary';
            }

            showNotification(message, type = 'info') {
                // Simple notification system
                const notification = document.createElement('div');
                notification.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
                notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
                notification.innerHTML = `
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                
                document.body.appendChild(notification);
                
                setTimeout(() => {
                    notification.remove();
                }, 5000);
            }

            toggleEmojiPicker() {
                // Simple emoji insertion
                const emojis = ['😊', '👍', '❤️', '😢', '😡', '🤔', '👋', '🙏'];
                const input = document.getElementById('message-input');
                const randomEmoji = emojis[Math.floor(Math.random() * emojis.length)];
                input.value += randomEmoji;
                input.focus();
            }
        }

        // Initialize the real-time chat
        const realtimeChat = new RealtimeSupportChat();
        
        document.addEventListener('DOMContentLoaded', function() {
            realtimeChat.init();
        });
    </script>
@endsection