@extends('layouts.app')
@php
    $currentPage = 'google-api-usage';
@endphp
@section('title', __('Google API usage'))
@section('content')
    <div class="container-fluid">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
            <div>
                <h1 class="mb-1">{{ __('Google API usage') }}</h1>
                <p class="text-body-secondary mb-0">{{ $report['label'] }} · {{ __('Free caps reset at midnight Pacific Time on the 1st') }}</p>
            </div>
            <div class="btn-group">
                <a class="btn btn-outline-primary btn-sm" href="{{ route('google-api-usage.index', ['month' => $previousMonth]) }}">{{ __('Previous month') }}</a>
                @if (! $isCurrentMonth)
                    <a class="btn btn-outline-primary btn-sm" href="{{ route('google-api-usage.index', ['month' => $nextMonth]) }}">{{ __('Next month') }}</a>
                @endif
            </div>
        </div>

        <div class="alert alert-info">
            {{ __('These numbers are calls this server recorded. They start from the day tracking was turned on, so earlier months stay at zero. The mobile apps can also call Google with the same key, and those calls are not in this table. The dollar amounts are an estimate from Google\'s published list prices, not the invoice.') }}
            @if ($report['first_tracked'])
                <div class="mt-1"><strong>{{ __('Tracking started') }}:</strong> {{ $report['first_tracked'] }}</div>
            @else
                <div class="mt-1">{{ __('No calls have been recorded yet.') }}</div>
            @endif
        </div>

        <div class="row mb-4">
            <div class="col-md-4 mb-3">
                <div class="card bg-danger text-white h-100">
                    <div class="card-body">
                        <h6 class="card-title">{{ __('Estimated charge this month') }}</h6>
                        <h3 class="mb-0">${{ number_format($report['total_cost'], 2) }}</h3>
                        <small>{{ __('After each product\'s free cap') }}</small>
                    </div>
                </div>
            </div>
            <div class="col-md-8 mb-3">
                <div class="card h-100">
                    <div class="card-body">
                        <h6 class="card-title">{{ __('Also used, and not charged per call') }}</h6>
                        <ul class="mb-0">
                            <li>{{ __('Firebase Realtime Database stores live driver locations. Firebase bills storage and download, not each write.') }}</li>
                            <li>{{ __('Google sign-in only checks an ID token on the server. That check is free.') }}</li>
                            <li>{{ __('Firebase phone login verifies a token here. The SMS itself is billed by Firebase, and this page does not see those SMS messages.') }}</li>
                            <li>{{ __('Maps SDK on the mobile apps is unlimited and free. Drawing tools on the zone map are included in the Dynamic Maps load.') }}</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead>
                            <tr>
                                <th>{{ __('API') }}</th>
                                <th>{{ __('Used this month') }}</th>
                                <th>{{ __('Free cap') }}</th>
                                <th>{{ __('Left in the free cap') }}</th>
                                <th>{{ __('Over the free cap') }}</th>
                                <th>{{ __('Price after the free cap') }}</th>
                                <th>{{ __('Estimated charge') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($report['rows'] as $row)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ __($row['name']) }}</div>
                                        <div class="small text-body-secondary">{{ __($row['category']) }}@if ($row['sku_id']) · {{ $row['sku_id'] }}@endif</div>
                                        <div class="small">{{ __($row['used_for']) }}</div>
                                    </td>
                                    <td>
                                        {{ number_format($row['used']) }} {{ __($row['unit']) }}
                                        @if ($row['sku'] === 'distance_matrix')
                                            <div class="small text-body-secondary">{{ number_format($row['requests']) }} {{ __('requests') }}</div>
                                        @endif
                                        @if ($row['percent'] !== null)
                                            <div class="progress mt-1" style="height: 6px;">
                                                <div class="progress-bar {{ $row['percent'] >= 100 ? 'bg-danger' : 'bg-primary' }}" style="width: {{ $row['percent'] }}%"></div>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($row['unlimited'])
                                            {{ __('Unlimited') }}
                                        @else
                                            {{ number_format($row['free_cap']) }}
                                        @endif
                                    </td>
                                    <td>
                                        @if ($row['unlimited'])
                                            —
                                        @else
                                            {{ number_format($row['remaining_free']) }}
                                        @endif
                                    </td>
                                    <td>{{ $row['unlimited'] ? '—' : number_format($row['overage']) }}</td>
                                    <td>{{ $row['price_after_free'] }}</td>
                                    <td class="fw-semibold">${{ number_format($row['estimated_cost'], 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">{{ __('Daily calls') }}</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>{{ __('Date') }}</th>
                                @foreach ($report['rows'] as $row)
                                    <th>{{ __($row['name']) }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($report['daily'] as $date => $entries)
                                @php $bySku = $entries->keyBy('sku'); @endphp
                                <tr>
                                    <td>{{ $date }}</td>
                                    @foreach ($report['rows'] as $row)
                                        <td>{{ number_format((int) ($bySku[$row['sku']]->units ?? 0)) }}</td>
                                    @endforeach
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ count($report['rows']) + 1 }}" class="text-center text-body-secondary py-4">
                                        {{ __('No calls have been recorded yet.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
