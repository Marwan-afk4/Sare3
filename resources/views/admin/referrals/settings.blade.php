@extends('layouts.app')
@php
    $currentPage = 'referrals';
@endphp
@section('title', __('Referral Settings'))
@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="mb-0">{{ __('Referral Settings') }}</h1>
            <div class="btn-group">
                <a href="{{ route('referrals.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>{{ __('Back to Dashboard') }}
                </a>
                <a href="{{ route('referrals.list') }}" class="btn btn-outline-primary">
                    <i class="fas fa-list me-2"></i>{{ __('View Referrals') }}
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>{{ __('Please fix the following errors:') }}</strong>
                <ul class="mb-0 mt-2">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <form method="POST" action="{{ route('referrals.settings.update') }}">
            @csrf
            @method('PUT')

            <div class="row">
                <!-- New User Benefits -->
                <div class="col-lg-6 mb-4">
                    <div class="card shadow h-100">
                        <div class="card-header bg-success text-white">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-user-plus me-2"></i>{{ __('New User Benefits') }}
                            </h5>
                            <small>{{ __('Rewards given to users who use referral codes') }}</small>
                        </div>
                        <div class="card-body">
                            <div class="mb-4">
                                <label for="referred_user_discount_percentage" class="form-label fw-bold">
                                    <i class="fas fa-percentage me-2"></i>{{ __('Discount Percentage') }}
                                </label>
                                <div class="input-group">
                                    <input
                                        type="number"
                                        class="form-control form-control-lg"
                                        id="referred_user_discount_percentage"
                                        name="referred_user_discount_percentage"
                                        value="{{ old('referred_user_discount_percentage', $settings['referred_user_discount_percentage']) }}"
                                        min="0"
                                        max="100"
                                        step="0.01"
                                        required
                                    >
                                    <span class="input-group-text">%</span>
                                </div>
                                <div class="form-text text-success">
                                    <i class="fas fa-info-circle me-1"></i>
                                    {{ __('Percentage discount new users get on their rides') }}
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="referred_user_discount_rides" class="form-label fw-bold">
                                    <i class="fas fa-car me-2"></i>{{ __('Number of Discounted Rides') }}
                                </label>
                                <input
                                    type="number"
                                    class="form-control form-control-lg"
                                    id="referred_user_discount_rides"
                                    name="referred_user_discount_rides"
                                    value="{{ old('referred_user_discount_rides', $settings['referred_user_discount_rides']) }}"
                                    min="1"
                                    max="50"
                                    required
                                >
                                <div class="form-text text-success">
                                    <i class="fas fa-info-circle me-1"></i>
                                    {{ __('How many rides the new user gets the discount for') }}
                                </div>
                            </div>


                        </div>
                    </div>
                </div>

                <!-- Referrer Rewards -->
                <div class="col-lg-6 mb-4">
                    <div class="card shadow h-100">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-gift me-2"></i>{{ __('Referrer Rewards') }}
                            </h5>
                            <small>{{ __('Rewards given to users who successfully refer others') }}</small>
                        </div>
                        <div class="card-body">
                            <div class="mb-4">
                                <label for="referrer_reward_percentage" class="form-label fw-bold">
                                    <i class="fas fa-percentage me-2"></i>{{ __('Reward Percentage') }}
                                </label>
                                <div class="input-group">
                                    <input
                                        type="number"
                                        class="form-control form-control-lg"
                                        id="referrer_reward_percentage"
                                        name="referrer_reward_percentage"
                                        value="{{ old('referrer_reward_percentage', $settings['referrer_reward_percentage']) }}"
                                        min="0"
                                        max="100"
                                        step="0.01"
                                        required
                                    >
                                    <span class="input-group-text">%</span>
                                </div>
                                <div class="form-text text-primary">
                                    <i class="fas fa-info-circle me-1"></i>
                                    {{ __('Percentage discount referrers get as a reward') }}
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="referrer_reward_rides" class="form-label fw-bold">
                                    <i class="fas fa-car me-2"></i>{{ __('Number of Reward Rides') }}
                                </label>
                                <input
                                    type="number"
                                    class="form-control form-control-lg"
                                    id="referrer_reward_rides"
                                    name="referrer_reward_rides"
                                    value="{{ old('referrer_reward_rides', $settings['referrer_reward_rides']) }}"
                                    min="1"
                                    max="100"
                                    required
                                >
                                <div class="form-text text-primary">
                                    <i class="fas fa-info-circle me-1"></i>
                                    {{ __('How many rides the referrer gets the reward for') }}
                                </div>
                            </div>


                        </div>
                    </div>
                </div>
            </div>



            <!-- Save Button -->
            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-save me-2"></i>{{ __('Update Referral Settings') }}
                </button>
            </div>
        </form>
    </div>

    <script>
        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const newUserPercentage = parseFloat(document.getElementById('referred_user_discount_percentage').value);
            const referrerPercentage = parseFloat(document.getElementById('referrer_reward_percentage').value);
            
            if (newUserPercentage < 0 || newUserPercentage > 100) {
                e.preventDefault();
                alert('{{ __("New user discount percentage must be between 0 and 100") }}');
                document.getElementById('referred_user_discount_percentage').focus();
                return;
            }
            
            if (referrerPercentage < 0 || referrerPercentage > 100) {
                e.preventDefault();
                alert('{{ __("Referrer reward percentage must be between 0 and 100") }}');
                document.getElementById('referrer_reward_percentage').focus();
                return;
            }
        });
    </script>
@endsection