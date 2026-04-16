@extends('layouts.app')
@php
    $currentPage = 'wallet-requests';
@endphp
@section('title', __('Admin Wallet Limits'))
@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1>{{ __('Admin Wallet Limits') }}</h1>
            <a href="{{ route('wallet-requests.index') }}" class="btn btn-secondary btn-sm">
                <i class="fa fa-arrow-right"></i> {{ __('Back to') }} {{ __('Driver Wallets Management') }}
            </a>
        </div>

        <div class="alert alert-info">
            <i class="fa fa-info-circle"></i>
            {{ __('Manage wallet limits for admins who have the wallet management permission. When an admin adds money to a driver wallet, it will be deducted from their limit.') }}
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fa fa-users-cog"></i> {{ __('Admins with Wallet Management Permission') }}
                </h5>
            </div>
            <div class="card-body">
                @if ($admins->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>{{ __('ID') }}</th>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Email') }}</th>
                                    <th>{{ __('Current Limit') }}</th>
                                    <th class="text-center">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($admins as $admin)
                                    <tr>
                                        <td>#{{ $admin->id }}</td>
                                        <td>{{ $admin->name }}</td>
                                        <td>{{ $admin->email }}</td>
                                        <td>
                                            <span
                                                class="badge bg-{{ $admin->wallet_limit > 0 ? 'success' : 'warning' }} rounded-pill px-3 py-2">
                                                ${{ number_format($admin->wallet_limit, 2) }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <!-- Set Limit Button -->
                                            <button type="button" class="btn btn-primary btn-sm me-1"
                                                data-bs-toggle="modal" data-bs-target="#setLimitModal{{ $admin->id }}">
                                                <i class="fa fa-edit"></i> {{ __('Set Limit') }}
                                            </button>

                                            <!-- Add to Limit Button -->
                                            <button type="button" class="btn btn-success btn-sm me-1" data-bs-toggle="modal"
                                                data-bs-target="#addLimitModal{{ $admin->id }}">
                                                <i class="fa fa-plus"></i> {{ __('Add to Limit') }}
                                            </button>

                                            @if ($admin->wallet_limit > 0)
                                                <!-- Subtract from Limit Button -->
                                                <button type="button" class="btn btn-warning btn-sm text-dark" data-bs-toggle="modal"
                                                    data-bs-target="#subtractLimitModal{{ $admin->id }}">
                                                    <i class="fa fa-minus"></i> {{ __('Subtract from Limit') }}
                                                </button>
                                            @endif
                                        </td>
                                    </tr>

                                    <!-- Set Limit Modal -->
                                    <div class="modal fade" id="setLimitModal{{ $admin->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form method="POST"
                                                    action="{{ route('admins.update-wallet-limit', $admin) }}">
                                                    @csrf
                                                    <input type="hidden" name="action" value="set">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">{{ __('Set Wallet Limit') }} -
                                                            {{ $admin->name }}</h5>
                                                        <button type="button" class="btn-close"
                                                            data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <label class="form-label">{{ __('Current Limit') }}</label>
                                                            <input type="text" class="form-control"
                                                                value="${{ number_format($admin->wallet_limit, 2) }}"
                                                                readonly>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">{{ __('New Limit') }} <span
                                                                    class="text-danger">*</span></label>
                                                            <input type="number" name="amount" class="form-control"
                                                                step="0.01" min="0" required>
                                                            <small
                                                                class="text-muted">{{ __('This will replace the current limit') }}</small>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary"
                                                            data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                                                        <button type="submit" class="btn btn-primary">
                                                            <i class="fa fa-save"></i> {{ __('Set Limit') }}
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Add to Limit Modal -->
                                    <div class="modal fade" id="addLimitModal{{ $admin->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form method="POST"
                                                    action="{{ route('admins.update-wallet-limit', $admin) }}">
                                                    @csrf
                                                    <input type="hidden" name="action" value="add">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">{{ __('Add to Wallet Limit') }} -
                                                            {{ $admin->name }}</h5>
                                                        <button type="button" class="btn-close"
                                                            data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <label class="form-label">{{ __('Current Limit') }}</label>
                                                            <input type="text" class="form-control"
                                                                value="${{ number_format($admin->wallet_limit, 2) }}"
                                                                readonly>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">{{ __('Amount to Add') }} <span
                                                                    class="text-danger">*</span></label>
                                                            <input type="number" name="amount" class="form-control"
                                                                step="0.01" min="0.01" required>
                                                            <small
                                                                class="text-muted">{{ __('This will be added to the current limit') }}</small>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary"
                                                            data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                                                        <button type="submit" class="btn btn-success">
                                                            <i class="fa fa-plus"></i> {{ __('Add to Limit') }}
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    @if ($admin->wallet_limit > 0)
                                        <!-- Subtract from Limit Modal -->
                                        <div class="modal fade" id="subtractLimitModal{{ $admin->id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form method="POST"
                                                        action="{{ route('admins.update-wallet-limit', $admin) }}">
                                                        @csrf
                                                        <input type="hidden" name="action" value="subtract">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">{{ __('Subtract from Wallet Limit') }} -
                                                                {{ $admin->name }}</h5>
                                                            <button type="button" class="btn-close"
                                                                data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <label class="form-label">{{ __('Current Limit') }}</label>
                                                                <input type="text" class="form-control"
                                                                    value="${{ number_format($admin->wallet_limit, 2) }}"
                                                                    readonly>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">{{ __('Amount to Subtract') }} <span
                                                                        class="text-danger">*</span></label>
                                                                <input type="number" name="amount" class="form-control"
                                                                    step="0.01" min="0.01" max="{{ $admin->wallet_limit }}"
                                                                    required>
                                                                <small
                                                                    class="text-muted">{{ __('This will be subtracted from the current limit') }}</small>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary"
                                                                data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                                                            <button type="submit" class="btn btn-warning text-dark">
                                                                <i class="fa fa-minus"></i> {{ __('Subtract from Limit') }}
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fa fa-users-slash fa-4x text-muted mb-3"></i>
                        <h5 class="text-muted">{{ __('No admins with wallet management permission') }}</h5>
                        <p class="text-muted">
                            {{ __('Assign the wallet management permission to admins to manage their limits here') }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
