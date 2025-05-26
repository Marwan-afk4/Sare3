<nav class="navbar navbar-vertical navbar-expand-lg" style="display:none;">
    <div class="collapse navbar-collapse" id="navbarVerticalCollapse">
        <div class="navbar-vertical-content">
            <ul class="navbar-nav flex-column" id="navbarVerticalNav">
                <li class="nav-item">
                    <p class="navbar-vertical-label">{{ config('app.name')  }}</p>
                    <hr class="navbar-vertical-line" />
                    <div class="nav-item-wrapper">
                        <a wire:navigate class="nav-link label-1 {{ isset($currentPage) && $currentPage == 'home' ? 'active' : '' }}" href="{{ route('home') }}" role="button" data-bs-toggle="" aria-expanded="false">
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
                        <a wire:navigate class="nav-link label-1 {{ isset($currentPage) && $currentPage == 'users' ? 'active' : '' }}" href="{{ route('users.index') }}" role="button" data-bs-toggle="" aria-expanded="false">
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
                        <a wire:navigate class="nav-link label-1 {{ isset($currentPage) && $currentPage == 'drivers' ? 'active' : '' }}" href="{{ route('drivers.index') }}" role="button" data-bs-toggle="" aria-expanded="false">
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
                    {{-- <div class="nav-item-wrapper">
                        <a wire:navigate class="nav-link label-1 {{ isset($currentPage) && $currentPage == 'employees' ? 'active' : '' }}" href="{{ route('employees.index') }}" role="button" data-bs-toggle="" aria-expanded="false">
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
                        <a wire:navigate class="nav-link label-1 {{ isset($currentPage) && $currentPage == 'suppliers' ? 'active' : '' }}" href="{{ route('suppliers.index') }}" role="button" data-bs-toggle="" aria-expanded="false">
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
                        <a wire:navigate class="nav-link label-1 {{ isset($currentPage) && $currentPage == 'clients' ? 'active' : '' }}" href="{{ route('clients.index') }}" role="button" data-bs-toggle="" aria-expanded="false">
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
                        <a wire:navigate class="nav-link label-1 {{ isset($currentPage) && $currentPage == 'order-channels' ? 'active' : '' }}" href="{{ route('order-channels.index') }}" role="button" data-bs-toggle="" aria-expanded="false">
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
                        <a wire:navigate class="nav-link label-1 {{ isset($currentPage) && $currentPage == 'delivery-vehicles' ? 'active' : '' }}" href="{{ route('delivery-vehicles.index') }}" role="button" data-bs-toggle="" aria-expanded="false">
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
                        <a wire:navigate class="nav-link label-1 {{ isset($currentPage) && $currentPage == 'delivery-agents' ? 'active' : '' }}" href="{{ route('delivery-agents.index') }}" role="button" data-bs-toggle="" aria-expanded="false">
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
                        <a wire:navigate class="nav-link label-1 {{ isset($currentPage) && $currentPage == 'delivery-orders' ? 'active' : '' }}" href="{{ route('delivery-orders.index') }}" role="button" data-bs-toggle="" aria-expanded="false">
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
                        <a wire:navigate class="nav-link label-1 {{ isset($currentPage) && $currentPage == 'supplier-product-lists' ? 'active' : '' }}" href="{{ route('supplier-product-lists.index') }}" role="button" data-bs-toggle="" aria-expanded="false">
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
                        <a wire:navigate class="nav-link label-1 {{ isset($currentPage) && $currentPage == 'complains' ? 'active' : '' }}" href="{{ route('complains.index') }}" role="button" data-bs-toggle="" aria-expanded="false">
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
                        <a wire:navigate class="nav-link label-1 {{ isset($currentPage) && $currentPage == 'money-transactions' ? 'active' : '' }}" href="{{ route('money-transactions.index') }}" role="button" data-bs-toggle="" aria-expanded="false">
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
