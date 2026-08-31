@extends('layouts.app')
@php
    $currentPage = 'settings';
    
    // Group settings by category
    $signupGiftSettings = $settings->filter(function($setting) {
        return str_contains($setting->key, 'signup_gift');
    });
    
    $referralSettings = $settings->filter(function($setting) {
        return str_contains($setting->key, 'referral') || str_contains($setting->key, 'referrer');
    });
    
    $profitSettings = $settings->filter(function($setting) {
        return (str_contains($setting->key, 'profit') || str_contains($setting->key, 'wallet'))
            && !str_contains($setting->key, 'signup_gift');
    });
    
    $otherSettings = $settings->reject(function($setting) {
        return str_contains($setting->key, 'referral') || 
               str_contains($setting->key, 'referrer') || 
               str_contains($setting->key, 'profit') || 
               str_contains($setting->key, 'wallet') ||
               str_contains($setting->key, 'signup_gift');
    });
@endphp
@section('title', __('App Settings'))
@section('content')
    <div class="container-fluid">
        <h1 class="mb-3">{{ __('App Settings') }}</h1>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <form method="POST" action="{{ route('settings.update') }}">
            @csrf
            @method('PUT')

            <!-- Referral System Settings -->
            @if($referralSettings->count() > 0)
            <div class='main-card mb-4 card'>
                <div class='card-header'>
                    <h5 class="card-title mb-0">
                        <i class="fas fa-share-alt me-2"></i>{{ __('Referral System Settings') }}
                    </h5>
                    <small class="text-muted">{{ __('Configure referral rewards for both new users and referrers') }}</small>
                </div>
                <div class='card-body'>
                    <div class="row">
                        <!-- Referred User Settings -->
                        <div class="col-md-6">
                            <h6 class="text-primary mb-3">
                                <i class="fas fa-user-plus me-1"></i>{{ __('New User Benefits') }}
                            </h6>
                            @foreach($referralSettings->filter(fn($s) => str_contains($s->key, 'referral_discount')) as $setting)
                                @include('settings.partials.setting-field', ['setting' => $setting])
                            @endforeach
                        </div>
                        
                        <!-- Referrer Rewards Settings -->
                        <div class="col-md-6">
                            <h6 class="text-success mb-3">
                                <i class="fas fa-gift me-1"></i>{{ __('Referrer Rewards') }}
                            </h6>
                            @foreach($referralSettings->filter(fn($s) => str_contains($s->key, 'referrer_reward')) as $setting)
                                @include('settings.partials.setting-field', ['setting' => $setting])
                            @endforeach
                        </div>
                    </div>
                    
                    <div class="alert alert-info mt-3">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>{{ __('How it works:') }}</strong>
                        {{ __('When someone uses a referral code, the new user gets discount benefits and the referrer gets reward benefits. Both can be active simultaneously.') }}
                    </div>
                </div>
            </div>
            @endif

            <!-- Signup Gift Settings -->
            @if($signupGiftSettings->count() > 0)
            <div class='main-card mb-4 card'>
                <div class='card-header d-flex justify-content-between align-items-center'>
                    <div>
                        <h5 class="card-title mb-0">
                            <i class="fas fa-gift me-2"></i>{{ __('Signup Gift') }}
                        </h5>
                        <small class="text-muted">{{ __('Wallet gift given to passengers the first time they sign up') }}</small>
                    </div>
                    @can('إدارة المستخدمين')
                    <a href="{{ route('signup-gifts.index') }}" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-users me-1"></i>{{ __('Manage pending gifts') }}
                    </a>
                    @endcan
                </div>
                <div class='card-body'>
                    @foreach($signupGiftSettings as $setting)
                        @include('settings.partials.setting-field', ['setting' => $setting])
                    @endforeach
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        {{ __('When enabled, every new passenger receives this amount in their wallet once. Users who signed up before this can be gifted from the Signup Gift page.') }}
                    </div>
                </div>
            </div>
            @endif

            <!-- Business Settings -->
            @if($profitSettings->count() > 0)
            <div class='main-card mb-4 card'>
                <div class='card-header'>
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-line me-2"></i>{{ __('Business Settings') }}
                    </h5>
                    <small class="text-muted">{{ __('Configure profit margins and financial settings') }}</small>
                </div>
                <div class='card-body'>
                    @foreach($profitSettings as $setting)
                        @include('settings.partials.setting-field', ['setting' => $setting])
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Other Settings -->
            @if($otherSettings->count() > 0)
            <div class='main-card mb-4 card'>
                <div class='card-header'>
                    <h5 class="card-title mb-0">
                        <i class="fas fa-cog me-2"></i>{{ __('General Settings') }}
                    </h5>
                </div>
                <div class='card-body'>
                    @foreach($otherSettings as $setting)
                        @include('settings.partials.setting-field', ['setting' => $setting])
                    @endforeach
                </div>
            </div>
            @endif

            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-save me-2"></i>{{ __('Update Settings') }}
                </button>
            </div>
        </form>
    </div>

    <script>
        // Toggle switch labels
        document.querySelectorAll('.form-check-input[type="checkbox"]').forEach(function(checkbox) {
            checkbox.addEventListener('change', function() {
                const label = this.nextElementSibling;
                label.textContent = this.checked ? 'Enabled' : 'Disabled';
            });
        });

        // Add validation for referral settings
        document.querySelector('form').addEventListener('submit', function(e) {
            const referralPercentage = document.querySelector('input[name="settings[referral_discount_percentage]"]');
            const referrerPercentage = document.querySelector('input[name="settings[referrer_reward_percentage]"]');
            
            if (referralPercentage && (referralPercentage.value < 0 || referralPercentage.value > 100)) {
                e.preventDefault();
                alert('Referral discount percentage must be between 0 and 100');
                referralPercentage.focus();
                return;
            }
            
            if (referrerPercentage && (referrerPercentage.value < 0 || referrerPercentage.value > 100)) {
                e.preventDefault();
                alert('Referrer reward percentage must be between 0 and 100');
                referrerPercentage.focus();
                return;
            }
        });
    </script>
@endsection
