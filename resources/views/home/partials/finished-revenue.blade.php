@php
    $highlight = $finishedRevenue['is_current_year']
        ? $finishedRevenue['this_month']
        : ($finishedRevenue['best_month_index'] !== null
            ? $finishedRevenue['months'][$finishedRevenue['best_month_index']]
            : null);
    $highlightLabel = $finishedRevenue['is_current_year'] ? __('This Month') : __('Best month');
    $change = $finishedRevenue['change_percent'];
@endphp

<div class="main-card mb-3 card finished-revenue-card">
    <div class="card-header border-bottom py-3">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div>
                <h4 class="mb-1">{{ __('Finished rides revenue') }}</h4>
                <p class="text-body-tertiary mb-0">{{ __('Final fare of every ride marked finished, grouped by the month it ended.') }}</p>
            </div>
            <div class="revenue-year-switcher d-flex flex-wrap gap-1" role="group" aria-label="{{ __('This Year') }}">
                @foreach ($finishedRevenue['available_years'] as $year)
                    <a href="{{ route('home', ['revenue_year' => $year]) }}"
                       class="btn btn-sm {{ (int) $year === (int) $finishedRevenue['year'] ? 'btn-primary' : 'btn-phoenix-secondary' }}">
                        {{ $year }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-xl-3">
                <div class="revenue-stat h-100">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-body-tertiary fs-9">{{ __('Year total') }}</span>
                        <span class="revenue-stat-icon text-success"><i class="fa-solid fa-coins"></i></span>
                    </div>
                    <div class="revenue-amount"><span dir="ltr">${{ number_format($finishedRevenue['year_total'], 2) }}</span></div>
                    <span class="fs-10 text-body-tertiary">{{ $finishedRevenue['year'] }}</span>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="revenue-stat h-100 {{ $finishedRevenue['is_current_year'] ? 'is-current' : '' }}">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-body-tertiary fs-9">{{ $highlightLabel }}</span>
                        <span class="revenue-stat-icon text-primary"><i class="fa-solid fa-calendar-check"></i></span>
                    </div>
                    <div class="revenue-amount"><span dir="ltr">${{ number_format($highlight['total'] ?? 0, 2) }}</span></div>
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <span class="fs-10 text-body-tertiary">
                            {{ $highlight ? __($highlight['label']) : '—' }}
                            · {{ __(':count rides', ['count' => number_format($highlight['rides'] ?? 0)]) }}
                        </span>
                        @if ($finishedRevenue['is_current_year'] && $change !== null)
                            <span class="revenue-change {{ $change >= 0 ? 'is-up' : 'is-down' }}" dir="ltr" title="{{ __('vs previous month') }}">
                                <i class="fa-solid {{ $change >= 0 ? 'fa-arrow-up' : 'fa-arrow-down' }}"></i>
                                {{ $change > 0 ? '+' : '' }}{{ number_format($change, 1) }}%
                            </span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="revenue-stat h-100">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-body-tertiary fs-9">{{ __('Finished rides') }}</span>
                        <span class="revenue-stat-icon text-info"><i class="fa-solid fa-flag-checkered"></i></span>
                    </div>
                    <div class="revenue-amount">{{ number_format($finishedRevenue['year_rides']) }}</div>
                    <span class="fs-10 text-body-tertiary">{{ $finishedRevenue['year'] }}</span>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="revenue-stat h-100">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-body-tertiary fs-9">{{ __('Average fare') }}</span>
                        <span class="revenue-stat-icon text-warning"><i class="fa-solid fa-receipt"></i></span>
                    </div>
                    <div class="revenue-amount"><span dir="ltr">${{ number_format($finishedRevenue['average_fare'], 2) }}</span></div>
                    <span class="fs-10 text-body-tertiary">{{ __('Per finished ride') }}</span>
                </div>
            </div>
        </div>

        @if ($finishedRevenue['year_rides'] === 0)
            <p class="text-body-tertiary text-center mb-3">{{ __('No finished rides in this year yet.') }}</p>
        @endif

        <div class="echarts-finished-revenue mb-2" dir="ltr" style="min-height: 300px; width: 100%;"></div>
        <div class="d-flex flex-wrap gap-3 mb-4 fs-10 text-body-tertiary">
            <span class="d-inline-flex align-items-center gap-2">
                <span class="revenue-legend-swatch is-month"></span>{{ __('Finished rides') }}
            </span>
            @if ($finishedRevenue['is_current_year'])
                <span class="d-inline-flex align-items-center gap-2">
                    <span class="revenue-legend-swatch is-current"></span>{{ __('Current month') }}
                </span>
            @endif
            <span class="d-inline-flex align-items-center gap-2">
                <span class="revenue-legend-swatch is-best"></span>{{ __('Best month') }}
            </span>
        </div>

        <div class="row g-2" dir="ltr">
            @foreach ($finishedRevenue['months'] as $month)
                @php
                    $share = $finishedRevenue['max_total'] > 0
                        ? min(100, ($month['total'] / $finishedRevenue['max_total']) * 100)
                        : 0;
                @endphp
                <div class="col-6 col-md-4 col-lg-3 col-xxl-2">
                    <div class="revenue-month h-100 {{ $month['is_current'] ? 'is-current' : '' }} {{ $month['is_best'] ? 'is-best' : '' }}">
                        <div class="d-flex align-items-center justify-content-between gap-2 mb-1">
                            <span class="fw-semibold text-body-emphasis">{{ __($month['label']) }}</span>
                            @if ($month['is_best'])
                                <span class="badge rounded-pill revenue-best-badge">{{ __('Best month') }}</span>
                            @elseif ($month['is_current'])
                                <span class="badge rounded-pill bg-primary-subtle text-primary">{{ __('Current month') }}</span>
                            @endif
                        </div>
                        <div class="revenue-month-amount"><span dir="ltr">${{ number_format($month['total'], 2) }}</span></div>
                        <div class="fs-10 text-body-tertiary mb-2">{{ __(':count rides', ['count' => number_format($month['rides'])]) }}</div>
                        <div class="progress revenue-month-bar" role="presentation">
                            <div class="progress-bar" style="width: {{ number_format($share, 2, '.', '') }}%"></div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
