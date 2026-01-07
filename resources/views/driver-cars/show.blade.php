@extends('layouts.app')
@php
    $currentPage = 'driver-cars';
@endphp
@section('title', __('Driver Car Details'))
@section('content')
<div class="container">
    <h1>{{ __('Driver Car Details') }}</h1>
    <div class="mb-3">
        <a href="{{ route('driver-cars.index') }}" class="btn btn-secondary btn-sm me-1">
            <i class="fa fa-arrow-right"></i> {{ __('Back to') }} {{ __('Driver Cars') }}
        </a>
        <a href="{{ route('driver-cars.edit', $driverCar) }}" class="btn btn-warning btn-sm me-1">
            {{ __('Edit') }} <i class="fa fa-edit"></i>
        </a>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">{{ __('Car Information') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item">
                                    <strong>{{ __('ID') }}:</strong> {{ $driverCar->id }}
                                </li>
                                <li class="list-group-item">
                                    <strong>{{ __('Car Number') }}:</strong>
                                    <span class="badge bg-primary">{{ $driverCar->car_number }}</span>
                                </li>
                                <li class="list-group-item">
                                    <strong>{{ __('Car Color') }}:</strong>
                                    <span class="badge" style="background-color: {{ strtolower($driverCar->car_color) }}; color: white;">
                                        {{ $driverCar->car_color }}
                                    </span>
                                </li>
                                <li class="list-group-item">
                                    <strong>{{ __('Category') }}:</strong> {{ $driverCar->carCategory->name ?? 'N/A' }}
                                </li>
                                <li class="list-group-item">
                                    <strong>{{ __('Model') }}:</strong> {{ $driverCar->carModel->name ?? 'N/A' }}
                                </li>
                                <li class="list-group-item">
                                    <strong>{{ __('Type') }}:</strong> {{ $driverCar->carType->type_name ?? 'N/A' }}
                                    @if($driverCar->carType && ($driverCar->carType->year_from || $driverCar->carType->year_to))
                                        ({{ $driverCar->carType->year_range }})
                                    @endif
                                </li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item">
                                    <strong>{{ __('Driver') }}:</strong>
                                    @if($driverCar->driver)
                                        <a href="{{ route('drivers.show', $driverCar->driver) }}" class="text-decoration-none">
                                            {{ $driverCar->driver->name }}
                                        </a>
                                    @else
                                        N/A
                                    @endif
                                </li>
                                <li class="list-group-item">
                                    <strong>{{ __('Driver Phone') }}:</strong> {{ $driverCar->driver->phone ?? 'N/A' }}
                                </li>
                                <li class="list-group-item">
                                    <strong>{{ __('Created At') }}:</strong> {{ $driverCar->created_at?->format('M d, Y H:i') ?? '-' }}
                                </li>
                                <li class="list-group-item">
                                    <strong>{{ __('Updated At') }}:</strong> {{ $driverCar->updated_at?->format('M d, Y H:i') ?? '-' }}
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Car Image -->
            @if($driverCar->car_image_link)
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="card-title mb-0">{{ __('Car Image') }}</h6>
                    </div>
                    <div class="card-body text-center">
                        <img src="{{ $driverCar->car_image_link }}" alt="Car Image"
                             class="img-fluid rounded" style="max-height: 300px;">
                        <div class="mt-2">
                            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#carImageModal">
                                {{ __('View Full Size') }} <i class="fa fa-expand"></i>
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Car License -->
            @if($driverCar->car_license_link)
                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title mb-0">{{ __('Car License') }}</h6>
                    </div>
                    <div class="card-body text-center">
                        <img src="{{ $driverCar->car_license_link }}" alt="Car License"
                             class="img-fluid rounded" style="max-height: 300px;">
                        <div class="mt-2">
                            <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#carLicenseModal">
                                {{ __('View Full Size') }} <i class="fa fa-expand"></i>
                            </button>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Car Image Modal -->
@if($driverCar->car_image_link)
<div class="modal fade" id="carImageModal" tabindex="-1" aria-labelledby="carImageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="carImageModalLabel">{{ __('Car Image') }} - {{ $driverCar->car_number }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
            </div>
            <div class="modal-body text-center">
                <img src="{{ $driverCar->car_image_link }}" class="img-fluid" alt="Car Image">
            </div>
        </div>
    </div>
</div>
@endif

<!-- Car License Modal -->
@if($driverCar->car_license_link)
<div class="modal fade" id="carLicenseModal" tabindex="-1" aria-labelledby="carLicenseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="carLicenseModalLabel">{{ __('Car License') }} - {{ $driverCar->car_number }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
            </div>
            <div class="modal-body text-center">
                <img src="{{ $driverCar->car_license_link }}" class="img-fluid" alt="Car License">
            </div>
        </div>
    </div>
</div>
@endif
@endsection
