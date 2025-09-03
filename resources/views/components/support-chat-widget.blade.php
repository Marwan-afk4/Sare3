@props(['stats' => null])

<div class="card border-0 shadow-sm">
    <div class="card-header bg-gradient-primary text-white">
        <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <i class="fas fa-headset fa-lg me-2"></i>
                <h6 class="mb-0">{{ __('Support Chat') }}</h6>
            </div>
            <div class="d-flex align-items-center">
                <span class="badge bg-light text-dark" id="support-unread-count">
                    {{ $stats['total_unread_messages'] ?? 0 }}
                </span>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <!-- Quick Stats -->
        <div class="row g-0">
            <div class="col-6">
                <div class="p-3 text-center border-end">
                    <div class="h4 mb-1 text-warning">{{ $stats['pending_requests'] ?? 0 }}</div>
                    <small class="text-muted">{{ __('Pending') }}</small>
                </div>
            </div>
            <div class="col-6">
                <div class="p-3 text-center">
                    <div class="h4 mb-1 text-info">{{ $stats['in_progress_requests'] ?? 0 }}</div>
                    <small class="text-muted">{{ __('In Progress') }}</small>
                </div>
            </div>
        </div>
        
        <!-- Recent Activity -->
        <div class="border-top">
            <div class="p-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <small class="text-muted fw-bold">{{ __('Recent Activity') }}</small>
                    <div class="connection-status">
                        <span class="connection-dot bg-success"></span>
                        <small class="text-muted">{{ __('Live') }}</small>
                    </div>
                </div>
                
                <div id="recent-support-activity" class="recent-activity-list">
                    <!-- Will be populated by JavaScript -->
                </div>
            </div>
        </div>
        
        <!-- Action Buttons -->
        <div class="card-footer bg-light border-0">
            <div class="d-grid gap-2">
                <a href="{{ route('admin.support-chat.realtime') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-comments me-1"></i>
                    {{ __('Open Chat Dashboard') }}
                </a>
                <div class="row g-1">
                    <div class="col">
                        <a href="{{ route('admin.support-chat.index') }}" class="btn btn-outline-secondary btn-sm w-100">
                            <i class="fas fa-list me-1"></i>
                            {{ __('View All') }}
                        </a>
                    </div>
                    <div class="col">
                        <button class="btn btn-outline-info btn-sm w-100" onclick="refreshSupportWidget()">
                            <i class="fas fa-sync me-1"></i>
                            {{ __('Refresh') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.connection-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    display: inline-block;
    margin-right: 4px;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.5; }
    100% { opacity: 1; }
}

.recent-activity-list {
    max-height: 150px;
    overflow-y: auto;
}

.activity-item {
    padding: 6px 0;
    border-bottom: 1px solid #f0f0f0;
    font-size: 0.875rem;
}

.activity-item:last-child {
    border-bottom: none;
}

.activity-time {
    font-size: 0.75rem;
    color: #6c757d;
}
</style>

<script>
async function refreshSupportWidget() {
    try {
        const response = await fetch('{{ route("admin.support-chat.statistics") }}');
        const data = await response.json();
        
        if (data.success) {
            const stats = data.data;
            
            // Update counters
            document.getElementById('support-unread-count').textContent = stats.total_unread_messages;
            
            // Update stats in the widget
            const pendingElement = document.querySelector('.text-warning');
            const inProgressElement = document.querySelector('.text-info');
            
            if (pendingElement) pendingElement.textContent = stats.pending_requests;
            if (inProgressElement) inProgressElement.textContent = stats.in_progress_requests;
        }
        
        // Load recent activity
        await loadRecentActivity();
        
    } catch (error) {
        console.error('Error refreshing support widget:', error);
    }
}

async function loadRecentActivity() {
    try {
        const response = await fetch('{{ route("admin.support-chat.active-requests") }}');
        const data = await response.json();
        
        if (data.success) {
            const recentRequests = data.data
                .sort((a, b) => new Date(b.updated_at) - new Date(a.updated_at))
                .slice(0, 3);
            
            const container = document.getElementById('recent-support-activity');
            
            if (recentRequests.length === 0) {
                container.innerHTML = `
                    <div class="text-center text-muted py-2">
                        <small>{{ __('No recent activity') }}</small>
                    </div>
                `;
                return;
            }
            
            container.innerHTML = recentRequests.map(req => `
                <div class="activity-item">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <i class="fas fa-${req.requester_type === 'driver' ? 'car' : 'user'} me-1 text-${req.requester_type === 'driver' ? 'success' : 'info'}"></i>
                            <span class="fw-medium">${req.requester_name}</span>
                        </div>
                        <span class="badge bg-${getStatusColor(req.status)} badge-sm">${req.status}</span>
                    </div>
                    <div class="activity-time">${formatTimeAgo(req.updated_at)}</div>
                </div>
            `).join('');
        }
    } catch (error) {
        console.error('Error loading recent activity:', error);
    }
}

function getStatusColor(status) {
    const colors = {
        'pending': 'warning',
        'in_progress': 'info',
        'resolved': 'success',
        'closed': 'secondary'
    };
    return colors[status] || 'secondary';
}

function formatTimeAgo(timestamp) {
    const date = new Date(timestamp);
    const now = new Date();
    const diff = now - date;
    
    if (diff < 60000) return '{{ __("Just now") }}';
    if (diff < 3600000) return Math.floor(diff / 60000) + '{{ __("m ago") }}';
    if (diff < 86400000) return Math.floor(diff / 3600000) + '{{ __("h ago") }}';
    
    return date.toLocaleDateString();
}

// Auto-refresh every 30 seconds
document.addEventListener('DOMContentLoaded', function() {
    loadRecentActivity();
    setInterval(refreshSupportWidget, 30000);
});
</script>