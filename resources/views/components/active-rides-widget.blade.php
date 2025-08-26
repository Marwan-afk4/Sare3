@props(['activeRides' => []])

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fa fa-location-arrow text-primary"></i>
            {{ __('Active Rides') }}
        </h5>
        <span class="badge bg-primary">{{ count($activeRides) }}</span>
    </div>
    <div class="card-body">
        @if(count($activeRides) > 0)
            <div class="list-group list-group-flush">
                @foreach($activeRides as $ride)
                    <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <div class="status-indicator status-{{ $ride->status->value === 'in_progress' ? 'live' : 'pending' }}"></div>
                            </div>
                            <div>
                                <div class="fw-bold">Ride #{{ $ride->id }}</div>
                                <small class="text-muted">
                                    {{ $ride->user?->name }} → {{ $ride->driver?->name }}
                                </small>
                                <div class="small text-muted">
                                    {!! $ride->status->badge() !!}
                                </div>
                            </div>
                        </div>
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('rides.track', $ride) }}" class="btn btn-outline-primary btn-sm" title="{{ __('Track') }}">
                                <i class="fa fa-location-arrow"></i>
                            </a>
                            <a href="{{ route('rides.show', $ride) }}" class="btn btn-outline-secondary btn-sm" title="{{ __('Details') }}">
                                <i class="fa fa-eye"></i>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="mt-3 text-center">
                <a href="{{ route('rides.index', ['status' => 'in_progress']) }}" class="btn btn-sm btn-outline-primary">
                    {{ __('View All Active Rides') }}
                </a>
            </div>
        @else
            <div class="text-center text-muted py-4">
                <i class="fa fa-car fa-2x mb-2"></i>
                <p class="mb-0">{{ __('No active rides at the moment') }}</p>
            </div>
        @endif
    </div>
</div>

<style>
.status-indicator {
    display: inline-block;
    width: 8px;
    height: 8px;
    border-radius: 50%;
    animation: pulse 2s infinite;
}

.status-live { background-color: #4CAF50; }
.status-pending { background-color: #FF9800; }

@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.5; }
    100% { opacity: 1; }
}
</style>