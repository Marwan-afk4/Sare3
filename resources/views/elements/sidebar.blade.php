<div class="scrollbar-sidebar">
    <div class="app-sidebar__inner">
        <ul class="vertical-nav-menu">
            <li class="app-sidebar__heading">{{ __('Dashboard') }}</li>
            <li>
                <a wire:navigate href="{{ route('home') }}" class="{{ isset($currentPage) && $currentPage == 'home' ? 'mm-active' : '' }}">
                    <i class="metismenu-icon fa fa-home"></i> {{ __('Home') }}
                </a>
            </li>
            <li>
                <a wire:navigate href="{{ route('users.index') }}" class="{{ isset($currentPage) && $currentPage == 'users' ? 'mm-active' : '' }}">
                    <i class="metismenu-icon fa fa-users"></i> {{ __('Users') }}
                </a>
            </li>
            {{-- <li>
                <a wire:navigate href="{{ route('employees.index') }}" class="{{ isset($currentPage) && $currentPage == 'emplyees' ? 'mm-active' : '' }}">
                    <i class="metismenu-icon fa fa-user"></i> {{ __('Employees') }}
                </a>
            </li>
            <li>
                <a wire:navigate href="{{ route('suppliers.index') }}" class="{{ isset($currentPage) && $currentPage == 'suppliers' ? 'mm-active' : '' }}">
                    <i class="metismenu-icon fa fa-briefcase"></i> {{ __('Suppliers') }}
                </a>
            </li>
            <li>
                <a wire:navigate href="{{ route('clients.index') }}" class="{{ isset($currentPage) && $currentPage == 'clients' ? 'mm-active' : '' }}">
                    <i class="metismenu-icon fa fa-users"></i> {{ __('Clients') }}
                </a>
            </li>
            <li>
                <a wire:navigate href="{{ route('order-channels.index') }}" class="{{ isset($currentPage) && $currentPage == 'order-channels' ? 'mm-active' : '' }}">
                    <i class="metismenu-icon fa fa-comments"></i> {{ __('Order Channels') }}
                </a>
            </li>
            <li>
                <a wire:navigate href="{{ route('delivery-vehicles.index') }}" class="{{ isset($currentPage) && $currentPage == 'delivery-vehicles' ? 'mm-active' : '' }}">
                    <i class="metismenu-icon fa fa-car"></i> {{ __('Delivery Vehicles') }}
                </a>
            </li>
            <li>
                <a wire:navigate href="{{ route('delivery-agents.index') }}" class="{{ isset($currentPage) && $currentPage == 'delivery-agents' ? 'mm-active' : '' }}">
                    <i class="metismenu-icon fa fa-motorcycle"></i> {{ __('Delivery Agents') }}
                </a>
            </li>
            <li>
                <a wire:navigate href="{{ route('delivery-orders.index') }}" class="{{ isset($currentPage) && $currentPage == 'delivery-orders' ? 'mm-active' : '' }}">
                    <i class="metismenu-icon fa fa-truck"></i> {{ __('Delivery Orders') }}
                </a>
            </li>
            <li>
                <a wire:navigate href="{{ route('supplier-product-lists.index') }}" class="{{ isset($currentPage) && $currentPage == 'supplier-product-lists' ? 'mm-active' : '' }}">
                    <i class="metismenu-icon fa fa-list"></i> {{ __('Supplier Product Lists') }}
                </a>
            </li> --}}
        </ul>
    </div>
</div>
