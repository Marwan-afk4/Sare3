@php
    // Determine which group should be open by default based on $currentPage
    $activeGroup = null;
    $groupMap = [
        'main'        => ['home', 'leaderboard', 'support-chat', 'ads', 'notifications'],
        'users'       => ['users', 'drivers', 'admins', 'roles'],
        'rides'       => ['rides', 'cancelation-rides', 'zones', 'abnormal-rides', 'cities'],
        'fleet'       => ['car-categories', 'car-models', 'car-types'],
        'finance'     => ['wallet-requests', 'financial-reports', 'profit-statistics', 'profit-history', 'referrals', 'coupons', 'paymenent-methods'],
        'settings'    => ['document-types', 'cancellation-policies', 'cancellation-reasons', 'ride-request-time-limits', 'otp-limits', 'settings'],
    ];
    foreach ($groupMap as $group => $pages) {
        if (isset($currentPage) && in_array($currentPage, $pages)) {
            $activeGroup = $group;
            break;
        }
    }
@endphp

<nav class="navbar navbar-vertical navbar-expand-lg" style="display:none;">
    <div class="collapse navbar-collapse" id="navbarVerticalCollapse">
        <div class="navbar-vertical-content scrollbar">


            <ul class="navbar-nav flex-column mb-3" id="navbarVerticalNav">

                {{-- ═══════════════════════════════════════════════ --}}
                {{-- SECTION: MAIN                                   --}}
                {{-- ═══════════════════════════════════════════════ --}}
                <li class="nav-item mt-2">
                    <p class="navbar-vertical-label">{{ __('Main') }}</p>
                    <hr class="navbar-vertical-line"/>

                    {{-- Home --}}
                    <div class="nav-item-wrapper">
                        <a class="nav-link label-1 {{ isset($currentPage) && $currentPage == 'home' ? 'active' : '' }}"
                            href="{{ route('home') }}">
                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon"><span data-feather="home"></span></span>
                                <span class="nav-link-text-wrapper"><span class="nav-link-text">{{ __('Home') }}</span></span>
                            </div>
                        </a>
                    </div>

                    {{-- Leaderboard & Bonuses --}}
                    <div class="nav-item-wrapper">
                        <a class="nav-link label-1 {{ isset($currentPage) && $currentPage == 'leaderboard' ? 'active' : '' }}"
                            href="{{ route('leaderboard.index') }}">
                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon"><span data-feather="award"></span></span>
                                <span class="nav-link-text-wrapper"><span class="nav-link-text">{{ __('Leaderboard & Bonuses') }}</span></span>
                            </div>
                        </a>
                    </div>

                    @can('إدارة الإشعارات')
                    <div class="nav-item-wrapper">
                        <a class="nav-link label-1 {{ isset($currentPage) && $currentPage == 'notifications' ? 'active' : '' }}"
                            href="{{ route('notifications.index') }}">
                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon"><span data-feather="bell"></span></span>
                                <span class="nav-link-text-wrapper"><span class="nav-link-text">{{ __('Notifications') }}</span></span>
                            </div>
                        </a>
                    </div>
                    @endcan

                    @can('إدارة الدردشة الدعمية')
                    <div class="nav-item-wrapper">
                        <a class="nav-link label-1 {{ isset($currentPage) && $currentPage == 'support-chat' ? 'active' : '' }}"
                            href="{{ route('support-chat.index') }}">
                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon"><span data-feather="message-circle"></span></span>
                                <span class="nav-link-text-wrapper">
                                    <span class="nav-link-text">{{ __('Support Chat') }}</span>
                                    <span class="badge bg-danger ms-2 d-none" id="support-chat-badge">0</span>
                                </span>
                            </div>
                        </a>
                    </div>
                    @endcan

                    @can('إدارة الإعلانات')
                    <div class="nav-item-wrapper">
                        <a class="nav-link label-1 {{ isset($currentPage) && $currentPage == 'ads' ? 'active' : '' }}"
                            href="{{ route('ads.index') }}">
                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon"><span data-feather="image"></span></span>
                                <span class="nav-link-text-wrapper"><span class="nav-link-text">{{ __('Ads') }}</span></span>
                            </div>
                        </a>
                    </div>
                    @endcan
                </li>

                {{-- ═══════════════════════════════════════════════ --}}
                {{-- SECTION: USERS                                  --}}
                {{-- ═══════════════════════════════════════════════ --}}
                @canany(['إدارة المستخدمين', 'إدارة السائقين', 'إدارة المسؤولين', 'إدارة الدورات'])
                <li class="nav-item mt-3">
                    <p class="navbar-vertical-label">{{ __('People') }}</p>
                    <hr class="navbar-vertical-line"/>

                    @can('إدارة المستخدمين')
                    <div class="nav-item-wrapper">
                        <a class="nav-link label-1 {{ isset($currentPage) && $currentPage == 'users' ? 'active' : '' }}"
                            href="{{ route('users.index') }}">
                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon"><span data-feather="users"></span></span>
                                <span class="nav-link-text-wrapper"><span class="nav-link-text">{{ __('Users') }}</span></span>
                            </div>
                        </a>
                    </div>
                    @endcan

                    @can('إدارة السائقين')
                    <div class="nav-item-wrapper">
                        <a class="nav-link label-1 {{ isset($currentPage) && $currentPage == 'drivers' ? 'active' : '' }}"
                            href="{{ route('drivers.index') }}">
                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon"><span data-feather="truck"></span></span>
                                <span class="nav-link-text-wrapper"><span class="nav-link-text">{{ __('Drivers') }}</span></span>
                                @if(!empty($pendingDriversCount) && $pendingDriversCount > 0)
                                    <a href="{{ route('drivers.index', ['status' => 'pending']) }}"
                                       class="badge rounded-pill bg-danger text-white text-decoration-none pending-drivers-badge ms-auto me-1"
                                       title="{{ $pendingDriversCount }} {{ __('pending drivers') }}"
                                       onclick="event.stopPropagation();">{{ $pendingDriversCount }}</a>
                                @endif
                            </div>
                        </a>
                    </div>
                    @endcan

                    @can('إدارة المسؤولين')
                    <div class="nav-item-wrapper">
                        <a class="nav-link label-1 {{ isset($currentPage) && $currentPage == 'admins' ? 'active' : '' }}"
                            href="{{ route('admins.index') }}">
                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon"><span data-feather="user-check"></span></span>
                                <span class="nav-link-text-wrapper"><span class="nav-link-text">{{ __('Admins') }}</span></span>
                            </div>
                        </a>
                    </div>
                    @endcan

                    @can('إدارة الدورات')
                    <div class="nav-item-wrapper">
                        <a class="nav-link label-1 {{ isset($currentPage) && $currentPage == 'roles' ? 'active' : '' }}"
                            href="{{ route('roles.index') }}">
                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon"><span data-feather="shield"></span></span>
                                <span class="nav-link-text-wrapper"><span class="nav-link-text">{{ __('Roles') }}</span></span>
                            </div>
                        </a>
                    </div>
                    @endcan
                </li>
                @endcanany

                {{-- ═══════════════════════════════════════════════ --}}
                {{-- SECTION: RIDES                                  --}}
                {{-- ═══════════════════════════════════════════════ --}}
                @canany(['إدارة الرحلات', 'إدارة الرحلات الملغاة', 'إدارة المناطق', 'إدارة المدن'])
                <li class="nav-item mt-3">
                    <p class="navbar-vertical-label">{{ __('Operations') }}</p>
                    <hr class="navbar-vertical-line"/>

                    @can('إدارة الرحلات')
                    <div class="nav-item-wrapper">
                        <a class="nav-link label-1 {{ isset($currentPage) && $currentPage == 'rides' ? 'active' : '' }}"
                            href="{{ route('rides.index') }}">
                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon"><span data-feather="navigation"></span></span>
                                <span class="nav-link-text-wrapper"><span class="nav-link-text">{{ __('Rides') }}</span></span>
                            </div>
                        </a>
                    </div>
                    <div class="nav-item-wrapper">
                        <a class="nav-link label-1 {{ isset($currentPage) && $currentPage == 'abnormal-rides' ? 'active' : '' }}"
                            href="{{ route('rides.abnormal') }}">
                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon"><span data-feather="alert-triangle"></span></span>
                                <span class="nav-link-text-wrapper"><span class="nav-link-text">{{ __('Abnormal Rides') }}</span></span>
                            </div>
                        </a>
                    </div>
                    @endcan

                    @can('إدارة الرحلات الملغاة')
                    <div class="nav-item-wrapper">
                        <a class="nav-link label-1 {{ isset($currentPage) && $currentPage == 'cancelation-rides' ? 'active' : '' }}"
                            href="{{ route('cancelation-rides.index') }}">
                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon"><span data-feather="slash"></span></span>
                                <span class="nav-link-text-wrapper"><span class="nav-link-text">{{ __('Cancelation Rides') }}</span></span>
                            </div>
                        </a>
                    </div>
                    @endcan

                    @can('إدارة المناطق')
                    <div class="nav-item-wrapper">
                        <a class="nav-link label-1 {{ isset($currentPage) && $currentPage == 'zones' ? 'active' : '' }}"
                            href="{{ route('zones.index') }}">
                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon"><span data-feather="map-pin"></span></span>
                                <span class="nav-link-text-wrapper"><span class="nav-link-text">{{ __('Zones') }}</span></span>
                            </div>
                        </a>
                    </div>
                    @endcan

                    @can('إدارة المدن')
                    <div class="nav-item-wrapper">
                        <a class="nav-link label-1 {{ isset($currentPage) && $currentPage == 'cities' ? 'active' : '' }}"
                            href="{{ route('cities.index') }}">
                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon"><span data-feather="map"></span></span>
                                <span class="nav-link-text-wrapper"><span class="nav-link-text">{{ __('Cities') }}</span></span>
                            </div>
                        </a>
                    </div>
                    @endcan
                </li>
                @endcanany

                {{-- ═══════════════════════════════════════════════ --}}
                {{-- SECTION: FLEET                                  --}}
                {{-- ═══════════════════════════════════════════════ --}}
                @canany(['إدارة فئات السيارات', 'إدارة نماذج السيارات', 'إدارة أنواع السيارات'])
                <li class="nav-item mt-3">
                    <p class="navbar-vertical-label">{{ __('Fleet') }}</p>
                    <hr class="navbar-vertical-line"/>

                    @can('إدارة فئات السيارات')
                    <div class="nav-item-wrapper">
                        <a class="nav-link label-1 {{ isset($currentPage) && $currentPage == 'car-categories' ? 'active' : '' }}"
                            href="{{ route('car-categories.index') }}">
                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon"><span data-feather="layers"></span></span>
                                <span class="nav-link-text-wrapper"><span class="nav-link-text">{{ __('Car Categories') }}</span></span>
                            </div>
                        </a>
                    </div>
                    @endcan

                    @can('إدارة نماذج السيارات')
                    <div class="nav-item-wrapper">
                        <a class="nav-link label-1 {{ isset($currentPage) && $currentPage == 'car-models' ? 'active' : '' }}"
                            href="{{ route('car-models.index') }}">
                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon"><span data-feather="camera"></span></span>
                                <span class="nav-link-text-wrapper"><span class="nav-link-text">{{ __('Car Models') }}</span></span>
                            </div>
                        </a>
                    </div>
                    @endcan

                    @can('إدارة أنواع السيارات')
                    <div class="nav-item-wrapper">
                        <a class="nav-link label-1 {{ isset($currentPage) && $currentPage == 'car-types' ? 'active' : '' }}"
                            href="{{ route('car-types.index') }}">
                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon"><span data-feather="truck"></span></span>
                                <span class="nav-link-text-wrapper"><span class="nav-link-text">{{ __('Car Types') }}</span></span>
                            </div>
                        </a>
                    </div>
                    @endcan
                </li>
                @endcanany

                {{-- ═══════════════════════════════════════════════ --}}
                {{-- SECTION: FINANCE & MARKETING                    --}}
                {{-- ═══════════════════════════════════════════════ --}}
                @canany(['إدارة طلبات المحفظة', 'إدارة التقارير المالية', 'إدارة إحصائيات الربح', 'إدارة الإحالات', 'إدارة الكوبونات', 'إدارة طرق الدفع'])
                <li class="nav-item mt-3">
                    <p class="navbar-vertical-label">{{ __('Finance') }}</p>
                    <hr class="navbar-vertical-line"/>

                    @can('إدارة طلبات المحفظة')
                    <div class="nav-item-wrapper">
                        <a class="nav-link label-1 {{ isset($currentPage) && $currentPage == 'wallet-requests' ? 'active' : '' }}"
                            href="{{ route('wallet-requests.index') }}">
                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon"><span data-feather="credit-card"></span></span>
                                <span class="nav-link-text-wrapper"><span class="nav-link-text">{{ __('Wallet Requests') }}</span></span>
                            </div>
                        </a>
                    </div>
                    @endcan

                    @can('إدارة التقارير المالية')
                    <div class="nav-item-wrapper">
                        <a class="nav-link label-1 {{ isset($currentPage) && $currentPage == 'financial-reports' ? 'active' : '' }}"
                            href="{{ route('financial-reports.index') }}">
                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon"><span data-feather="file-text"></span></span>
                                <span class="nav-link-text-wrapper"><span class="nav-link-text">{{ __('Financial Reports') }}</span></span>
                            </div>
                        </a>
                    </div>
                    @endcan

                    @can('إدارة إحصائيات الربح')
                    <div class="nav-item-wrapper">
                        <a class="nav-link label-1 {{ isset($currentPage) && in_array($currentPage, ['profit-statistics', 'profit-history']) ? 'active' : '' }}"
                            href="{{ route('profit-statistics.index') }}">
                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon"><span data-feather="trending-up"></span></span>
                                <span class="nav-link-text-wrapper"><span class="nav-link-text">{{ __('Profit Statistics') }}</span></span>
                            </div>
                        </a>
                    </div>
                    @endcan

                    @can('إدارة طرق الدفع')
                    <div class="nav-item-wrapper">
                        <a class="nav-link label-1 {{ isset($currentPage) && $currentPage == 'paymenent-methods' ? 'active' : '' }}"
                            href="{{ route('paymenent-methods.index') }}">
                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon"><span data-feather="dollar-sign"></span></span>
                                <span class="nav-link-text-wrapper"><span class="nav-link-text">{{ __('Payment Methods') }}</span></span>
                            </div>
                        </a>
                    </div>
                    @endcan

                    @can('إدارة الإحالات')
                    <div class="nav-item-wrapper">
                        <a class="nav-link label-1 {{ isset($currentPage) && $currentPage == 'referrals' ? 'active' : '' }}"
                            href="{{ route('referrals.index') }}">
                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon"><span data-feather="share-2"></span></span>
                                <span class="nav-link-text-wrapper"><span class="nav-link-text">{{ __('Referral Management') }}</span></span>
                            </div>
                        </a>
                    </div>
                    @endcan

                    @can('إدارة الكوبونات')
                    <div class="nav-item-wrapper">
                        <a class="nav-link label-1 {{ isset($currentPage) && $currentPage == 'coupons' ? 'active' : '' }}"
                            href="{{ route('coupons.index') }}">
                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon"><span data-feather="tag"></span></span>
                                <span class="nav-link-text-wrapper"><span class="nav-link-text">{{ __('Coupons') }}</span></span>
                            </div>
                        </a>
                    </div>
                    @endcan
                </li>
                @endcanany

                {{-- ═══════════════════════════════════════════════ --}}
                {{-- SECTION: CONFIGURATION                          --}}
                {{-- ═══════════════════════════════════════════════ --}}
                @canany(['إدارة أنواع المستندات', 'إدارة سياسات الإلغاء', 'إدارة أسباب الإلغاء', 'إدارة حدود وقت طلب الرحلة', 'إدارة حدود OTP', 'إدارة الإعدادات'])
                <li class="nav-item mt-3">
                    <p class="navbar-vertical-label">{{ __('Configuration') }}</p>
                    <hr class="navbar-vertical-line"/>

                    @can('إدارة أنواع المستندات')
                    <div class="nav-item-wrapper">
                        <a class="nav-link label-1 {{ isset($currentPage) && $currentPage == 'document-types' ? 'active' : '' }}"
                            href="{{ route('document-types.index') }}">
                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon"><span data-feather="file-text"></span></span>
                                <span class="nav-link-text-wrapper"><span class="nav-link-text">{{ __('Document Types') }}</span></span>
                            </div>
                        </a>
                    </div>
                    @endcan

                    @can('إدارة سياسات الإلغاء')
                    <div class="nav-item-wrapper">
                        <a class="nav-link label-1 {{ isset($currentPage) && $currentPage == 'cancellation-policies' ? 'active' : '' }}"
                            href="{{ route('cancellation-policies.index') }}">
                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon"><span data-feather="alert-circle"></span></span>
                                <span class="nav-link-text-wrapper"><span class="nav-link-text">{{ __('Cancelation Policy') }}</span></span>
                            </div>
                        </a>
                    </div>
                    @endcan

                    @can('إدارة أسباب الإلغاء')
                    <div class="nav-item-wrapper">
                        <a class="nav-link label-1 {{ isset($currentPage) && $currentPage == 'cancellation-reasons' ? 'active' : '' }}"
                            href="{{ route('cancellation-reasons.index') }}">
                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon"><span data-feather="x-circle"></span></span>
                                <span class="nav-link-text-wrapper"><span class="nav-link-text">{{ __('Cancellation Reasons') }}</span></span>
                            </div>
                        </a>
                    </div>
                    @endcan

                    @can('إدارة حدود وقت طلب الرحلة')
                    <div class="nav-item-wrapper">
                        <a class="nav-link label-1 {{ isset($currentPage) && $currentPage == 'ride-request-time-limits' ? 'active' : '' }}"
                            href="{{ route('ride-request-time-limits.index') }}">
                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon"><span data-feather="clock"></span></span>
                                <span class="nav-link-text-wrapper"><span class="nav-link-text">{{ __('Ride Request Time Limits') }}</span></span>
                            </div>
                        </a>
                    </div>
                    @endcan

                    @can('إدارة حدود OTP')
                    <div class="nav-item-wrapper">
                        <a class="nav-link label-1 {{ isset($currentPage) && $currentPage == 'otp-limits' ? 'active' : '' }}"
                            href="{{ route('otp-limits.index') }}">
                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon"><span data-feather="key"></span></span>
                                <span class="nav-link-text-wrapper"><span class="nav-link-text">{{ __('OTP Limits') }}</span></span>
                            </div>
                        </a>
                    </div>
                    @endcan

                    @can('إدارة الإعدادات')
                    <div class="nav-item-wrapper">
                        <a class="nav-link label-1 {{ isset($currentPage) && $currentPage == 'settings' ? 'active' : '' }}"
                            href="{{ route('settings.index') }}">
                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon"><span data-feather="settings"></span></span>
                                <span class="nav-link-text-wrapper"><span class="nav-link-text">{{ __('Settings') }}</span></span>
                            </div>
                        </a>
                    </div>
                    @endcan
                </li>
                @endcanany

            </ul>
        </div>
    </div>
    <div class="navbar-vertical-footer">
        <button class="btn navbar-vertical-toggle border-0 fw-semibold w-100 white-space-nowrap d-flex align-items-center">
            <span class="uil uil-left-arrow-to-left fs-8"></span>
            <span class="uil uil-arrow-from-right fs-8"></span>
            <span class="navbar-vertical-footer-text ms-2">Collapsed View</span>
        </button>
    </div>
</nav>

@push('styles')
<style>
    @keyframes pulse-badge {
        0%, 100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.6); }
        50%       { transform: scale(1.12); box-shadow: 0 0 0 5px rgba(220, 53, 69, 0); }
    }
    .pending-drivers-badge {
        animation: pulse-badge 1.5s ease-in-out infinite !important;
        cursor: pointer;
    }
</style>
@endpush
