@extends('layouts.app')
@php
    $currentPage = 'push-logs';
    $statusLabels = [
        'accepted' => [__('Firebase accepted'), 'success'],
        'failed' => [__('Firebase rejected'), 'danger'],
        'skipped' => [__('Not sent'), 'warning'],
        'exception' => [__('Server error'), 'secondary'],
    ];
    $roleLabels = [
        'driver' => __('Driver'),
        'user' => __('Passenger'),
        'rider' => __('Courier'),
    ];
@endphp
@section('title', __('Push log'))
@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-start mb-3 gap-2">
        <div>
            <h1 class="mb-1">{{ __('Push log') }}</h1>
            <p class="text-body-secondary mb-0">{{ __('Each row is one attempt to send a push from this server.') }}</p>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <h2 class="h5 mb-1">{{ __('Send a test ride alert') }}</h2>
            <p class="text-body-secondary small mb-3">{{ __('Choose a driver, put the driver app in the background, then send. This uses the same loud ride alert as a real offer. It does not create a ride, and the driver cannot accept it.') }}</p>
            <form method="POST" action="{{ route('push-logs.test-ride') }}" class="row g-2 align-items-end">
                @csrf
                <div class="col-md-6">
                    <label class="form-label" for="test_driver_id">{{ __('Driver') }}</label>
                    <select class="form-select @error('driver_id') is-invalid @enderror" id="test_driver_id" name="driver_id" required>
                        <option value="">{{ __('Choose a driver') }}</option>
                        @foreach ($drivers as $driver)
                            <option value="{{ $driver->id }}" @selected(old('driver_id') == $driver->id)>
                                {{ $driver->name }} — {{ $driver->phone }}
                                @if (!$driver->fcm_token)
                                    ({{ __('no token') }})
                                @endif
                            </option>
                        @endforeach
                    </select>
                    @error('driver_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-3">
                    <button class="btn btn-primary" type="submit">{{ __('Send') }}</button>
                </div>
            </form>
        </div>
    </div>

    <div class="alert alert-info">
        {{ __('Firebase accepted means Google took the message. It does not mean the phone showed it. Not sent means this server never called Firebase, usually because that account has no saved token. Firebase rejected means the token is dead or Google refused the message. A driver can still receive the ride while the app is open, because that path uses the live connection and not this push.') }}
    </div>

    <div class="row mb-3">
        @foreach (['accepted' => 'success', 'failed' => 'danger', 'skipped' => 'warning', 'exception' => 'secondary'] as $status => $color)
            <div class="col-6 col-md-3 mb-3">
                <a class="text-decoration-none" href="{{ route('push-logs.index', ['status' => $status]) }}">
                    <div class="card h-100 border-{{ $color }}">
                        <div class="card-body">
                            <div class="text-body-secondary small">{{ $statusLabels[$status][0] }} · {{ __('Last 24 hours') }}</div>
                            <div class="fs-3 mb-0">{{ number_format($counts[$status] ?? 0) }}</div>
                        </div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('push-logs.index') }}" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label" for="q">{{ __('Filter') }}</label>
                    <input class="form-control" id="q" name="q" value="{{ $filters['q'] }}" placeholder="{{ __('Search name, phone, or id') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label" for="status">{{ __('Status') }}</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">{{ __('All statuses') }}</option>
                        @foreach ($statusLabels as $value => $label)
                            <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label[0] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label" for="role">{{ __('Role') }}</label>
                    <select class="form-select" id="role" name="role">
                        <option value="">{{ __('All roles') }}</option>
                        @foreach ($roleLabels as $value => $label)
                            <option value="{{ $value }}" @selected($filters['role'] === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label" for="msg_type">{{ __('Type') }}</label>
                    <select class="form-select" id="msg_type" name="msg_type">
                        <option value="">{{ __('All types') }}</option>
                        @foreach ($msgTypes as $type)
                            <option value="{{ $type }}" @selected($filters['msg_type'] === $type)>{{ $type }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-1">
                    <label class="form-label" for="ride_id">{{ __('Ride id') }}</label>
                    <input class="form-control" id="ride_id" name="ride_id" value="{{ $filters['ride_id'] }}" inputmode="numeric">
                </div>
                <div class="col-6 col-md-1">
                    <label class="form-label" for="delivery_id">{{ __('Delivery id') }}</label>
                    <input class="form-control" id="delivery_id" name="delivery_id" value="{{ $filters['delivery_id'] }}" inputmode="numeric">
                </div>
                <div class="col-12 d-flex flex-wrap align-items-center gap-3">
                    <div class="form-check mb-0">
                        <input class="form-check-input" type="checkbox" id="problems" name="problems" value="1" @checked($filters['problems'])>
                        <label class="form-check-label" for="problems">{{ __('Problems only') }}</label>
                    </div>
                    <button class="btn btn-primary btn-sm" type="submit">{{ __('Apply') }}</button>
                    <a class="btn btn-outline-secondary btn-sm" href="{{ route('push-logs.index') }}">{{ __('Reset') }}</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>{{ __('When') }}</th>
                            <th>{{ __('Person') }}</th>
                            <th>{{ __('Type') }}</th>
                            <th>{{ __('Ride') }}</th>
                            <th>{{ __('Result') }}</th>
                            <th>{{ __('Token') }}</th>
                            <th>{{ __('Priority') }}</th>
                            <th>{{ __('Error') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($logs as $log)
                            @php
                                [$statusText, $statusColor] = $statusLabels[$log->status] ?? [$log->status, 'secondary'];
                                $roleText = $roleLabels[$log->recipient_role] ?? ($log->recipient_role ?: '—');
                            @endphp
                            <tr>
                                <td class="text-nowrap">
                                    <div>{{ $log->created_at?->format('Y-m-d H:i:s') }}</div>
                                    <div class="small text-body-secondary">{{ $log->created_at?->diffForHumans() }}</div>
                                    @if ($log->duration_ms)
                                        <div class="small text-body-secondary">{{ $log->duration_ms }} ms</div>
                                    @endif
                                </td>
                                <td>
                                    @if ($log->recipient)
                                        @php
                                            $personRoute = $log->recipient_role === 'driver'
                                                ? route('drivers.show', $log->recipient_id)
                                                : route('users.show', $log->recipient_id);
                                        @endphp
                                        <a href="{{ $personRoute }}">{{ $log->recipient->name }}</a>
                                        <div class="small text-body-secondary">{{ $roleText }} · {{ $log->recipient->phone }} · #{{ $log->recipient_id }}</div>
                                    @else
                                        <div>{{ $roleText }}</div>
                                        <div class="small text-body-secondary">#{{ $log->recipient_id ?: '—' }}</div>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $log->msg_type ?: '—' }}</div>
                                    <div>{{ $log->title }}</div>
                                    <div class="small text-body-secondary">{{ \Illuminate\Support\Str::limit($log->body, 80) }}</div>
                                </td>
                                <td class="text-nowrap">
                                    @if ($log->ride_id)
                                        <a href="{{ route('rides.show', $log->ride_id) }}">{{ __('Ride') }} #{{ $log->ride_id }}</a>
                                    @endif
                                    @if ($log->delivery_id)
                                        <div><a href="{{ route('deliveries.show', $log->delivery_id) }}">{{ __('Delivery') }} #{{ $log->delivery_id }}</a></div>
                                    @endif
                                    @if (!$log->ride_id && !$log->delivery_id)
                                        —
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $statusColor }}">{{ $statusText }}</span>
                                    @if ($log->http_status)
                                        <div class="small text-body-secondary">HTTP {{ $log->http_status }}</div>
                                    @endif
                                    @if ($log->fcm_message_id)
                                        <div class="small text-body-secondary" title="{{ $log->fcm_message_id }}">{{ __('Message id') }}</div>
                                    @endif
                                </td>
                                <td>
                                    @if ($log->has_token)
                                        <span class="text-success">{{ __('Saved') }}</span>
                                        <div class="small text-body-secondary">{{ $log->token_prefix }}…</div>
                                    @else
                                        <span class="text-danger">{{ __('Missing') }}</span>
                                    @endif
                                </td>
                                <td>{{ $log->high_priority ? __('High') : __('Normal') }}</td>
                                <td>
                                    @if ($log->error_code)
                                        <div class="fw-semibold">{{ $log->error_code }}</div>
                                    @endif
                                    <div class="small">{{ $log->error_message ?: '—' }}</div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-body-secondary py-4">
                                    @if ($filters['q'] || $filters['status'] || $filters['role'] || $filters['msg_type'] || $filters['ride_id'] || $filters['delivery_id'] || $filters['problems'])
                                        {{ __('No pushes match these filters.') }}
                                    @else
                                        {{ __('No push attempts recorded yet. New ride offers will appear here.') }}
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($logs->hasPages())
            <div class="card-footer">
                {{ $logs->links('pagination::custom') }}
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const driverSelect = document.querySelector('#test_driver_id');
            if (driverSelect && typeof Choices !== 'undefined') {
                new Choices(driverSelect, {
                    searchEnabled: true,
                    searchPlaceholderValue: @json(__('Search drivers...')),
                    noResultsText: @json(__('No results found')),
                    itemSelectText: '',
                    shouldSort: false,
                    allowHTML: false,
                });
            }
        });
    </script>
@endpush
