@extends('layouts.app')

@php
    $currentPage = 'drivers';
@endphp

@section('title', __('Cars for ') . $driver->name)

@section('content')
<div class="container-fluid">
    <h1>{{ __('Cars for') }} {{ $driver->name }}</h1>

    <div class="mb-3">
        <a href="{{ route('drivers.show', $driver->id) }}" class="btn btn-secondary btn-sm me-1">
            <i class="fa fa-arrow-right"></i> {{ __('Back to Driver') }}
        </a>
        <a href="{{ route('driver-cars.create') }}?driver_id={{ $driver->id }}" class="btn btn-primary btn-sm">
            <i class="fa fa-plus"></i> {{ __('Add New Car') }}
        </a>
    </div>

    <div class="row">
        @forelse ($cars as $car)
            @php
                $carImage = $car->car_image_link ?? 'https://archive.org/download/placeholder-image/placeholder-image.jpg';
                $carLicense = $car->car_license_link ?? null;
            @endphp

            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <img src="{{ $carImage }}" class="card-img-top" alt="Car Image" style="height: 200px; object-fit: cover;">
                    <div class="card-body">
                        <h5 class="card-title">{{ __('Car Number:') }} {{ $car->car_number }}</h5>
                        <p class="card-text">
                            {{ __('Category:') }} {{ $car->carCategory->name ?? '-' }}<br>
                            {{ __('Type:') }} {{ $car->carType->type_name ?? '-' }}<br>
                            {{ __('Model:') }} {{ $car->carModel->name ?? '-' }}<br>
                            {{ __('Color:') }} {{ $car->car_color ?? '-' }}
                        </p>

                        <!-- Action Buttons -->
                        <div class="d-flex flex-wrap gap-1">
                            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#carImageModal{{ $car->id }}">
                                {{ __('View Image') }} <i class="fa fa-image"></i>
                            </button>

                            @if ($carLicense)
                                <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#carLicenseModal{{ $car->id }}">
                                    {{ __('View License') }} <i class="fa fa-id-card"></i>
                                </button>
                            @endif

                            <a href="{{ route('driver-cars.edit', $car->id) }}" class="btn btn-warning btn-sm">
                                {{ __('Edit') }} <i class="fa fa-edit"></i>
                            </a>

                            <a href="{{ route('driver-cars.show', $car->id) }}" class="btn btn-success btn-sm">
                                {{ __('Details') }} <i class="fa fa-eye"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal: Car Image -->
            <div class="modal fade" id="carImageModal{{ $car->id }}" tabindex="-1" aria-labelledby="carImageModalLabel{{ $car->id }}" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="carImageModalLabel{{ $car->id }}">{{ __('Car Image for') }} {{ $car->car_number }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                        </div>
                        <div class="modal-body text-center">
                            <img src="{{ $carImage }}" class="img-fluid" alt="Car Image">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal: Car License -->
            @if ($carLicense)
                <div class="modal fade" id="carLicenseModal{{ $car->id }}" tabindex="-1" aria-labelledby="carLicenseModalLabel{{ $car->id }}" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="carLicenseModalLabel{{ $car->id }}">{{ __('Car License for') }} {{ $car->car_number }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                            </div>
                            <div class="modal-body text-center">
                                <img src="{{ $carLicense }}" class="img-fluid" alt="Car License">
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @empty
            <div class="col-12">
                <div class="alert alert-info">{{ __('No cars found for this driver.') }}</div>
            </div>
        @endforelse
    </div>
</div>
@endsection
