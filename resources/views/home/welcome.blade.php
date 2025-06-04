@extends('layouts.app')

@php
    $currentPage = 'home';
@endphp

@section('title', 'الصفحه الرئيسية')

@section('content')
<div class="main-card mb-3 card">
    <div class="card-body">
        <div class="row gx-4 gy-6 pb-5">
            <div class="col-xxl-6">
                <div class="mb-3">
                    <h3>{{ __('New Users & Drivers') }}</h3>
                    <p class="text-body-tertiary mb-0">{{ __('Number of new registered accounts') }}</p>
                </div>
                <div class="row g-6">
                    <div class="col-md-6 mb-2 mb-sm-0">
                        <div class="d-flex align-items-center">
                            <span class="me-2 text-info" data-feather="users" style="min-height:24px; width:24px"></span>
                            <h4 class="text-body-tertiary mb-0">{{ __('New Users') }} : <span class="text-body-emphasis"> {{ $userCount }} </span></h4>
                        </div>
                        <div class="pb-0 pt-4">
                            <div class="echarts-new-users" style="min-height:300px;width:100%; background:#f9f9f9;"></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-center">
                            <span class="me-2 text-primary" data-feather="user-check" style="height:24px; width:24px"></span>
                            <h4 class="text-body-tertiary mb-0">{{ __('New Drivers') }} : <span class="text-body-emphasis"> {{ $driverCount }} </span></h4>
                        </div>
                        <div class="pb-0 pt-4">
                            <div class="echarts-new-drivers" style="min-height:300px;width:100%; background:#f9f9f9;"></div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- Feather Icons -->
<script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
<!-- ECharts -->
<script src="https://cdn.jsdelivr.net/npm/echarts/dist/echarts.min.js"></script>

@php
    $safeUserCounts = $userMonthlyCounts ?? [12, 15, 20, 18, 22, 30, 25, 28, 24, 26, 30, 33];
    $safeDriverCounts = $driverMonthlyCounts ?? [5, 7, 9, 6, 10, 12, 8, 11, 9, 13, 14, 16];
@endphp

<script>
    feather.replace();

    const renderLineChart = (selector, seriesName, seriesData) => {
        const months = [
            "January", "February", "March", "April", "May", "June",
            "July", "August", "September", "October", "November", "December"
        ];

        const el = document.querySelector(selector);
        if (!el) return;

        const chart = echarts.init(el);

        chart.setOption({
            tooltip: {
                trigger: 'axis',
                formatter: function (params) {
                    return `
                        <div>
                            <h6 class="fs-9 text-body-tertiary mb-0">
                                <span class="fas fa-circle me-1" style='color:${params[0].color}'></span>
                                ${params[0].seriesName} : ${params[0].value}
                            </h6>
                        </div>
                    `;
                }
            },
            xAxis: {
                type: 'category',
                data: months,
                axisLabel: {
                    formatter: value => value.substring(0, 3)
                }
            },
            yAxis: {
                type: 'value',
                min: 0
            },
            series: [{
                name: seriesName,
                type: 'line',
                data: seriesData,
                smooth: true,
                symbol: 'circle',
                symbolSize: 8,
                lineStyle: {
                    width: 3
                },
                itemStyle: {
                    borderWidth: 2
                }
            }]
        });
    };

    document.addEventListener('DOMContentLoaded', () => {
        const userMonthlyCounts = @json($safeUserCounts);
        const driverMonthlyCounts = @json($safeDriverCounts);

        renderLineChart('.echarts-new-users', 'Users', userMonthlyCounts);
        renderLineChart('.echarts-new-drivers', 'Drivers', driverMonthlyCounts);
    });
</script>

@endpush
