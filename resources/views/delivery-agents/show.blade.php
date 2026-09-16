@extends('layouts.app')
@php
    $currentPage = 'delivery-agents';
    $vehicle = $delivery_agent->riderVehicle;
    $st = $delivery_agent->status->value ?? $delivery_agent->status;
@endphp
@section('title', __('Delivery Agent'))
@section('content')
<div class="container-fluid">
    <div class="mb-3">
        <a href="{{ route('delivery-agents.index') }}" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-right"></i> {{ __('Back') }}</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row">
        <div class="col-md-5">
            <div class="main-card mb-3 card">
                <div class="card-body">
                    <h5 class="mb-3">{{ $delivery_agent->name ?? __('Delivery Agent') }} #{{ $delivery_agent->id }}</h5>
                    <table class="table table-sm">
                        <tr><th>{{ __('Phone') }}</th><td>{{ $delivery_agent->phone }}</td></tr>
                        <tr><th>{{ __('Email') }}</th><td>{{ $delivery_agent->email ?? '-' }}</td></tr>
                        <tr><th>{{ __('Status') }}</th><td>
                            <span class="badge bg-{{ $st === 'approved' ? 'success' : ($st === 'rejected' ? 'danger' : 'warning') }}">{{ __(ucfirst($st)) }}</span>
                        </td></tr>
                        <tr><th>{{ __('Wallet') }}</th><td>{{ number_format((float) $delivery_agent->wallet, 2) }}</td></tr>
                        <tr><th>{{ __('Zone') }}</th><td>{{ $delivery_agent->zone->name ?? '-' }}</td></tr>
                        <tr><th>{{ __('Vehicle') }}</th><td>{{ $vehicle ? __($vehicle->type->label()) : '-' }}</td></tr>
                        @if($delivery_agent->rejected_reason)
                        <tr><th>{{ __('Rejected reason') }}</th><td>{{ $delivery_agent->rejected_reason }}</td></tr>
                        @endif
                    </table>

                    <form action="{{ route('delivery-agents.status', $delivery_agent) }}" method="POST" class="mt-3">
                        @csrf @method('PATCH')
                        <div class="mb-2">
                            <label class="form-label">{{ __('Set Status') }}</label>
                            <select name="status" class="form-select" id="statusSelect">
                                @foreach(['approved','pending','rejected'] as $s)
                                    <option value="{{ $s }}" @selected($st === $s)>{{ __(ucfirst($s)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">{{ __('Rejected reason (optional)') }}</label>
                            <input type="text" name="rejected_reason" class="form-control" value="{{ $delivery_agent->rejected_reason }}">
                        </div>
                        <button class="btn btn-primary btn-sm">{{ __('Save') }}</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-7">
            <div class="main-card mb-3 card">
                <div class="card-body">
                    <h6 class="mb-3">{{ __('Documents') }}</h6>
                    <div class="row">
                        @if($vehicle)
                            @foreach(['rider_image_link' => __('Rider photo'), 'identity_image_link' => __('Identity'), 'vehicle_image_link' => __('Vehicle'), 'license_image_link' => __('License')] as $attr => $label)
                                @if($vehicle->{$attr})
                                <div class="col-6 col-md-3 mb-3 text-center">
                                    <a href="{{ $vehicle->{$attr} }}" target="_blank">
                                        <img src="{{ $vehicle->{$attr} }}" class="img-fluid rounded border" style="height:110px;object-fit:cover;">
                                    </a>
                                    <small class="d-block text-muted mt-1">{{ $label }}</small>
                                </div>
                                @endif
                            @endforeach
                        @else
                            <p class="text-muted">{{ __('No vehicle documents uploaded.') }}</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="main-card mb-3 card">
                <div class="card-body">
                    <h6 class="mb-3">{{ __('Recent Deliveries') }}</h6>
                    <table class="table table-sm table-hover mb-0">
                        <tr>
                            <th>{{ __('Id') }}</th>
                            <th>{{ __('User') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Price') }}</th>
                            <th>{{ __('Date') }}</th>
                        </tr>
                        @forelse($deliveries as $d)
                        <tr>
                            <td><a href="{{ route('deliveries.show', $d) }}">#{{ $d->id }}</a></td>
                            <td>{{ $d->user->name ?? '-' }}</td>
                            <td>{{ __(ucfirst($d->status->value)) }}</td>
                            <td>{{ $d->calculated_final_price ?? $d->calculated_initial_price }}</td>
                            <td>{{ $d->created_at?->diffForHumans() }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted">{{ __('No deliveries yet.') }}</td></tr>
                        @endforelse
                    </table>
                    {{ $deliveries->links('pagination::custom') }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
