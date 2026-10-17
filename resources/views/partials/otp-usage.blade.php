@php
    $otpUsed = (int) ($account->otp_used ?? 0);
    $personalLimit = (int) ($account->otp_limit ?? 0);
    $effectiveLimit = $personalLimit > 0 ? $personalLimit : (int) $defaultOtpLimit;
    $remaining = max(0, $effectiveLimit - $otpUsed);
    $blocked = $otpUsed >= $effectiveLimit;
@endphp

<div class="card mt-4" id="otp-usage">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h5 class="card-title mb-0">{{ __('OTP Usage') }}</h5>
        @if ($blocked)
            <span class="badge bg-danger">{{ __('Limit reached') }}</span>
        @else
            <span class="badge bg-success">{{ __('Can request OTP') }}</span>
        @endif
    </div>
    <div class="card-body">
        @if (session('otp_success'))
            <div class="alert alert-success py-2">{{ session('otp_success') }}</div>
        @endif

        <div class="row mb-3">
            <div class="col-md-3">
                <div class="text-center">
                    <h4 class="mb-0">{{ $otpUsed }}</h4>
                    <small class="text-muted">{{ __('Used') }}</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="text-center">
                    <h4 class="mb-0">{{ $effectiveLimit }}</h4>
                    <small class="text-muted">{{ __('Effective limit') }}</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="text-center">
                    <h4 class="mb-0 {{ $blocked ? 'text-danger' : 'text-success' }}">{{ $remaining }}</h4>
                    <small class="text-muted">{{ __('Remaining') }}</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="text-center">
                    <h4 class="mb-0">{{ $defaultOtpLimit }}</h4>
                    <small class="text-muted">{{ __('Default limit') }}</small>
                </div>
            </div>
        </div>

        <p class="text-muted small">
            {{ __('Set used to 0 to let this account request OTP again. A personal limit of 0 uses the default limit.') }}
        </p>

        <form method="POST" action="{{ $updateRoute }}" class="row g-3 align-items-end">
            @csrf
            @method('PUT')
            <div class="col-md-4">
                <label for="otp_used" class="form-label">{{ __('OTP used') }}</label>
                <input
                    type="number"
                    min="0"
                    class="form-control @error('otp_used') is-invalid @enderror"
                    id="otp_used"
                    name="otp_used"
                    value="{{ old('otp_used', $otpUsed) }}"
                    required
                >
                @error('otp_used')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-4">
                <label for="otp_limit" class="form-label">{{ __('Personal OTP limit') }}</label>
                <input
                    type="number"
                    min="0"
                    class="form-control @error('otp_limit') is-invalid @enderror"
                    id="otp_limit"
                    name="otp_limit"
                    value="{{ old('otp_limit', $personalLimit) }}"
                    required
                >
                @error('otp_limit')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-4 d-flex flex-wrap gap-2">
                <button type="submit" class="btn btn-primary btn-sm" name="otp_action" value="save">
                    {{ __('Save OTP usage') }}
                </button>
                <button
                    type="submit"
                    class="btn btn-outline-success btn-sm"
                    name="otp_action"
                    value="reset"
                    onclick="return confirm('{{ __('Reset OTP usage to 0 for this account?') }}');"
                >
                    {{ __('Reset usage') }}
                </button>
            </div>
        </form>
    </div>
</div>
