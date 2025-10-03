<nav class="navbar navbar-vertical navbar-expand-lg" style="display:none;">
    <div class="collapse navbar-collapse" id="navbarVerticalCollapse">
        <div class="navbar-vertical-content">
            <ul class="navbar-nav flex-column" id="navbarVerticalNav">
                <li class="nav-item">
                    <p class="navbar-vertical-label">{{ config('app.name')  }}</p>
                    <hr class="navbar-vertical-line" />
                    <div class="nav-item-wrapper">
                        <a class="nav-link label-1 {{ isset($currentPage) && $currentPage == 'home' ? 'active' : '' }}" href="{{ route('home') }}" role="button" data-bs-toggle="" aria-expanded="false">
                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon">
                                    <span data-feather="home"></span>
                                </span>
                                <span class="nav-link-text-wrapper">
                                    <span class="nav-link-text">{{ __('Home') }}</span>
                                </span>
                            </div>
                        </a>
                    </div>
                    <div class="nav-item-wrapper">
                        <a class="nav-link label-1 {{ isset($currentPage) && $currentPage == 'users' ? 'active' : '' }}" href="{{ route('users.index') }}" role="button" data-bs-toggle="" aria-expanded="false">
                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon">
                                    <span data-feather="users"></span>
                                </span>
                                <span class="nav-link-text-wrapper">
                                    <span class="nav-link-text">{{ __('Users') }}</span>
                                </span>
                            </div>
                        </a>
                    </div>
                    <div class="nav-item-wrapper">
                        <a class="nav-link label-1 {{ isset($currentPage) && $currentPage == 'drivers' ? 'active' : '' }}" href="{{ route('drivers.index') }}" role="button" data-bs-toggle="" aria-expanded="false">
                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon">
                                    <span data-feather="truck"></span>
                                </span>
                                <span class="nav-link-text-wrapper">
                                    <span class="nav-link-text">{{ __('Drivers') }}</span>
                                </span>
                            </div>
                        </a>
                    </div>
                    <div class="nav-item-wrapper">
                        <a class="nav-link label-1 {{ isset($currentPage) && $currentPage == 'driver-cars' ? 'active' : '' }}" href="{{ route('driver-cars.index') }}" role="button" data-bs-toggle="" aria-expanded="false">
                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon">
                                    <span data-feather="car"></span>
                                </span>
                                <span class="nav-link-text-wrapper">
                                    <span class="nav-link-text">{{ __('Driver Cars') }}</span>
                                </span>
                            </div>
                        </a>
                    </div>
                    {{-- wallet-requests --}}
                    <div class="nav-item-wrapper">
                        <a class="nav-link label-1 {{ isset($currentPage) && $currentPage == 'wallet-requests' ? 'active' : '' }}"

                            href="{{ route('wallet-requests.index') }}">
                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon"><span data-feather="credit-card"></span></span>
                                <span class="nav-link-text-wrapper"><span
                                        class="nav-link-text">{{ __('Wallet Requests') }}</span></span>
                            </div>
                        </a>
                    </div>
                    {{-- document-types --}}
                    <div class="nav-item-wrapper">
                        <a class="nav-link label-1 {{ isset($currentPage) && $currentPage == 'document-types' ? 'active' : '' }}"

                            href="{{ route('document-types.index') }}">
                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon"><span data-feather="list"></span></span>
                                <span class="nav-link-text-wrapper"><span
                                        class="nav-link-text">{{ __('Document Types') }}</span></span>
                            </div>
                        </a>
                    </div>
                    {{-- carCategories --}}
                    <div class="nav-item-wrapper">
                        <a class="nav-link label-1 {{ isset($currentPage) && $currentPage == 'car-categories' ? 'active' : '' }}"

                            href="{{ route('car-categories.index') }}">
                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon"><span data-feather="layers"></span></span>
                                <span class="nav-link-text-wrapper"><span
                                        class="nav-link-text">{{ __('Car Categories') }}</span></span>
                            </div>
                        </a>
                    </div>
                    {{-- carModels --}}
                    <div class="nav-item-wrapper">
                        <a class="nav-link label-1 {{ isset($currentPage) && $currentPage == 'car-models' ? 'active' : '' }}"

                            href="{{ route('car-models.index') }}">
                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon"><span data-feather="camera"></span></span>
                                <span class="nav-link-text-wrapper"><span
                                        class="nav-link-text">{{ __('Car Models') }}</span></span>
                            </div>
                        </a>
                    </div>
                    {{-- carTypes --}}
                    <div class="nav-item-wrapper">
                        <a class="nav-link label-1 {{ isset($currentPage) && $currentPage == 'car-types' ? 'active' : '' }}"

                            href="{{ route('car-types.index') }}">
                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon"><span data-feather="truck"></span></span>
                                <span class="nav-link-text-wrapper"><span
                                        class="nav-link-text">{{ __('Car Types') }}</span></span>
                            </div>
                        </a>
                    </div>
                    {{-- rides --}}
                    <div class="nav-item-wrapper">
                        <a class="nav-link label-1 {{ isset($currentPage) && $currentPage == 'rides' ? 'active' : '' }}"

                            href="{{ route('rides.index') }}">
                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon"><span data-feather="navigation"></span></span>
                                <span class="nav-link-text-wrapper"><span
                                        class="nav-link-text">{{ __('Rides') }}</span></span>
                            </div>
                        </a>
                    </div>

                    {{-- paymenent-methods --}}
                    <div class="nav-item-wrapper">
                        <a class="nav-link label-1 {{ isset($currentPage) && $currentPage == 'paymenent-methods' ? 'active' : '' }}"

                            href="{{ route('paymenent-methods.index') }}">
                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon"><span data-feather="credit-card"></span></span>
                                <span class="nav-link-text-wrapper"><span
                                        class="nav-link-text">{{ __('Payment Methods') }}</span></span>
                            </div>
                        </a>
                    </div>

                    {{-- cancelationPolicy --}}
                    <div class="nav-item-wrapper">
                        <a class="nav-link label-1 {{ isset($currentPage) && $currentPage == 'cancellation-policies' ? 'active' : '' }}"

                            href="{{ route('cancellation-policies.index') }}">
                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon"><span data-feather="alert-circle"></span></span>
                                <span class="nav-link-text-wrapper"><span
                                        class="nav-link-text">{{ __('Cancelation Policy') }}</span></span>
                            </div>
                        </a>
                    </div>

                    {{-- cancellation-reasons --}}
                    <div class="nav-item-wrapper">
                        <a class="nav-link label-1 {{ isset($currentPage) && $currentPage == 'cancellation-reasons' ? 'active' : '' }}"
                            href="{{ route('cancellation-reasons.index') }}">
                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon"><span data-feather="x-circle"></span></span>
                                <span class="nav-link-text-wrapper"><span
                                        class="nav-link-text">{{ __('Cancellation Reasons') }}</span></span>
                            </div>
                        </a>
                    </div>

                    {{-- cancelation-rides --}}
                    <div class="nav-item-wrapper">
                        <a class="nav-link label-1 {{ isset($currentPage) && $currentPage == 'cancelation-rides' ? 'active' : '' }}"

                            href="{{ route('cancelation-rides.index') }}">
                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon"><span data-feather="slash"></span></span>
                                <span class="nav-link-text-wrapper"><span
                                        class="nav-link-text">{{ __('Cancelation Rides') }}</span></span>
                            </div>
                        </a>
                    </div>

                    {{-- ride-request-time-limits --}}
                    <div class="nav-item-wrapper">
                        <a class="nav-link label-1 {{ isset($currentPage) && $currentPage == 'ride-request-time-limits' ? 'active' : '' }}"

                            href="{{ route('ride-request-time-limits.index') }}">
                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon"><span data-feather="clock"></span></span>
                                <span class="nav-link-text-wrapper"><span
                                        class="nav-link-text">{{ __('Ride Request Time Limits') }}</span></span>
                            </div>
                        </a>
                    </div>

                    {{-- otp-limits --}}
                    <div class="nav-item-wrapper">
                        <a class="nav-link label-1 {{ isset($currentPage) && $currentPage == 'otp-limits' ? 'active' : '' }}"
                            href="{{ route('otp-limits.index') }}">
                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon"><span data-feather="key"></span></span>
                                <span class="nav-link-text-wrapper"><span
                                        class="nav-link-text">{{ __('OTP Limits') }}</span></span>
                            </div>
                        </a>
                    </div>

                    {{-- notifications --}}
                    <div class="nav-item-wrapper">
                        <a class="nav-link label-1 {{ isset($currentPage) && $currentPage == 'notifications' ? 'active' : '' }}"
                            href="{{ route('notifications.index') }}">
                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon"><span data-feather="bell"></span></span>
                                <span class="nav-link-text-wrapper"><span
                                        class="nav-link-text">{{ __('Notifications') }}</span></span>
                            </div>
                        </a>
                    </div>

                    {{-- zones --}}
                    <div class="nav-item-wrapper">
                        <a class="nav-link label-1 {{ isset($currentPage) && $currentPage == 'zones' ? 'active' : '' }}"
                            href="{{ route('zones.index') }}">
                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon"><span data-feather="map-pin"></span></span>
                                <span class="nav-link-text-wrapper"><span
                                        class="nav-link-text">{{ __('Zones') }}</span></span>
                            </div>
                        </a>
                    </div>

                    {{-- settings --}}
                    {{-- <div class="nav-item-wrapper">
                        <a class="nav-link label-1 {{ isset($currentPage) && $currentPage == 'settings' ? 'active' : '' }}"
                            href="{{ route('settings.index') }}">
                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon"><span data-feather="settings"></span></span>
                                <span class="nav-link-text-wrapper"><span
                                        class="nav-link-text">{{ __('Settings') }}</span></span>
                            </div>
                        </a>
                    </div> --}}

                    {{-- profit-statistics --}}
                    <div class="nav-item-wrapper">
                        <a class="nav-link label-1 {{ isset($currentPage) && in_array($currentPage, ['profit-statistics', 'profit-history']) ? 'active' : '' }}"
                            href="{{ route('profit-statistics.index') }}">
                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon"><span data-feather="trending-up"></span></span>
                                <span class="nav-link-text-wrapper"><span
                                        class="nav-link-text">{{ __('Profit Statistics') }}</span></span>
                            </div>
                        </a>
                    </div>

                    {{-- referrals --}}
                    <div class="nav-item-wrapper">
                        <a class="nav-link label-1 {{ isset($currentPage) && $currentPage == 'referrals' ? 'active' : '' }}"
                            href="{{ route('referrals.index') }}">
                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon"><span data-feather="share-2"></span></span>
                                <span class="nav-link-text-wrapper"><span
                                        class="nav-link-text">{{ __('Referral Management') }}</span></span>
                            </div>
                        </a>
                    </div>

                    {{-- coupons --}}
                    <div class="nav-item-wrapper">
                        <a class="nav-link label-1 {{ isset($currentPage) && $currentPage == 'coupons' ? 'active' : '' }}"
                            href="{{ route('coupons.index') }}">
                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon"><span data-feather="tag"></span></span>
                                <span class="nav-link-text-wrapper"><span
                                        class="nav-link-text">{{ __('Coupon Management') }}</span></span>
                            </div>
                        </a>
                    </div>

                    {{-- settings --}}
                    <div class="nav-item-wrapper">
                        <a class="nav-link label-1 {{ isset($currentPage) && $currentPage == 'settings' ? 'active' : '' }}"
                            href="{{ route('settings.index') }}">
                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon"><span data-feather="settings"></span></span>
                                <span class="nav-link-text-wrapper"><span
                                        class="nav-link-text">{{ __('Settings') }}</span></span>
                            </div>
                        </a>
                    </div>

                    {{-- support-chat --}}
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

                    {{-- <div class="nav-item-wrapper">
                        <a class="nav-link label-1 {{ isset($currentPage) && $currentPage == 'employees' ? 'active' : '' }}" href="{{ route('employees.index') }}" role="button" data-bs-toggle="" aria-expanded="false">
                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon">
                                    <span data-feather="users"></span>
                                </span>
                                <span class="nav-link-text-wrapper">
                                    <span class="nav-link-text">{{ __('Employees') }}</span>
                                </span>
                            </div>
                        </a>
                    </div>
                    <div class="nav-item-wrapper">
                        <a class="nav-link label-1 {{ isset($currentPage) && $currentPage == 'suppliers' ? 'active' : '' }}" href="{{ route('suppliers.index') }}" role="button" data-bs-toggle="" aria-expanded="false">
                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon">
                                    <span data-feather="box"></span>
                                </span>
                                <span class="nav-link-text-wrapper">
                                    <span class="nav-link-text">{{ __('Suppliers') }}</span>
                                </span>
                            </div>
                        </a>
                    </div>
                    <div class="nav-item-wrapper">
                        <a class="nav-link label-1 {{ isset($currentPage) && $currentPage == 'clients' ? 'active' : '' }}" href="{{ route('clients.index') }}" role="button" data-bs-toggle="" aria-expanded="false">
                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon">
                                    <span data-feather="briefcase"></span>
                                </span>
                                <span class="nav-link-text-wrapper">
                                    <span class="nav-link-text">{{ __('Clients') }}</span>
                                </span>
                            </div>
                        </a>
                    </div>
                    <div class="nav-item-wrapper">
                        <a class="nav-link label-1 {{ isset($currentPage) && $currentPage == 'order-channels' ? 'active' : '' }}" href="{{ route('order-channels.index') }}" role="button" data-bs-toggle="" aria-expanded="false">
                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon">
                                    <span data-feather="radio"></span>
                                </span>
                                <span class="nav-link-text-wrapper">
                                    <span class="nav-link-text">{{ __('Order Channels') }}</span>
                                </span>
                            </div>
                        </a>
                    </div> --}}

                    {{-- delivery-vehicles --}}

                    {{-- <div class="nav-item-wrapper">
                        <a class="nav-link label-1 {{ isset($currentPage) && $currentPage == 'delivery-vehicles' ? 'active' : '' }}" href="{{ route('delivery-vehicles.index') }}" role="button" data-bs-toggle="" aria-expanded="false">
                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon">
                                    <span data-feather="truck"></span>
                                </span>
                                <span class="nav-link-text-wrapper">
                                    <span class="nav-link-text">{{ __('Delivery Vehicles') }}</span>
                                </span>
                            </div>
                        </a>
                    </div> --}}

                    {{-- delivery-agents --}}

                    {{-- <div class="nav-item-wrapper">
                        <a class="nav-link label-1 {{ isset($currentPage) && $currentPage == 'delivery-agents' ? 'active' : '' }}" href="{{ route('delivery-agents.index') }}" role="button" data-bs-toggle="" aria-expanded="false">
                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon">
                                    <span data-feather="user"></span>
                                </span>
                                <span class="nav-link-text-wrapper">
                                    <span class="nav-link-text">{{ __('Delivery Agents') }}</span>
                                </span>
                            </div>
                        </a>
                    </div> --}}

                    {{-- delivery-orders --}}

                    {{-- <div class="nav-item-wrapper">
                        <a class="nav-link label-1 {{ isset($currentPage) && $currentPage == 'delivery-orders' ? 'active' : '' }}" href="{{ route('delivery-orders.index') }}" role="button" data-bs-toggle="" aria-expanded="false">
                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon">
                                    <span data-feather="package"></span>
                                </span>
                                <span class="nav-link-text-wrapper">
                                    <span class="nav-link-text">{{ __('Delivery Orders') }}</span>
                                </span>
                            </div>
                        </a>
                    </div> --}}


                    {{-- supplier-product-lists --}}


                    {{-- <div class="nav-item-wrapper">
                        <a class="nav-link label-1 {{ isset($currentPage) && $currentPage == 'supplier-product-lists' ? 'active' : '' }}" href="{{ route('supplier-product-lists.index') }}" role="button" data-bs-toggle="" aria-expanded="false">
                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon">
                                    <span data-feather="dollar-sign"></span>
                                </span>
                                <span class="nav-link-text-wrapper">
                                    <span class="nav-link-text">{{ __('Supplier Product Lists') }}</span>
                                </span>
                            </div>
                        </a>
                    </div> --}}


                    {{-- complains --}}

                    {{-- <div class="nav-item-wrapper">
                        <a class="nav-link label-1 {{ isset($currentPage) && $currentPage == 'complains' ? 'active' : '' }}" href="{{ route('complains.index') }}" role="button" data-bs-toggle="" aria-expanded="false">
                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon">
                                    <span data-feather="alert-triangle"></span>
                                </span>
                                <span class="nav-link-text-wrapper">
                                    <span class="nav-link-text">{{ __('Complains') }}</span>
                                </span>
                            </div>
                        </a>
                    </div> --}}

                    {{-- money-transactions --}}


                    {{-- <div class="nav-item-wrapper">
                        <a class="nav-link label-1 {{ isset($currentPage) && $currentPage == 'money-transactions' ? 'active' : '' }}" href="{{ route('money-transactions.index') }}" role="button" data-bs-toggle="" aria-expanded="false">
                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon">
                                    <span data-feather="refresh-cw"></span>
                                </span>
                                <span class="nav-link-text-wrapper">
                                    <span class="nav-link-text">{{ __('Money Transactions') }}</span>
                                </span>
                            </div>
                        </a>
                    </div> --}}

                </li>
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
