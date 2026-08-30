<div class="mb-3">
    <label for="setting_{{ $setting->key }}" class="form-label fw-bold">
        {{ ucwords(str_replace(['_', 'referral', 'referrer', 'signup'], [' ', 'Referral', 'Referrer', 'Signup'], $setting->key)) }}
    </label>

    @if($setting->description)
        <small class="form-text text-muted d-block mb-2">{{ $setting->description }}</small>
    @endif

    @if($setting->key === 'phone_verification_method')
        <select
            class="form-select"
            id="setting_{{ $setting->key }}"
            name="settings[{{ $setting->key }}]"
        >
            <option value="backend_otp" {{ $setting->value === 'backend_otp' ? 'selected' : '' }}>
                {{ __('Backend OTP (WhatsApp)') }}
            </option>
            <option value="kastana" {{ $setting->value === 'kastana' ? 'selected' : '' }}>
                {{ __('Backend OTP (Kastana SMS)') }}
            </option>
            <option value="firebase_otp" {{ $setting->value === 'firebase_otp' ? 'selected' : '' }}>
                {{ __('Firebase Phone Auth') }}
            </option>
        </select>
        <small class="form-text text-muted d-block mt-2">
            {{ __('Mobile apps keep using the same backend OTP or Firebase endpoints. Kastana only replaces WhatsApp delivery; /auth/otp-method still returns backend_otp.') }}
        </small>
    @elseif($setting->type === 'boolean')
        <div class="form-check form-switch">
            <!-- Hidden input to ensure unchecked checkboxes send a value -->
            <input type="hidden" name="settings[{{ $setting->key }}]" value="0">
            <input
                class="form-check-input"
                type="checkbox"
                id="setting_{{ $setting->key }}"
                name="settings[{{ $setting->key }}]"
                value="1"
                {{ $setting->cast_value ? 'checked' : '' }}
            >
            <label class="form-check-label" for="setting_{{ $setting->key }}">
                {{ $setting->cast_value ? 'Enabled' : 'Disabled' }}
            </label>
        </div>
    @elseif($setting->type === 'integer')
        <input
            type="number"
            class="form-control"
            id="setting_{{ $setting->key }}"
            name="settings[{{ $setting->key }}]"
            value="{{ $setting->value }}"
            @if(str_contains($setting->key, 'rides'))
                min="1" max="{{ str_contains($setting->key, 'referrer') ? '100' : '50' }}"
            @endif
        >
    @elseif(str_contains($setting->key, 'amount') || $setting->key === 'minimum_driver_wallet_balance')
        <div class="input-group">
            <input
                type="number"
                class="form-control"
                id="setting_{{ $setting->key }}"
                name="settings[{{ $setting->key }}]"
                value="{{ $setting->value }}"
                min="0"
                step="0.01"
            >
            <span class="input-group-text">{{ __('JOD') }}</span>
        </div>
    @elseif(str_contains($setting->key, 'percentage'))
        <div class="input-group">
            <input
                type="number"
                class="form-control"
                id="setting_{{ $setting->key }}"
                name="settings[{{ $setting->key }}]"
                value="{{ $setting->value }}"
                min="0"
                max="100"
                step="0.01"
                placeholder="{{ __('Enter percentage (0-100)') }}"
            >
            <span class="input-group-text">%</span>
        </div>
        @if(str_contains($setting->key, 'admin_profit'))
            <small class="form-text text-muted">
                {{ __('This percentage will be deducted from driver earnings on each completed ride.') }}
            </small>
        @elseif(str_contains($setting->key, 'user_wallet_payment'))
            <small class="form-text text-muted">
                {{ __('Maximum share of the ride fare that can be paid from the passenger wallet. Example: 50% of a 20 fare = 10. If the wallet has less, the full wallet is used. The rest is cash.') }}
            </small>
        @elseif(str_contains($setting->key, 'referral_discount'))
            <small class="form-text text-success">
                {{ __('Discount given to new users who use referral codes.') }}
            </small>
        @elseif(str_contains($setting->key, 'referrer_reward'))
            <small class="form-text text-primary">
                {{ __('Reward given to users who successfully refer others.') }}
            </small>
        @endif
    @elseif($setting->type === 'json')
        <textarea
            class="form-control"
            id="setting_{{ $setting->key }}"
            name="settings[{{ $setting->key }}]"
            rows="4"
        >{{ is_array($setting->value) ? json_encode($setting->value, JSON_PRETTY_PRINT) : $setting->value }}</textarea>
    @else
        <input
            type="text"
            class="form-control"
            id="setting_{{ $setting->key }}"
            name="settings[{{ $setting->key }}]"
            value="{{ $setting->value }}"
        >
    @endif
</div>