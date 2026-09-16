@extends('layouts.app')
@php
    $currentPage = 'deliveries';
@endphp
@section('title', __('Deliveries'))
@section('content')
<div class="container-fluid">
    <h1 class="mb-3">{{ __('Deliveries') }}</h1>

    <form method="GET" class="row g-2 mb-3">
        <div class="col-md-3">
            <select name="status" class="form-select">
                <option value="">{{ __('All statuses') }}</option>
                @foreach(['pending','accepted','arrived','finshed','rejected','cancelled'] as $s)
                    <option value="{{ $s }}" @selected(request('status') === $s)>{{ __(ucfirst($s)) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <select name="vehicle_type" class="form-select">
                <option value="">{{ __('All vehicles') }}</option>
                @foreach(['bike' => __('Bike'), 'motorcycle' => __('Motorcycle')] as $v => $label)
                    <option value="{{ $v }}" @selected(request('vehicle_type') === $v)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <button class="btn btn-primary btn-sm">{{ __('Filter') }}</button>
        </div>
    </form>

    <div class="main-card mb-3 card">
        <div class="card-body">
            <table class="mb-0 table table-hover">
                <tr>
                    <th>{{ __('Id') }}</th>
                    <th>{{ __('User') }}</th>
                    <th>{{ __('Rider') }}</th>
                    <th>{{ __('Vehicle') }}</th>
                    <th>{{ __('Zone') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('Price') }}</th>
                    <th>{{ __('Date') }}</th>
                    <th class="text-center">{{ __('Actions') }}</th>
                </tr>
                @forelse($deliveries as $d)
                @php $st = $d->status->value; @endphp
                <tr>
                    <td>#{{ $d->id }}</td>
                    <td>{{ $d->user->name ?? '-' }}</td>
                    <td>{{ $d->rider->name ?? '-' }}</td>
                    <td>{{ $d->vehicle_type ? __($d->vehicle_type->label()) : '-' }}</td>
                    <td>{{ $d->zone->name ?? '-' }}</td>
                    <td>
                        <span class="badge bg-{{ in_array($st, ['finshed']) ? 'success' : (in_array($st, ['rejected','cancelled']) ? 'danger' : 'warning') }}">
                            {{ __(ucfirst($st)) }}
                        </span>
                    </td>
                    <td>{{ $d->calculated_final_price ?? $d->calculated_initial_price ?? '-' }}</td>
                    <td>{{ $d->created_at?->diffForHumans() }}</td>
                    <td class="text-center">
                        <a href="{{ route('deliveries.show', $d) }}" class="btn btn-subtle-primary btn-sm me-1">{{ __('Details') }} <i class="fa fa-eye"></i></a>
                        <a href="{{ route('deliveries.track', $d) }}" class="btn btn-subtle-info btn-sm">{{ __('Track') }} <i class="fa fa-map-marker"></i></a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" class="text-center text-muted">{{ __('No deliveries found.') }}</td></tr>
                @endforelse
            </table>
            {{ $deliveries->links('pagination::custom') }}
        </div>
    </div>
</div>
@endsection
