@extends('layouts.app')
@php
    $currentPage = 'car-models';
@endphp
@section('title', $carModel->name)
@section('content')
    <div class="container-fluid">
        <h1>{{ $carModel->name }}</h1>
        <div class="mb-3">
            <a href="{{ route('car-models.index') }}" class="btn btn-secondary btn-sm me-1"> <i class="fa fa-arrow-right"></i>
                {{ __('Back to') }} {{ __('Car Models') }}</a>
            {{-- <a href='{{ route('car-models.edit', $carModel) }}' class="btn btn-warning btn-sm me-1">{{ __('Edit') }} <i
                    class="fa fa-edit"></i></a> --}}
        </div>
        <div class="card">
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <strong>{{ __('Id') }}:</strong> {{ $carModel->id }}
                    </li>
                    <li class="list-group-item">
                        <strong>{{ __('Name') }}:</strong> {{ $carModel->name }}
                    </li>
                    <li class="list-group-item">
                        <strong>{{ __('Created At') }}:</strong> {{ $carModel->created_at?->diffForHumans() ?? '-' }}
                    </li>
                    <li class="list-group-item">
                        <strong>{{ __('Updated At') }}:</strong> {{ $carModel->updated_at?->diffForHumans() ?? '-' }}
                    </li>
                </ul>
            </div>
            <div>
                <div class="card-footer">
                    <a href='{{ route('car-models.edit', $carModel) }}' class="btn btn-warning btn-sm me-1">{{ __('Edit') }} <i
                    class="fa fa-edit"></i></a>
                </div>
            </div>
        </div>
    </div>
@endsection
