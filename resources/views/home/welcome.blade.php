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
                                <h4 class="text-body-tertiary mb-0">
                                    {{ __('New Users') }} :
                                    <span class="text-body-emphasis"> {{ $userCount }} </span>
                                </h4>
                            </div>
                            <div class="pb-0 pt-4">
                                <div class="echarts-new-users" style="min-height:300px;width:100%;"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <span class="me-2 text-primary" data-feather="user-check"
                                    style="height:24px; width:24px"></span>
                                <h4 class="text-body-tertiary mb-0">
                                    {{ __('New Drivers') }} :
                                    <span class="text-body-emphasis"> {{ $driverCount }} </span>
                                </h4>
                            </div>
                            <div class="pb-0 pt-4">
                                <div class="echarts-new-drivers" style="min-height:300px;width:100%;"></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Active Rides Widget -->
                <div class="col-xxl-6">
                    <x-active-rides-widget :activeRides="$activeRides" />
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
        function renderLineChart(selector, seriesName, seriesData) {
            const months = [
                "January", "February", "March", "April", "May", "June",
                "July", "August", "September", "October", "November", "December"
            ];

            const el = document.querySelector(selector);
            if (!el) return;

            if (echarts.getInstanceByDom(el)) {
                echarts.getInstanceByDom(el).dispose();
            }

            // ✅ detect dark mode (لو فيه كلاس اسمه dark-mode في body)
            const isDark = localStorage.getItem('phoenixTheme') === 'dark';

            const chart = echarts.init(el, null, {
                backgroundColor: 'transparent'
            });

            chart.setOption({
                tooltip: {
                    trigger: 'axis',
                    backgroundColor: isDark ? '#333' : '#fff',
                    textStyle: {
                        color: isDark ? '#fff' : '#000'
                    },
                    borderWidth: 0,
                    formatter: function(params) {
                        return `
                        <div>
                            <h6 class="fs-9 mb-0" style="color:${isDark ? '#fff' : '#333'}">
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
                        color: isDark ? '#ddd' : '#333', // ✅ ألوان الأرقام
                        formatter: value => value.substring(0, 3)
                    },
                    axisLine: {
                        lineStyle: {
                            color: isDark ? '#555' : '#ccc'
                        }
                    }
                },
                yAxis: {
                    type: 'value',
                    min: 0,
                    axisLabel: {
                        color: isDark ? '#ddd' : '#333' // ✅ ألوان الأرقام
                    },
                    splitLine: {
                        lineStyle: {
                            color: isDark ? '#444' : '#eee'
                        }
                    }
                },
                series: [{
                    name: seriesName,
                    type: 'line',
                    data: seriesData,
                    smooth: true,
                    symbol: 'circle',
                    symbolSize: 8,
                    lineStyle: {
                        width: 3,
                        color: isDark ? '#4dabf7' : '#1971c2'
                    },
                    itemStyle: {
                        borderWidth: 2,
                        color: isDark ? '#74c0fc' : '#228be6'
                    }
                }]
            });
        }

        function initHomePageScripts() {
            feather.replace();

            const userMonthlyCounts = @json($safeUserCounts);
            const driverMonthlyCounts = @json($safeDriverCounts);

            renderLineChart('.echarts-new-users', 'Users', userMonthlyCounts);
            renderLineChart('.echarts-new-drivers', 'Drivers', driverMonthlyCounts);
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initHomePageScripts);
        } else {
            initHomePageScripts();
        }

        document.addEventListener('turbo:load', initHomePageScripts);
        document.addEventListener('livewire:load', initHomePageScripts);

        // ✅ إعادة رسم الرسوم عند تغيير الثيم (Light/Dark)
        const observer = new MutationObserver(() => {
            initHomePageScripts();
        });

        observer.observe(document.body, {
            attributes: true,
            attributeFilter: ['class']
        });

        // ✅ دعم الـ event لو الثيم بيبعت إشارة
        // ✅ لما localStorage يتغير (مثلاً phoenixTheme يتبدل) على نفس الصفحة أو من صفحة تانية
        window.addEventListener('storage', function(e) {
            if (e.key === 'phoenixTheme') {
                initHomePageScripts();
            }
        });

        // ✅ لو عندك زرار بيغير الثيم في نفس الصفحة
        // اعمل بعد ما تغير localStorage.dispatchEvent(new Event('themeChanged'));
        window.addEventListener('themeChanged', () => {
            initHomePageScripts();
        });
    </script>
@endpush
