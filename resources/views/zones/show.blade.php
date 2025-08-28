@extends('layouts.app')
@php
	$currentPage = 'zones';
@endphp
@section('title', $zone->name)
@section('content')
<div class="container-fluid">
    <h1>{{ $zone->name }}</h1>
    <div class="mb-3">
        <a href="{{ route('zones.index') }}" class="btn btn-secondary btn-sm me-1"> <i class="fa fa-arrow-right"></i>
            {{ __('Back to') }} {{ __('Zones') }}</a>
        {{-- <a href='{{ route('zones.edit', $zone) }}' class="btn btn-warning btn-sm me-1">{{ __('Edit') }} <i class="fa fa-edit"></i></a> --}}
    </div>
    <div class="card">
        <div class="card-body">
            <ul class="list-group list-group-flush">
                <li class="list-group-item">
                    <strong>{{ __('Id') }}:</strong> {{ $zone->id }}
                </li>
                <li class="list-group-item">
                    <strong>{{ __('Name') }}:</strong> {{ $zone->name }}
                </li>
                <li class="list-group-item">
                    <strong>{{ __('From Lat') }}:</strong> {{ $zone->from_lat }}
                </li>
                <li class="list-group-item">
                    <strong>{{ __('From Lng') }}:</strong> {{ $zone->from_lng }}
                </li>
                <li class="list-group-item">
                    <strong>{{ __('To Lat') }}:</strong> {{ $zone->to_lat }}
                </li>
                <li class="list-group-item">
                    <strong>{{ __('To Lng') }}:</strong> {{ $zone->to_lng }}
                </li>
                <li class="list-group-item">
                    <strong>{{ __('Created At') }}:</strong> {{ $zone->created_at?->diffForHumans() ?? '-' }}
                </li>
                <li class="list-group-item">
                    <strong>{{ __('Updated At') }}:</strong> {{ $zone->updated_at?->diffForHumans() ?? '-' }}
                </li>
            </ul>
        </div>
        <div class="card-footer">
            <a href='{{ route('zones.edit', $zone) }}' class="btn btn-warning btn-sm me-1">
                {{ __('Edit') }} <i class="fa fa-edit"></i>
            </a>
        </div>
    </div>
    <br>
    {{-- Car Categories --}}
    <livewire:zone-car-categories :zone="$zone" />
</div>
@endsection
