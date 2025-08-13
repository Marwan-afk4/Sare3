@extends('layouts.app')

@php
    $currentPage = 'drivers';
@endphp

@section('title', __('Cars for ') . $driver->name)

@section('content')
<div class="container-fluid">
    <h1>{{ __('Cars for') }} {{ $driver->name }}</h1>

    <a href="{{ route('drivers.show', $driver->id) }}" class="btn btn-secondary btn-sm mb-3">
        <i class="fa fa-arrow-right"></i> {{ __('Back to Driver') }}
    </a>

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

                        <!-- View Car Image -->
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#carImageModal{{ $car->id }}">
                            {{ __('View Full Car Image') }} <i class="fa fa-image"></i>
                        </button>

                        <!-- View License if available -->
                        @if ($carLicense)
                            <button type="button" class="btn btn-info btn-sm mt-1" data-bs-toggle="modal" data-bs-target="#carLicenseModal{{ $car->id }}">
                                {{ __('View Car License') }} <i class="fa fa-id-card"></i>
                            </button>
                        @endif
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
