@extends('layouts.app')
@php
    $currentPage = 'referrals';
@endphp
@section('title', __('Referral List'))
@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="mb-0">{{ __('Referral List') }}</h1>
            <div class="btn-group">
                <a href="{{ route('referrals.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>{{ __('Back to Dashboard') }}
                </a>
                <a href="{{ route('referrals.settings') }}" class="btn btn-outline-primary">
                    <i class="fas fa-cog me-2"></i>{{ __('Settings') }}
                </a>
            </div>
        </div>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('referrals.list') }}" class="row g-3">
                    <div class="col-md-3">
                        <label for="status" class="form-label">{{ __('Status') }}</label>
                        <select name="status" id="status" class="form-select">
                            <option value="">{{ __('All Statuses') }}</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>
                                {{ __('Active') }}
                            </option>
                            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>
                                {{ __('Completed') }}
                            </option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="from_date" class="form-label">{{ __('From Date') }}</label>
                        <input type="date" name="from_date" id="from_date" class="form-control" 
                               value="{{ request('from_date') }}">
                    </div>
                    <div class="col-md-3">
                        <label for="to_date" class="form-label">{{ __('To Date') }}</label>
                        <input type="date" name="to_date" id="to_date" class="form-control" 
                               value="{{ request('to_date') }}">
                    </div>
                    <div class="col-md-3">
                        <label for="per_page" class="form-label">{{ __('Per Page') }}</label>
                        <select name="per_page" id="per_page" class="form-select">
                            <option value="15" {{ request('per_page', 15) == 15 ? 'selected' : '' }}>15</option>
                            <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter me-2"></i>{{ __('Apply Filters') }}
                        </button>
                        <a href="{{ route('referrals.list') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-2"></i>{{ __('Clear Filters') }}
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Referrals Table -->
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-list me-2"></i>{{ __('Referrals') }}
                    <span class="badge bg-secondary ms-2">{{ $referrals->total() }}</span>
                </h6>
            </div>
            <div class="card-body">
                @if($referrals->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>{{ __('Referrer') }}</th>
                                    <th>{{ __('Referred User') }}</th>
                                    <th>{{ __('Referral Code') }}</th>
                                    <th>{{ __('Discount Info') }}</th>
                                    <th>{{ __('Usage') }}</th>
                                    <th>{{ __('Date Accepted') }}</th>
                                    <th>{{ __('Status') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($referrals as $referral)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="me-3">
                                                    <span class="badge bg-{{ $referral->referrer->role === 'driver' ? 'primary' : 'success' }}">
                                                        {{ ucfirst($referral->referrer->role) }}
                                                    </span>
                                                </div>
                                                <div>
                                                    <strong>{{ $referral->referrer->name }}</strong>
                                                    <br><small class="text-muted">{{ $referral->referrer->phone }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="me-3">
                                                    <span class="badge bg-{{ $referral->referredUser->role === 'driver' ? 'primary' : 'success' }}">
                                                        {{ ucfirst($referral->referredUser->role) }}
                                                    </span>
                                                </div>
                                                <div>
                                                    <strong>{{ $referral->referredUser->name }}</strong>
                                                    <br><small class="text-muted">{{ $referral->referredUser->phone }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <code class="bg-light p-1 rounded">{{ $referral->referral_code }}</code>
                                        </td>
                                        <td>
                                            <div class="small">
                                                <div class="text-success">
                                                    <i class="fas fa-percentage me-1"></i>{{ $referral->discount_percentage }}% discount
                                                </div>
                                                <div class="text-muted">
                                                    <i class="fas fa-car me-1"></i>{{ $referral->discount_rides_count }} rides
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="progress mb-1" style="height: 20px;">
                                                @php
                                                    $percentage = $referral->discount_rides_count > 0 
                                                        ? ($referral->used_rides_count / $referral->discount_rides_count) * 100 
                                                        : 0;
                                                @endphp
                                                <div class="progress-bar bg-success" role="progressbar" 
                                                     style="width: {{ $percentage }}%">
                                                    {{ $referral->used_rides_count }}/{{ $referral->discount_rides_count }}
                                                </div>
                                            </div>
                                            <small class="text-muted">{{ number_format($percentage, 1) }}% used</small>
                                        </td>
                                        <td>
                                            @if($referral->accepted_at)
                                                {{ $referral->accepted_at->format('M d, Y') }}
                                                <br><small class="text-muted">{{ $referral->accepted_at->format('H:i') }}</small>
                                            @else
                                                <span class="text-muted">{{ __('Not accepted') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($referral->is_active && $referral->used_rides_count < $referral->discount_rides_count)
                                                <span class="badge bg-success">
                                                    <i class="fas fa-check-circle me-1"></i>{{ __('Active') }}
                                                </span>
                                            @else
                                                <span class="badge bg-secondary">
                                                    <i class="fas fa-check me-1"></i>{{ __('Completed') }}
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <div class="text-muted">
                            {{ __('Showing') }} {{ $referrals->firstItem() }} {{ __('to') }} {{ $referrals->lastItem() }} 
                            {{ __('of') }} {{ $referrals->total() }} {{ __('results') }}
                        </div>
                        {{ $referrals->appends(request()->query())->links() }}
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-handshake fa-4x text-gray-300 mb-4"></i>
                        <h4 class="text-muted">{{ __('No Referrals Found') }}</h4>
                        <p class="text-muted">
                            @if(request()->hasAny(['status', 'from_date', 'to_date']))
                                {{ __('No referrals match your current filters. Try adjusting your search criteria.') }}
                            @else
                                {{ __('No referrals have been created yet.') }}
                            @endif
                        </p>
                        @if(request()->hasAny(['status', 'from_date', 'to_date']))
                            <a href="{{ route('referrals.list') }}" class="btn btn-outline-primary">
                                <i class="fas fa-times me-2"></i>{{ __('Clear Filters') }}
                            </a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection