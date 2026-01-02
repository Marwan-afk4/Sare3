@extends('layouts.app')
@php
    $currentPage = 'cancellation-policies';
@endphp
@section('title', $cancellationPolicy->name)
@section('content')
    <div class="container-fluid">
        <h1>{{ $cancellationPolicy->name }}</h1>
        <div class="mb-3">
            <a href="{{ route('cancellation-policies.index') }}" class="btn btn-secondary btn-sm me-1"> <i
                    class="fa fa-arrow-right"></i> {{ __('Back to') }} {{ __('Cancellation Policies') }}</a>
            {{-- <a href='{{ route('cancellation-policies.edit', $cancellationPolicy) }}'
                class="btn btn-warning btn-sm me-1">{{ __('Edit') }} <i class="fa fa-edit"></i></a> --}}
        </div>
        <div class="card">
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <strong>{{ __('Id') }}:</strong> {{ $cancellationPolicy->id }}
                    </li>
                    <li class="list-group-item">
                        <strong>{{ __('Name') }}:</strong> {{ $cancellationPolicy->name }}
                    </li>
                    <li class="list-group-item">
                        <strong>{{ __('User Type') }}:</strong> {{ $cancellationPolicy->user_type === 'rider' ? __('Rider') : __('Driver') }}
                    </li>
                    <li class="list-group-item">
                        <strong>{{ __('Penalty Amount') }}:</strong> {{ $cancellationPolicy->penalty_amount ?? '-' }}
                    </li>
                    <li class="list-group-item">
                        <strong>{{ __('Penalty Percent') }}:</strong> {{ $cancellationPolicy->penalty_percent ?? '-' }}
                    </li>
                    <li class="list-group-item">
                        <strong>{{ __('Description') }}:</strong> {{ $cancellationPolicy->description }}
                    </li>
                    <li class="list-group-item">
                        <strong>{{ __('Status') }}:</strong>
                        {!! $cancellationPolicy->status->badge() !!}
                    </li>
                    <li class="list-group-item">
                        <strong>{{ __('Min Minutes') }}:</strong> {{ $cancellationPolicy->min_minutes }}
                    </li>
                    <li class="list-group-item">
                        <strong>{{ __('Max Minutes') }}:</strong> {{ $cancellationPolicy->max_minutes }}
                    </li>
                    <li class="list-group-item">
                        <strong>{{ __('Created At') }}:</strong>
                        {{ $cancellationPolicy->created_at->diffForHumans() ?? '-' }}
                    </li>
                    <li class="list-group-item">
                        <strong>{{ __('Updated At') }}:</strong>
                        {{ $cancellationPolicy->updated_at->diffForHumans() ?? '-' }}
                    </li>
                </ul>
            </div>
            <div class="card-footer">
                <a href='{{ route('cancellation-policies.edit', $cancellationPolicy) }}'
                    class="btn btn-warning btn-sm me-1">{{ __('Edit') }} <i class="fa fa-edit"></i></a>
            </div>
        </div>
    </div>
@endsection
