@extends('layouts.app')
@php
    $currentPage = 'rides';
@endphp
@section('title', $ride->name)

@push('styles')
<style>
    #rideMap {
        height: 400px;
        min-height: 400px;
        width: 100%;
        border-radius: 8px;
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
    }
    .map-container {
        margin-bottom: 20px;
    }
    .tracking-status {
        padding: 10px;
        border-radius: 5px;
        margin-bottom: 15px;
    }
    .tracking-live {
        background-color: #e3f2fd;
        border-left: 4px solid #2196f3;
    }
    .tracking-completed {
        background-color: #e8f5e8;
        border-left: 4px solid #4caf50;
    }
</style>
@endpush

@section('content')
    <div class="container-fluid">
        <h1>{{ __('Ride') }} #{{ $ride->id }}</h1>
        <div class="mb-3">
            <a href="{{ route('rides.index') }}" class="btn btn-secondary btn-sm me-1"> <i class="fa fa-arrow-right"></i>
                {{ __('Back to') }} {{ __('Rides') }}</a>
            @if($ride->pickup_lat && $ride->pickup_lng)
                <a href="{{ route('rides.track', $ride) }}" class="btn btn-success btn-sm me-1">
                    @if(in_array($ride->status->value, ['in_progress', 'accepted', 'waiting_user']))
                        <i class="fa fa-location-arrow"></i> {{ __('Live Tracking') }}
                    @else
                        <i class="fa fa-route"></i> {{ __('View Route') }}
                    @endif
                </a>
            @endif
            {{-- <a href='{{ route('rides.edit', $ride) }}' class="btn btn-warning btn-sm me-1">{{ __('Edit') }} <i class="fa fa-edit"></i></a> --}}
        </div>
        <!-- Tracking Status -->
        @if(in_array($ride->status->value, ['in_progress', 'accepted', 'waiting_user']))
            <div class="tracking-status tracking-live">
                <h5><i class="fa fa-location-arrow"></i> {{ __('Live Tracking Active') }}</h5>
                <p>{{ __('Driver location is being tracked in real-time') }}</p>
            </div>
        @elseif(in_array($ride->status->value, ['completed', 'finshed']))
            <div class="tracking-status tracking-completed">
                <h5><i class="fa fa-route"></i> {{ __('Trip Route') }}</h5>
                <p>{{ __('Showing the completed trip route') }}</p>
            </div>
        @elseif(in_array($ride->status->value, ['cancelled', 'rejected']) && $ride->driver_accept_lat && $ride->driver_accept_lng)
            <div class="tracking-status tracking-completed">
                <h5><i class="fa fa-map-marker-alt"></i> {{ __('Ride Route History') }}</h5>
                <p>{{ __('Showing pickup, dropoff, and where the captain accepted the ride') }}</p>
            </div>
        @endif

        <!-- Map Container -->
        @if($ride->pickup_lat && $ride->pickup_lng)
            <div class="map-container">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                        <h5 class="mb-0">
                            @if(in_array($ride->status->value, ['in_progress', 'accepted', 'waiting_user']))
                                <i class="fa fa-location-arrow text-primary"></i> {{ __('Live Ride Tracking') }}
                            @else
                                <i class="fa fa-route text-success"></i> {{ __('Trip Route') }}
                            @endif
                        </h5>
                        <div class="small text-muted d-flex flex-wrap gap-3 mt-1">
                            <span><span class="badge" style="background:#FF9800">A</span> {{ __('Captain accept location') }}</span>
                            <span><span class="badge" style="background:#E53935">X</span> {{ __('Captain cancel location') }}</span>
                            <span><span style="display:inline-block;width:24px;border-top:3px dashed #FF9800;vertical-align:middle"></span> {{ __('On the way to passenger') }}</span>
                            <span><span class="badge" style="background:#FF9800">S</span> {{ __('To-pickup start') }}</span>
                            <span><span class="badge" style="background:#FF9800">E</span> {{ __('To-pickup end') }}</span>
                            <span><span class="badge" style="background:#4CAF50">P</span> {{ __('Pickup') }}</span>
                            <span><span style="display:inline-block;width:24px;border-top:3px solid #4CAF50;vertical-align:middle"></span> {{ __('Trip path') }}</span>
                            <span><span class="badge" style="background:#4CAF50">S</span> {{ __('Trip start') }}</span>
                            <span><span class="badge" style="background:#4CAF50">E</span> {{ __('Trip end') }}</span>
                            <span><span class="badge" style="background:#2196F3"><i class="fa fa-location-arrow"></i></span> {{ __('Captain live') }}</span>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div id="rideMap"></div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Ride Details -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fa fa-info-circle"></i> {{ __('Ride Details') }}</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item">
                                <strong>{{ __('Ride ID') }}:</strong> #{{ $ride->id }}
                            </li>
                            <li class="list-group-item">
                                <strong>{{ __('User') }}:</strong> {{ $ride->user?->name ?? '-' }}
                            </li>
                            <li class="list-group-item">
                                <strong>{{ __('Driver') }}:</strong> {{ $ride->driver?->name ?? '-' }}
                            </li>
                            <li class="list-group-item">
                                <strong>{{ __('Car Category') }}:</strong> {{ $ride->carCategory?->name ?? '-' }}
                            </li>
                            <li class="list-group-item">
                                <strong>{{ __('Status') }}:</strong>
                                <span id="ride-status-badge">{!! $ride->status->badge() !!}</span>

                                <form action="{{ route('rides.updateStatus', $ride) }}" method="POST" class="d-inline-block ms-2">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status" class="form-select form-select-sm d-inline-block w-auto" onchange="this.form.submit()">
                                        @foreach($rideStatuses as $value => $label)
                                            <option value="{{ $value }}" {{ $ride->status->value === $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </form>
                            </li>
                            <li class="list-group-item">
                                <strong>{{ __('Created At') }}:</strong> {{ $ride->created_at->diffForHumans() ?? '-' }}
                            </li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item">
                                <strong>{{ __('Pickup Address') }}:</strong>
                                <small class="text-muted d-block">{{ $ride->pickup_address ?? '-' }}</small>
                            </li>
                            <li class="list-group-item">
                                <strong>{{ __('Dropoff Address') }}:</strong>
                                <small class="text-muted d-block">{{ $ride->dropoff_address ?? '-' }}</small>
                            </li>
                            <li class="list-group-item">
                                <strong>{{ __('Estimated Distance') }}:</strong> {{ $ride->estimated_km ?? '-' }} km
                            </li>
                            <li class="list-group-item">
                                <strong>{{ __('Actual Distance') }}:</strong> {{ $ride->total_distance_in_km ?? '-' }} km
                            </li>
                            <li class="list-group-item">
                                <strong>{{ __('Estimated Time') }}:</strong> {{ $ride->estimated_time ?? '-' }} min
                            </li>
                            <li class="list-group-item">
                                <strong>{{ __('Actual Time') }}:</strong> {{ $ride->time_taken ?? '-' }} min
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pricing Details -->
        @if($ride->calculated_initial_price || $ride->calculated_final_price)
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fa fa-dollar-sign"></i> {{ __('Pricing Details') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>{{ __('Initial Price') }}:</strong> ${{ $ride->calculated_initial_price ?? '0.00' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>{{ __('Final Price') }}:</strong> ${{ $ride->calculated_final_price ?? '0.00' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Ride settlement: Sarea profit, passenger cash/wallet, captain collection -->
        @php
            $money = function ($amount) {
                if ($amount === null) {
                    return '—';
                }

                return '<span dir="ltr">$'.number_format($amount, 2).'</span>';
            };
        @endphp
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0"><i class="fa fa-wallet"></i> {{ __('Ride Settlement') }}</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6 col-xl-3">
                        <div class="border rounded p-3 h-100">
                            <div class="text-muted small">{{ __('Sarea Profit') }}</div>
                            <div class="fs-4 fw-bold text-success mb-0">{!! $money($settlement['sarea_profit']) !!}</div>
                            @if($settlement['sarea_profit_percentage'] !== null)
                                <div class="small text-muted"><span dir="ltr">{{ number_format($settlement['sarea_profit_percentage'], 1) }}%</span></div>
                            @else
                                <div class="small text-muted">{{ __('Recorded when the ride is completed.') }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6 col-xl-3">
                        <div class="border rounded p-3 h-100">
                            <div class="text-muted small">{{ __('Paid from Wallet') }}</div>
                            <div class="fs-4 fw-bold text-primary mb-0">{!! $money($settlement['wallet_paid']) !!}</div>
                            <div class="small text-muted">{{ __('Amount the passenger paid from his wallet') }}</div>
                        </div>
                    </div>
                    <div class="col-md-6 col-xl-3">
                        <div class="border rounded p-3 h-100">
                            <div class="text-muted small">{{ __('Paid in Cash') }}</div>
                            <div class="fs-4 fw-bold mb-0">{!! $money($settlement['cash_paid']) !!}</div>
                            <div class="small text-muted">{{ __('Amount the passenger paid in cash') }}</div>
                        </div>
                    </div>
                    <div class="col-md-6 col-xl-3">
                        <div class="border rounded p-3 h-100">
                            <div class="text-muted small">{{ __('Captain Collected') }}</div>
                            <div class="fs-4 fw-bold mb-0">{!! $money($settlement['driver_collected']) !!}</div>
                            <div class="small text-muted">{{ __('Cash collected from the passenger, plus the wallet amount transferred to the captain.') }}</div>
                            @if($settlement['driver_earnings'] !== null)
                                <div class="small mt-2">
                                    <strong>{{ __('Captain earnings after Sarea profit') }}:</strong>
                                    {!! $money($settlement['driver_earnings']) !!}
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100">
                            <div class="text-muted small">{{ __('Wallet before payment') }}</div>
                            <div class="fs-4 fw-bold mb-0">{!! $money($settlement['user_wallet_before']) !!}</div>
                            <div class="small text-muted">{{ __('Passenger wallet balance before paying for this ride') }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100">
                            <div class="text-muted small">{{ __('Wallet after payment') }}</div>
                            <div class="fs-4 fw-bold mb-0">{!! $money($settlement['user_wallet_after']) !!}</div>
                            <div class="small text-muted">{{ __('Passenger wallet balance after paying for this ride') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Captain Tracking Details -->
        @php
            $ignoredOffersWithLocation = ($ride->offers ?? collect())->filter(
                fn ($offer) => $offer->response === \App\Models\RideOffer::RESPONSE_IGNORED && $offer->hasDriverLocation()
            );
        @endphp
        @if($ride->driver_accept_lat || $ride->driver_arrived_lat || $ride->driver_cancel_lat || $ignoredOffersWithLocation->isNotEmpty())
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fa fa-map-marked-alt"></i> {{ __('Captain Tracking') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @if($ride->driver_accept_lat && $ride->driver_accept_lng)
                            <div class="col-md-6 mb-3">
                                <strong><i class="fa fa-play-circle text-warning"></i> {{ __('Accept Location') }}</strong>
                                <div class="small text-muted">
                                    {{ $ride->driver_accept_lat }}, {{ $ride->driver_accept_lng }}
                                    @if($ride->accepted_at)
                                        <br><i class="fa fa-clock"></i> {{ $ride->accepted_at->format('M d, Y h:i A') }}
                                    @endif
                                </div>
                                <a href="https://www.google.com/maps?q={{ $ride->driver_accept_lat }},{{ $ride->driver_accept_lng }}" target="_blank" class="small">
                                    <i class="fa fa-external-link-alt"></i> {{ __('Open in Google Maps') }}
                                </a>
                            </div>
                        @endif
                        @if($ride->driver_arrived_lat && $ride->driver_arrived_lng)
                            <div class="col-md-6 mb-3">
                                <strong><i class="fa fa-flag text-purple"></i> {{ __('Arrival at Pickup') }}</strong>
                                <div class="small text-muted">
                                    {{ $ride->driver_arrived_lat }}, {{ $ride->driver_arrived_lng }}
                                    @if($ride->arrived_at)
                                        <br><i class="fa fa-clock"></i> {{ $ride->arrived_at->format('M d, Y h:i A') }}
                                    @endif
                                </div>
                                <a href="https://www.google.com/maps?q={{ $ride->driver_arrived_lat }},{{ $ride->driver_arrived_lng }}" target="_blank" class="small">
                                    <i class="fa fa-external-link-alt"></i> {{ __('Open in Google Maps') }}
                                </a>
                            </div>
                        @endif
                        @if($ride->driver_cancel_lat && $ride->driver_cancel_lng)
                            <div class="col-md-6 mb-3">
                                <strong><i class="fa fa-times-circle text-danger"></i> {{ __('Driver Cancel Location') }}</strong>
                                <div class="small text-muted">
                                    {{ $ride->driver_cancel_lat }}, {{ $ride->driver_cancel_lng }}
                                    @if($ride->driverCancelledBy)
                                        <br><i class="fa fa-user"></i> {{ $ride->driverCancelledBy->name }}
                                    @endif
                                    @if($ride->driver_cancelled_at)
                                        <br><i class="fa fa-clock"></i> {{ $ride->driver_cancelled_at->format('M d, Y h:i A') }}
                                    @endif
                                    <br>
                                    @if($ride->accepted_at)
                                        <span class="badge bg-danger">{{ __('Cancelled after accepting') }}</span>
                                    @else
                                        <span class="badge bg-secondary">{{ __('Rejected before accepting') }}</span>
                                    @endif
                                </div>
                                <a href="https://www.google.com/maps?q={{ $ride->driver_cancel_lat }},{{ $ride->driver_cancel_lng }}" target="_blank" class="small">
                                    <i class="fa fa-external-link-alt"></i> {{ __('Open in Google Maps') }}
                                </a>
                            </div>
                        @endif
                        @foreach($ignoredOffersWithLocation as $ignoredOffer)
                            <div class="col-md-6 mb-3">
                                <strong><i class="fa fa-clock text-warning"></i> {{ __('Driver Ignore Location') }}</strong>
                                <div class="small text-muted">
                                    {{ $ignoredOffer->driver_lat }}, {{ $ignoredOffer->driver_lng }}
                                    @if($ignoredOffer->driver)
                                        <br><i class="fa fa-user"></i> {{ $ignoredOffer->driver->name }}
                                    @endif
                                    @if($ignoredOffer->responded_at)
                                        <br><i class="fa fa-clock"></i> {{ $ignoredOffer->responded_at->format('M d, Y h:i A') }}
                                    @endif
                                    <br>
                                    <span class="badge bg-secondary">{{ __('Ignored the request') }}</span>
                                </div>
                                <a href="https://www.google.com/maps?q={{ $ignoredOffer->driver_lat }},{{ $ignoredOffer->driver_lng }}" target="_blank" class="small">
                                    <i class="fa fa-external-link-alt"></i> {{ __('Open in Google Maps') }}
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <!-- Captain Offer History (who saw this ride and what did they do) -->
        @php
            $offers = $ride->offers ?? collect();
            $acceptedOffer = $offers->firstWhere('response', \App\Models\RideOffer::RESPONSE_ACCEPTED);
            $totalOffers = $offers->count();
        @endphp
        @if($totalOffers > 0 || $ride->cancelled_before_accept)
            <div class="card mt-3">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="mb-0">
                        <i class="fa fa-users"></i> {{ __('Captain Offer History') }}
                        <span class="badge bg-primary ms-2">{{ $totalOffers }}</span>
                    </h5>
                    <div>
                        @if($ride->cancelled_before_accept)
                            <span class="badge bg-dark">
                                <i class="fa fa-ban"></i> {{ __('Passenger cancelled before any captain accepted') }}
                            </span>
                        @endif
                        @if($acceptedOffer && $acceptedOffer->response_seconds !== null)
                            <span class="badge bg-success">
                                <i class="fa fa-check"></i>
                                {{ __('Accepted after :n sec', ['n' => $acceptedOffer->response_seconds]) }}
                            </span>
                        @endif
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($totalOffers === 0)
                        <div class="p-3 text-muted small">
                            <i class="fa fa-info-circle"></i>
                            {{ __('No offer records for this ride (likely created before offer tracking was enabled).') }}
                        </div>
                    @else
                        @php
                            // Group by driver so the admin can see, at a glance, how many
                            // times this ride was offered to each captain in total.
                            $offersByDriver = $offers->groupBy('driver_id');
                        @endphp
                        @if($offersByDriver->count() > 1)
                            <div class="p-3 border-bottom bg-light">
                                <strong class="d-block mb-2 small text-uppercase text-muted">
                                    {{ __('Times offered per captain') }}
                                </strong>
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach($offersByDriver as $driverId => $driverOffers)
                                        @php
                                            $driverName = $driverOffers->first()->driver->name ?? ('#' . $driverId);
                                            $wasAccepted = $driverOffers->contains('response', \App\Models\RideOffer::RESPONSE_ACCEPTED);
                                        @endphp
                                        <span class="badge {{ $wasAccepted ? 'bg-success' : 'bg-secondary' }} px-2 py-2">
                                            {{ $driverName }} &times; {{ $driverOffers->count() }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        <table class="table mb-0 table-sm align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('Captain') }}</th>
                                    <th>{{ __('Offered At') }}</th>
                                    <th>{{ __('Responded At') }}</th>
                                    <th>{{ __('Response Time') }}</th>
                                    <th>{{ __('Result') }}</th>
                                    <th>{{ __('Location') }}</th>
                                    <th>{{ __('Attempt') }}</th>
                                    <th>{{ __('Note') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($offers as $index => $offer)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            @if($offer->driver)
                                                <a href="{{ route('drivers.show', $offer->driver) }}">
                                                    {{ $offer->driver->name }}
                                                </a>
                                                @if($offer->driver->phone)
                                                    <br><small class="text-muted">{{ $offer->driver->phone }}</small>
                                                @endif
                                            @else
                                                <span class="text-muted">#{{ $offer->driver_id }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <small>{{ optional($offer->offered_at)->format('M d, Y h:i:s A') }}</small>
                                        </td>
                                        <td>
                                            <small>{{ optional($offer->responded_at)->format('M d, Y h:i:s A') ?? '-' }}</small>
                                        </td>
                                        <td>
                                            @if($offer->response_seconds !== null)
                                                <span class="badge bg-light text-dark">
                                                    {{ $offer->response_seconds }}s
                                                </span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>{!! $offer->responseBadgeHtml() !!}</td>
                                        <td>
                                            @if($offer->hasDriverLocation())
                                                <a href="https://www.google.com/maps?q={{ $offer->driver_lat }},{{ $offer->driver_lng }}" target="_blank" class="small">
                                                    <i class="fa fa-map-marker-alt"></i> {{ number_format($offer->driver_lat, 5) }}, {{ number_format($offer->driver_lng, 5) }}
                                                </a>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td><small>#{{ $offer->attempt }}</small></td>
                                        <td><small class="text-muted">{{ $offer->note }}</small></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        @endif

        <!-- Ride Chat History -->
        @if($ride->user_id && $ride->driver_id)
            <div class="card mt-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fa fa-comments"></i> {{ __('Ride Chat') }}</h5>
                    <span class="badge bg-secondary">{{ $rideChatMessages->count() }} {{ __('messages') }}</span>
                </div>
                <div class="card-body p-0">
                    @if($rideChatMessages->isEmpty())
                        <div class="p-4 text-center text-muted">
                            <i class="fa fa-comment-slash fa-2x mb-2"></i>
                            <p class="mb-0">{{ __('No chat messages for this ride.') }}</p>
                        </div>
                    @else
                        <div class="list-group list-group-flush" style="max-height: 420px; overflow-y: auto;">
                            @foreach($rideChatMessages as $chatMessage)
                                @php
                                    $isUserMessage = $chatMessage->sender_type === 'user';
                                    $senderName = $isUserMessage
                                        ? ($ride->user->name ?? __('User'))
                                        : ($ride->driver->name ?? __('Driver'));
                                @endphp
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-start gap-3">
                                        <div>
                                            <strong class="{{ $isUserMessage ? 'text-primary' : 'text-success' }}">
                                                {{ $senderName }}
                                            </strong>
                                            <span class="badge bg-light text-dark ms-1">
                                                {{ $isUserMessage ? __('User') : __('Driver') }}
                                            </span>
                                            <div class="mt-1">{{ $chatMessage->message }}</div>
                                        </div>
                                        <small class="text-muted text-nowrap">
                                            {{ $chatMessage->created_at->format('M d, Y h:i A') }}
                                        </small>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <!-- Trip Timeline -->
        @if($ride->accepted_at || $ride->arrived_at || $ride->trip_started_at || $ride->completed_at)
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fa fa-clock"></i> {{ __('Trip Timeline') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @if($ride->accepted_at)
                            <div class="col-md-6 mb-3">
                                <div class="d-flex align-items-center">
                                    <i class="fa fa-check-circle text-success me-2"></i>
                                    <div>
                                        <strong>{{ __('Ride Accepted') }}</strong>
                                        <div class="small text-muted">{{ $ride->accepted_at->format('M d, Y h:i A') }}</div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if($ride->arrived_at)
                            <div class="col-md-6 mb-3">
                                <div class="d-flex align-items-center">
                                    <i class="fa fa-map-marker-alt text-info me-2"></i>
                                    <div>
                                        <strong>{{ __('Driver Arrived') }}</strong>
                                        <div class="small text-muted">{{ $ride->arrived_at->format('M d, Y h:i A') }}</div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if($ride->trip_started_at)
                            <div class="col-md-6 mb-3">
                                <div class="d-flex align-items-center">
                                    <i class="fa fa-play-circle text-primary me-2"></i>
                                    <div>
                                        <strong>{{ __('Trip Started') }}</strong>
                                        <div class="small text-muted">{{ $ride->trip_started_at->format('M d, Y h:i A') }}</div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if($ride->completed_at)
                            <div class="col-md-6 mb-3">
                                <div class="d-flex align-items-center">
                                    <i class="fa fa-flag-checkered text-success me-2"></i>
                                    <div>
                                        <strong>{{ __('Trip Completed') }}</strong>
                                        <div class="small text-muted">{{ $ride->completed_at->format('M d, Y h:i A') }}</div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>

@push('scripts')
<!-- Laravel Echo + Pusher (Reverb) — loaded first -->
<script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js"></script>
<script src="https://cdn.jsdelivr.net/npm/pusher-js@8.4.0/dist/web/pusher.min.js"></script>
<script>
    (function () {
        try {
            const wsPort  = window.location.port ? parseInt(window.location.port) : (window.location.protocol === 'https:' ? 443 : 80);
            const useTLS  = window.location.protocol === 'https:';
            window.Echo = new Echo({
                broadcaster:       'reverb',
                key:               '{{ config("broadcasting.connections.reverb.key") }}',
                wsHost:            window.location.hostname,
                wsPort:            wsPort,
                wssPort:           wsPort,
                forceTLS:          useTLS,
                enabledTransports: ['ws', 'wss'],
            });
            console.log('📡 Laravel Echo (Reverb) initialized for ride tracking');
        } catch (e) {
            console.warn('Could not initialize Laravel Echo:', e);
        }
    })();
</script>

@include('rides.tracking-scripts')

@php
    // Built as plain PHP (not one giant inline @json expression) — a long
    // nested ternary/array literal inside a single @json(...) directive can
    // exceed Blade's directive-parsing regex limits and get silently
    // truncated, producing invalid JS/PHP on render.
    $driverAcceptLocationData = ($ride->driver_accept_lat && $ride->driver_accept_lng) ? [
        'lat' => (float) $ride->driver_accept_lat,
        'lng' => (float) $ride->driver_accept_lng,
        'recorded_at' => optional($ride->accepted_at)->toIso8601String(),
    ] : null;

    $driverArrivedLocationData = ($ride->driver_arrived_lat && $ride->driver_arrived_lng) ? [
        'lat' => (float) $ride->driver_arrived_lat,
        'lng' => (float) $ride->driver_arrived_lng,
        'recorded_at' => optional($ride->arrived_at)->toIso8601String(),
    ] : null;

    $driverCancelLocationData = ($ride->driver_cancel_lat && $ride->driver_cancel_lng) ? [
        'lat' => (float) $ride->driver_cancel_lat,
        'lng' => (float) $ride->driver_cancel_lng,
        'recorded_at' => optional($ride->driver_cancelled_at)->toIso8601String(),
        'cancelled_after_accept' => $ride->accepted_at !== null,
    ] : null;
@endphp
<script>
// Ride data from Laravel
const rideData = {
    id: {{ $ride->id }},
    status: '{{ $ride->status->value }}',
    pickup: {
        lat: {{ $ride->pickup_lat ?? 'null' }},
        lng: {{ $ride->pickup_lng ?? 'null' }},
        address: '{{ addslashes($ride->pickup_address ?? '') }}'
    },
    dropoff: {
        lat: {{ $ride->dropoff_lat ?? 'null' }},
        lng: {{ $ride->dropoff_lng ?? 'null' }},
        address: '{{ addslashes($ride->dropoff_address ?? '') }}'
    },
    routePoints: @json($ride->route_points ?? []),
    toPickupRoutePoints: @json($ride->to_pickup_route_points ?? []),
    driverId: {{ $ride->driver_id ?? 'null' }},
    firebaseRideId: '{{ $ride->firebase_ride_id ?? '' }}',
    driverAcceptLocation: @json($driverAcceptLocationData),
    driverArrivedLocation: @json($driverArrivedLocationData),
    driverCancelLocation: @json($driverCancelLocationData),
    driverIgnoreLocations: @json($ride->ignoreLocationPayload())
};

let rideTracker;

function initMap() {
    if (!rideData.pickup.lat || !rideData.pickup.lng) {
        console.log('No pickup coordinates available');
        document.getElementById('rideMap').innerHTML = '<div class="alert alert-warning">No location data available for this ride.</div>';
        return;
    }

    // Initialize the ride tracker
    rideTracker = new SimpleRideTracker(rideData, 'rideMap');
    rideTracker.init();
}

// Cleanup function
function cleanup() {
    if (rideTracker) {
        rideTracker.cleanup();
    }
}

// Auto-refresh ride data for active rides
if (['in_progress', 'accepted', 'waiting_user'].includes(rideData.status)) {
    setInterval(() => {
        if (rideTracker) {
            rideTracker.refreshRideData();
        }
    }, 30000); // Refresh every 30 seconds
}

// Initialize map when page loads
window.addEventListener('load', initMap);

// Cleanup when page unloads
window.addEventListener('beforeunload', cleanup);
</script>

<!-- Google Maps API -->
@if(config('services.google_maps.api_key') && config('services.google_maps.api_key') !== 'your_actual_api_key_here')
<script async defer
    src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.api_key') }}&callback=initMap&libraries=marker">
</script>
@else
<script>
    function initMap() {
        document.getElementById('rideMap').innerHTML = '<div class="alert alert-warning m-3"><strong>Configuration Required:</strong> Please set your Google Maps API key in the .env file.<br><small>Add: GOOGLE_MAPS_API_KEY=your_actual_api_key</small></div>';
    }
    window.addEventListener('load', initMap);
</script>
@endif

@endpush

@endsection
