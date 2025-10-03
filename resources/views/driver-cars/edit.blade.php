@extends('layouts.app')
@php
    $currentPage = 'driver-cars';
@endphp
@section('title', __('Edit Driver Car'))
@section('content')
<div class="container">
    <h1>{{ __('Edit Driver Car') }}</h1>
    <div class="mb-3">
        <a href="{{ route('driver-cars.index') }}" class="btn btn-secondary btn-sm me-1">
            <i class="fa fa-arrow-right"></i> {{ __('Back to') }} {{ __('Driver Cars') }}
        </a>
        <a href="{{ route('driver-cars.show', $driverCar) }}" class="btn btn-primary btn-sm me-1">
            {{ __('Details') }} <i class="fa fa-eye"></i>
        </a>
    </div>
    
    <div class="main-card mb-3 card">
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data" action="{{ route('driver-cars.update', $driverCar->id) }}" novalidate>
                @csrf
                @method('PUT')
                
                <div class="row">
                    <div class="col-md-6">
                        <x-form-select
                            name="driver_id"
                            type="select"
                            label="{{ __('Driver') }}"
                            :selected="$driverCar->driver_id ?? ''"
                            :options="$drivers"
                            required
                        />
                    </div>
                    <div class="col-md-6">
                        <x-form-input
                            name="car_number"
                            type="text"
                            label="{{ __('Car Number') }}"
                            :value="$driverCar->car_number ?? ''"
                            required
                        />
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <x-form-select
                            name="car_categories_id"
                            type="select"
                            label="{{ __('Car Category') }}"
                            :selected="$driverCar->car_categories_id ?? ''"
                            :options="$carCategories"
                            required
                        />
                    </div>
                    <div class="col-md-4">
                        <x-form-select
                            name="car_model_id"
                            type="select"
                            label="{{ __('Car Model') }}"
                            :selected="$driverCar->car_model_id ?? ''"
                            :options="$carModels"
                            required
                        />
                    </div>
                    <div class="col-md-4">
                        <x-form-select
                            name="car_type_id"
                            type="select"
                            label="{{ __('Car Type') }}"
                            :selected="$driverCar->car_type_id ?? ''"
                            :options="$carTypes"
                            required
                        />
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <x-form-input
                            name="car_color"
                            type="text"
                            label="{{ __('Car Color') }}"
                            :value="$driverCar->car_color ?? ''"
                            required
                        />
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="car_image" class="form-label">{{ __('Car Image') }}</label>
                            <input type="file" class="form-control @error('car_image') is-invalid @enderror" 
                                   id="car_image" name="car_image" accept="image/*">
                            @error('car_image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @if($driverCar->car_image_link)
                                <div class="mt-2">
                                    <small class="text-muted">{{ __('Current image:') }}</small><br>
                                    <img src="{{ $driverCar->car_image_link }}" alt="Current car image" 
                                         class="img-thumbnail" style="max-width: 200px;">
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="car_license" class="form-label">{{ __('Car License') }}</label>
                            <input type="file" class="form-control @error('car_license') is-invalid @enderror" 
                                   id="car_license" name="car_license" accept="image/*">
                            @error('car_license')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @if($driverCar->car_license_link)
                                <div class="mt-2">
                                    <small class="text-muted">{{ __('Current license:') }}</small><br>
                                    <img src="{{ $driverCar->car_license_link }}" alt="Current car license" 
                                         class="img-thumbnail" style="max-width: 200px;">
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between">
                    <button type="submit" class="btn btn-warning btn-sm me-1">
                        {{ __('Update Car') }} <i class="fa fa-save"></i>
                    </button>
                    <a href="{{ route('driver-cars.index') }}" class="btn btn-secondary btn-sm">
                        {{ __('Cancel') }}
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
