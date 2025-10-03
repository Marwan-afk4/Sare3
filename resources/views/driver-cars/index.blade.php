@extends('layouts.app')
@php
    $currentPage = 'driver-cars';
@endphp
@section('title', __('Driver Cars'))
@section('content')
<div class="container-fluid">
    <h1>{{ __('Driver Cars') }}</h1>
    <div class="mb-3">
        <a href="{{ route('driver-cars.create') }}" class="btn btn-primary btn-sm me-1">
            <i class="fa fa-plus"></i> {{ __('Add New Car') }}
        </a>
    </div>

    <div class="main-card mb-3 card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>
                                <a href="{{ route('driver-cars.index', ['sort' => 'id', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
                                    {{ __('ID') }}
                                    @if($sortField === 'id')<i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>@endif
                                </a>
                            </th>
                            <th>{{ __('Driver') }}</th>
                            <th>{{ __('Car Number') }}</th>
                            <th>{{ __('Category') }}</th>
                            <th>{{ __('Model') }}</th>
                            <th>{{ __('Type') }}</th>
                            <th>{{ __('Color') }}</th>
                            <th>{{ __('Created At') }}</th>
                            <th class="text-center">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($driverCars as $driverCar)
                        <tr>
                            <td>{{ $driverCar->id }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    @if($driverCar->driver && $driverCar->driver->image_link)
                                        <img src="{{ $driverCar->driver->image_link }}" alt="{{ $driverCar->driver->name }}" 
                                             class="rounded-circle me-2" width="30" height="30">
                                    @endif
                                    <div>
                                        <div class="fw-bold">{{ $driverCar->driver->name ?? 'N/A' }}</div>
                                        <small class="text-muted">{{ $driverCar->driver->phone ?? 'N/A' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-primary">{{ $driverCar->car_number }}</span>
                            </td>
                            <td>{{ $driverCar->carCategory->name ?? 'N/A' }}</td>
                            <td>{{ $driverCar->carModel->name ?? 'N/A' }}</td>
                            <td>{{ $driverCar->carType->type_name ?? 'N/A' }}</td>
                            <td>
                                <span class="badge" style="background-color: {{ strtolower($driverCar->car_color) }}; color: white;">
                                    {{ $driverCar->car_color }}
                                </span>
                            </td>
                            <td>{{ $driverCar->created_at?->diffForHumans() ?? '-' }}</td>
                            <td class="text-center">
                                <a href="{{ route('driver-cars.show', $driverCar) }}" class="btn btn-subtle-primary btn-sm me-1">
                                    {{ __('Details') }} <i class="fa fa-eye"></i>
                                </a>
                                <a href="{{ route('driver-cars.edit', $driverCar) }}" class="btn btn-subtle-warning btn-sm me-1">
                                    {{ __('Edit') }} <i class="fa fa-edit"></i>
                                </a>
                                <form method="POST" action="{{ route('driver-cars.destroy', $driverCar) }}" class="d-inline" 
                                      onsubmit="return confirm('{{ __('Are you sure you want to delete this car?') }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-subtle-danger btn-sm">
                                        {{ __('Delete') }} <i class="fa fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center">{{ __('No driver cars found.') }}</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="d-flex justify-content-center">
                {{ $driverCars->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
