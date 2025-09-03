{{-- Support Chat Quick Access Widget --}}
<div class="col-md-6 col-lg-4 mb-4">
    <div class="card border-0 shadow-sm h-100">
        <div class="card-header bg-gradient-info text-white">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <i class="fas fa-headset fa-lg me-2"></i>
                    <h6 class="mb-0">{{ __('Support Chat') }}</h6>
                </div>
                <span class="badge bg-light text-dark" id="support-widget-unread">0</span>
            </div>
        </div>
        <div class="card-body">
            <div class="row text-center">
                <div class="col-6">
                    <div class="border-end">
                        <h4 class="text-warning mb-1" id="support-widget-pending">0</h4>
                        <small class="text-muted">{{ __('Pending') }}</small>
                    </div>
                </div>
                <div class="col-6">
                    <h4 class="text-info mb-1" id="support-widget-active">0</h4>
                    <small class="text-muted">{{ __('Active') }}</small>
                </div>
            </div>
            
            <hr class="my-3">
            
            <div class="d-grid gap-2">
                <a href="{{ route('support-chat.realtime') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-comments me-1"></i>
                    {{ __('Open Real-time Chat') }}
                </a>
                <div class="row g-1">
                    <div class="col">
                        <a href="{{ route('support-chat.index') }}" class="btn btn-outline-secondary btn-sm w-100">
                            <i class="fas fa-list me-1"></i>
                            {{ __('View All') }}
                        </a>
                    </div>
                    <div class="col">
                        <a href="{{ route('support-chat.test') }}" class="btn btn-outline-info btn-sm w-100">
                            <i class="fas fa-vial me-1"></i>
                            {{ __('Test') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-update support widget stats
async function updateSupportWidget() {
    try {
        const response = await fetch('{{ route("support-chat.statistics") }}');
        const data = await response.json();
        
        if (data.success) {
            const stats = data.data;
            document.getElementById('support-widget-unread').textContent = stats.total_unread_messages || 0;
            document.getElementById('support-widget-pending').textContent = stats.pending_requests || 0;
            document.getElementById('support-widget-active').textContent = stats.in_progress_requests || 0;
        }
    } catch (error) {
        console.log('Support widget update failed:', error);
    }
}

// Update on page load and every 30 seconds
document.addEventListener('DOMContentLoaded', function() {
    updateSupportWidget();
    setInterval(updateSupportWidget, 30000);
});
</script>