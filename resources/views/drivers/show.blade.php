@extends('layouts.app')
@php
    $currentPage = 'drivers';
@endphp
@section('title', $driver->name)
@section('content')
    <div class="container-fluid">
        <h1>{{ $driver->name }}</h1>
        <div class="mb-3">
            <a wire:navigate href="{{ route('drivers.index') }}" class="btn btn-secondary btn-sm me-1">
                <i class="fa fa-arrow-right"></i> {{ __('Back to') }} {{ __('Drivers') }}
            </a>
            {{-- <a wire:navigate href='{{ route('drivers.edit', $driver) }}' class="btn btn-warning btn-sm me-1">
                {{ __('Edit') }} <i class="fa fa-edit"></i>
            </a> --}}
        </div>

        <div class="card">
            <div class="card-body">
                <div class="row">
                    {{-- Profile Image --}}
                    <div class="col-md-2 text-center">
                        @if ($driver->image)
                            <img src="{{ $driver->image_link ?? 'https://th.bing.com/th/id/OIP.hGSCbXlcOjL_9mmzerqAbQHaHa?rs=1&pid=ImgDetMain' }}"
                                alt="{{ $driver->name }}"
                                class="img-thumbnail mb-3" style="max-width: 100%;">
                        @else
                            <img src="https://th.bing.com/th/id/OIP.hGSCbXlcOjL_9mmzerqAbQHaHa?rs=1&pid=ImgDetMain"
                                alt="No Image" class="img-thumbnail mb-3">
                        @endif
                    </div>

                    {{-- Info --}}
                    <div class="col-md-9">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item"><strong>{{ __('Id') }}:</strong> {{ $driver->id }}</li>
                            <li class="list-group-item"><strong>{{ __('Name') }}:</strong> {{ $driver->name }}</li>
                            <li class="list-group-item"><strong>{{ __('Email') }}:</strong> {{ $driver->email }}</li>
                            <li class="list-group-item"><strong>{{ __('Phone') }}:</strong> {{ $driver->phone }}</li>
                            <li class="list-group-item"><strong>{{ __('Activity') }}:</strong>
                                <span class="badge badge-phoenix fs-10"
                                    style="background-color: #{{ $driver->activity->color() }}; color: #{{ $driver->activity->textColor() }};">
                                    <span class="badge-label m-1">{{ $driver->activity->label() ?? '-' }}</span>
                                </span>
                            </li>
                            <li class="list-group-item"><strong>{{ __('Wallet') }}:</strong> {{ $driver->wallet }}</li>
                            <li class="list-group-item"><strong>{{ __('Created At') }}:</strong>
                                {{ $driver->created_at?->diffForHumans() }}</li>
                            <li class="list-group-item"><strong>{{ __('Updated At') }}:</strong>
                                {{ $driver->updated_at?->diffForHumans() }}</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <a wire:navigate href='{{ route('drivers.edit', $driver) }}' class="btn btn-subtle-warning btn-sm me-1">{{ __("Edit") }} <i class="fa fa-edit"></i></a>
            </div>
        </div>
    </div>
@endsection
