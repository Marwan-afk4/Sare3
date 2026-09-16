@extends('layouts.app')
@php
    $currentPage = 'delivery-agents';
@endphp
@section('title', __('Delivery Agents'))
@section('content')
<div class="container-fluid">
    <h1 class="mb-3">{{ __('Delivery Agents') }}</h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form method="GET" class="row g-2 mb-3">
        <div class="col-md-3">
            <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                placeholder="{{ __('Search by name / phone / email') }}">
        </div>
        <div class="col-md-3">
            <select name="status" class="form-select">
                <option value="">{{ __('All statuses') }}</option>
                @foreach(['approved','pending','rejected'] as $s)
                    <option value="{{ $s }}" @selected(request('status') === $s)>{{ __(ucfirst($s)) }}</option>
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
                    <th>{{ __('Name') }}</th>
                    <th>{{ __('Phone') }}</th>
                    <th>{{ __('Vehicle') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('Wallet') }}</th>
                    <th>{{ __('Available') }}</th>
                    <th class="text-center">{{ __('Actions') }}</th>
                </tr>
                @forelse($agents as $agent)
                <tr>
                    <td>{{ $agent->id }}</td>
                    <td>{{ $agent->name ?? '-' }}</td>
                    <td>{{ $agent->phone }}</td>
                    <td>
                        @if($agent->riderVehicle)
                            <span class="badge bg-info">{{ __($agent->riderVehicle->type->label()) }}</span>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td>
                        @php $st = $agent->status->value ?? $agent->status; @endphp
                        <span class="badge bg-{{ $st === 'approved' ? 'success' : ($st === 'rejected' ? 'danger' : 'warning') }}">
                            {{ __(ucfirst($st)) }}
                        </span>
                    </td>
                    <td>{{ number_format((float) $agent->wallet, 2) }}</td>
                    <td>{!! $agent->is_available ? '<span class="badge bg-success">'.__('Online').'</span>' : '<span class="badge bg-secondary">'.__('Offline').'</span>' !!}</td>
                    <td class="text-center">
                        <a href="{{ route('delivery-agents.show', $agent) }}" class="btn btn-subtle-primary btn-sm me-1">{{ __('Details') }} <i class="fa fa-eye"></i></a>
                        <form action="{{ route('delivery-agents.destroy', $agent) }}" method="POST" class="d-inline"
                            onsubmit="return confirm('{{ __('Are you sure?') }}')">
                            @csrf @method('DELETE')
                            <button class="btn btn-subtle-danger btn-sm">{{ __('Delete') }} <i class="fa fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted">{{ __('No delivery agents found.') }}</td></tr>
                @endforelse
            </table>
            {{ $agents->links('pagination::custom') }}
        </div>
    </div>
</div>
@endsection
