@extends('layouts.app')
@php
    $currentPage = 'cancelation-rides';
@endphp
@section('title', $cancelationRide->name)
@section('content')
    <div class="container-fluid">
        <h1>{{ $cancelationRide->id }}</h1>
        <div class="mb-3">
            <a href="{{ route('cancelation-rides.index') }}" class="btn btn-secondary btn-sm me-1"> <i
                    class="fa fa-arrow-right"></i> {{ __('Back to') }} {{ __('Cancelation Rides') }}</a>
            {{-- <a href='{{ route('cancelation-rides.edit', $cancelationRide) }}' class="btn btn-warning btn-sm me-1">{{ __('Edit') }} <i class="fa fa-edit"></i></a> --}}
        </div>
        <div class="card">
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <strong>{{ __('Id') }}:</strong> {{ $cancelationRide->id }}
                    </li>
                    <li class="list-group-item">
                        <strong>{{ __('Ride') }}:</strong>
                        @if ($cancelationRide->ride)
                            <a
                                href="{{ route('rides.show', $cancelationRide->ride) }}">{{ $cancelationRide->ride?->id }}</a>
                        @endif
                    </li>
                    <li class="list-group-item">
                        <strong>{{ __('User') }}:</strong>
                        @if ($cancelationRide->user)
                            <a
                                href="{{ route('users.show', $cancelationRide->user) }}">{{ $cancelationRide->user?->name }}</a>
                        @endif
                    </li>
                    <li class="list-group-item">
                        <strong>{{ __('Driver') }}:</strong>
                        @if ($cancelationRide->driver)
                            <a
                                href="{{ route('drivers.show', $cancelationRide->driver) }}">{{ $cancelationRide->driver?->name ?? '-' }}</a>
                        @endif
                    </li>
                    <li class="list-group-item">
                        <strong>{{ __('Cancelation Policy') }}:</strong>
                        @if ($cancelationRide->cancelationPolicy)
                            <a
                                href="{{ route('cancellation-policies.show', $cancelationRide->cancelationPolicy) }}">{{ $cancelationRide->cancelationPolicy?->name }}</a>
                        @endif
                    </li>
                    <li class="list-group-item">
                        <strong>{{ __('Canceled By') }}:</strong>
                        {{ $cancelationRide->canceled_by ? __('Driver') : __('User') }}
                    </li>
                    <li class="list-group-item">
                        <strong>{{ __('Penalty Applied') }}:</strong>
                        {{ $cancelationRide->penalty_applied ? __('Yes') : __('No') }}
                    </li>
                    <li class="list-group-item">
                        <strong>{{ __('Penalty Amount') }}:</strong> {{ $cancelationRide->penalty_amount }}
                    </li>
                    <li class="list-group-item">
                        <strong>{{ __('Reason') }}:</strong> {{ $cancelationRide->reason }}
                    </li>
                    <li class="list-group-item">
                        <strong>{{ __('Canceled At') }}:</strong>
                        {{ $cancelationRide->canceled_at->diffForHumans() ?? '-' }}
                    </li>
                    <li class="list-group-item">
                        <strong>{{ __('Created At') }}:</strong>
                        {{ $cancelationRide->created_at->diffForHumans() ?? '-' }}
                    </li>
                    <li class="list-group-item">
                        <strong>{{ __('Updated At') }}:</strong>
                        {{ $cancelationRide->updated_at->diffForHumans() ?? '-' }}
                    </li>
                </ul>
            </div>
        </div>
        <div class="mt-3">
            {{-- <form method='POST' action='{{ route('cancelation-rides.destroy', $cancelationRide) }}' onsubmit='return confirm("Are you sure you want to delete this item?")'>
			<input type='hidden' name='_method' value='DELETE'>
			<button type='submit' class="btn btn-square btn-danger">{{ __('Delete') }}</button>
		</form> --}}
        </div>
    </div>
@endsection
