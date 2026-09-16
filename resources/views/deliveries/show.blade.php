@extends('layouts.app')
@php
    $currentPage = 'deliveries';
    $st = $delivery->status->value;
@endphp
@section('title', __('Delivery') . ' #' . $delivery->id)
@section('content')
<div class="container-fluid">
    <div class="mb-3">
        <a href="{{ route('deliveries.index') }}" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-right"></i> {{ __('Back') }}</a>
        <a href="{{ route('deliveries.track', $delivery) }}" class="btn btn-info btn-sm"><i class="fa fa-map-marker"></i> {{ __('Track') }}</a>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="main-card mb-3 card">
                <div class="card-body">
                    <h5 class="mb-3">{{ __('Delivery') }} #{{ $delivery->id }}
                        <span class="badge bg-{{ $st === 'finshed' ? 'success' : (in_array($st, ['rejected','cancelled']) ? 'danger' : 'warning') }}">{{ __(ucfirst($st)) }}</span>
                    </h5>
                    <table class="table table-sm">
                        <tr><th>{{ __('User') }}</th><td>{{ $delivery->user->name ?? '-' }} ({{ $delivery->user->phone ?? '-' }})</td></tr>
                        <tr><th>{{ __('Rider') }}</th><td>
                            @if($delivery->rider)
                                <a href="{{ route('delivery-agents.show', $delivery->rider) }}">{{ $delivery->rider->name }}</a> ({{ $delivery->rider->phone }})
                            @else - @endif
                        </td></tr>
                        <tr><th>{{ __('Vehicle') }}</th><td>{{ $delivery->vehicle_type ? $delivery->vehicle_type->label() : '-' }}</td></tr>
                        <tr><th>{{ __('Zone') }}</th><td>{{ $delivery->zone->name ?? '-' }}</td></tr>
                        <tr><th>{{ __('Pickup') }}</th><td>{{ $delivery->pickup_address ?? ($delivery->pickup_lat . ', ' . $delivery->pickup_lng) }}</td></tr>
                        <tr><th>{{ __('Dropoff') }}</th><td>{{ $delivery->dropoff_address ?? ($delivery->dropoff_lat . ', ' . $delivery->dropoff_lng) }}</td></tr>
                        <tr><th>{{ __('Estimated km') }}</th><td>{{ $delivery->estimated_km }}</td></tr>
                        <tr><th>{{ __('Distance (km)') }}</th><td>{{ $delivery->total_distance_in_km ?? '-' }}</td></tr>
                        <tr><th>{{ __('Duration (min)') }}</th><td>{{ $delivery->time_taken ?? '-' }}</td></tr>
                        <tr><th>{{ __('Initial price') }}</th><td>{{ $delivery->calculated_initial_price }}</td></tr>
                        <tr><th>{{ __('Final price') }}</th><td>{{ $delivery->calculated_final_price ?? '-' }}</td></tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="main-card mb-3 card">
                <div class="card-body">
                    <h6 class="mb-3">{{ __('Timeline') }}</h6>
                    <table class="table table-sm">
                        <tr><th>{{ __('Created') }}</th><td>{{ $delivery->created_at }}</td></tr>
                        <tr><th>{{ __('Accepted') }}</th><td>{{ $delivery->accepted_at ?? '-' }}</td></tr>
                        <tr><th>{{ __('Arrived') }}</th><td>{{ $delivery->arrived_at ?? '-' }}</td></tr>
                        <tr><th>{{ __('Completed') }}</th><td>{{ $delivery->completed_at ?? '-' }}</td></tr>
                    </table>
                </div>
            </div>

            <div class="main-card mb-3 card">
                <div class="card-body">
                    <h6 class="mb-3">{{ __('Offer History') }}</h6>
                    <table class="table table-sm mb-0">
                        <tr><th>{{ __('Rider') }}</th><th>{{ __('Response') }}</th><th>{{ __('Seconds') }}</th></tr>
                        @forelse($delivery->offers as $offer)
                        <tr>
                            <td>{{ $offer->rider->name ?? ('#' . $offer->rider_id) }}</td>
                            <td><span class="badge bg-{{ $offer->responseColor() }}">{{ $offer->responseLabel() }}</span></td>
                            <td>{{ $offer->response_seconds ?? '-' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center text-muted">{{ __('No offers recorded.') }}</td></tr>
                        @endforelse
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
