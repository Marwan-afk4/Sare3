@extends('layouts.app')
@php
    $currentPage = 'wallet-requests';
@endphp
@section('title', __('Driver Wallets Management'))
@section('content')
    <div class="container-fluid">
        <h1 class="mb-3">{{ __('Driver Wallets Management') }}</h1>

        <!-- Search Form -->
        <div class="card mb-3">
            <div class="card-body">
                <form method="GET" action="{{ route('wallet-requests.index') }}" class="row g-3">
                    <div class="col-md-10">
                        <input type="text" name="keyword" class="form-control"
                            placeholder="{{ __('Search by name, phone, or email...') }}" value="{{ request('keyword') }}">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fa fa-search"></i> {{ __('Search') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class='main-card mb-3 card'>
            <div class='card-body'>
                <table class="mb-0 table table-hover">
                    <thead>
                        <tr>
                            <th>
                                <a
                                    href="{{ route('wallet-requests.index', ['sort' => 'id', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
                                    {{ __('Id') }}
                                    @if ($sortField === 'id')
                                        <i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a
                                    href="{{ route('wallet-requests.index', ['sort' => 'name', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
                                    {{ __('Driver Name') }}
                                    @if ($sortField === 'name')
                                        <i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a
                                    href="{{ route('wallet-requests.index', ['sort' => 'phone', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
                                    {{ __('Phone') }}
                                    @if ($sortField === 'phone')
                                        <i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a
                                    href="{{ route('wallet-requests.index', ['sort' => 'wallet', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
                                    {{ __('Wallet Amount') }}
                                    @if ($sortField === 'wallet')
                                        <i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>
                                    @endif
                                </a>
                            </th>
                            <th class="text-center">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($drivers as $driver)
                            <tr>
                                <td>{{ $driver->id }}</td>
                                <td>
                                    <a href="{{ route('drivers.show', $driver) }}">{{ $driver->name }}</a>
                                </td>
                                <td>{{ $driver->phone }}</td>
                                <td>
                                    <span
                                        class="badge bg-{{ $driver->wallet >= 0 ? 'success' : 'danger' }} rounded-pill px-3 py-2">
                                        ${{ number_format($driver->wallet, 2) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <!-- Add to Wallet Button -->
                                    <button type="button" class="btn btn-success btn-sm me-1" data-bs-toggle="modal"
                                        data-bs-target="#addModal{{ $driver->id }}">
                                        <i class="fa fa-plus"></i> {{ __('Add') }}
                                    </button>

                                    <!-- Subtract from Wallet Button -->
                                    <button type="button" class="btn btn-danger btn-sm me-1" data-bs-toggle="modal"
                                        data-bs-target="#subtractModal{{ $driver->id }}">
                                        <i class="fa fa-minus"></i> {{ __('Subtract') }}
                                    </button>

                                    <!-- Wallet History Button -->
                                    <a href="{{ route('drivers.wallet-history', $driver) }}" class="btn btn-info btn-sm">
                                        <i class="fa fa-history"></i> {{ __('History') }}
                                    </a>
                                </td>
                            </tr>

                            <!-- Add to Wallet Modal -->
                            <div class="modal fade" id="addModal{{ $driver->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="POST" action="{{ route('drivers.add-to-wallet', $driver) }}">
                                            @csrf
                                            <div class="modal-header">
                                                <h5 class="modal-title">{{ __('Add to Wallet') }} - {{ $driver->name }}
                                                </h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label">{{ __('Current Balance') }}</label>
                                                    <input type="text" class="form-control"
                                                        value="${{ number_format($driver->wallet, 2) }}" readonly>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">{{ __('Amount to Add') }} <span
                                                            class="text-danger">*</span></label>
                                                    <input type="number" name="amount" class="form-control" step="0.01"
                                                        min="0.01" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">{{ __('Note') }}</label>
                                                    <textarea name="note" class="form-control" rows="3" placeholder="{{ __('Optional note...') }}"></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary"
                                                    data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                                                <button type="submit" class="btn btn-success">
                                                    <i class="fa fa-plus"></i> {{ __('Add to Wallet') }}
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Subtract from Wallet Modal -->
                            <div class="modal fade" id="subtractModal{{ $driver->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="POST" action="{{ route('drivers.subtract-from-wallet', $driver) }}">
                                            @csrf
                                            <div class="modal-header">
                                                <h5 class="modal-title">{{ __('Subtract from Wallet') }} -
                                                    {{ $driver->name }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label">{{ __('Current Balance') }}</label>
                                                    <input type="text" class="form-control"
                                                        value="${{ number_format($driver->wallet, 2) }}" readonly>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">{{ __('Amount to Subtract') }} <span
                                                            class="text-danger">*</span></label>
                                                    <input type="number" name="amount" class="form-control"
                                                        step="0.01" min="0.01" max="{{ $driver->wallet }}"
                                                        required>
                                                    <small class="text-muted">{{ __('Maximum') }}:
                                                        ${{ number_format($driver->wallet, 2) }}</small>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">{{ __('Note') }}</label>
                                                    <textarea name="note" class="form-control" rows="3" placeholder="{{ __('Optional note...') }}"></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary"
                                                    data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                                                <button type="submit" class="btn btn-danger">
                                                    <i class="fa fa-minus"></i> {{ __('Subtract from Wallet') }}
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                        @empty
                            <tr>
                                <td colspan="5" class="text-center">{{ __('No drivers found') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                {{ $drivers->links('pagination::custom') }}
            </div>
        </div>
    </div>
@endsection
