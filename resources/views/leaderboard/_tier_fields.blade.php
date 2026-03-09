<div class="mb-3">
    <label class="form-label fw-semibold">
        {{ __('Tier Name') }} <span class="text-muted fw-normal">({{ __('optional') }})</span>
    </label>
    <input type="text" name="name" class="form-control"
           placeholder="{{ __('e.g. Bronze, Silver, Gold') }}"
           value="{{ old('name', isset($editing) ? '' : '') }}">
</div>

<div class="row g-3">
    <div class="col-6">
        <label class="form-label fw-semibold">{{ __('Rides Required') }}</label>
        <input type="number" name="rides_count" class="form-control" min="1" required
               placeholder="{{ __('e.g. 10') }}"
               value="{{ old('rides_count') }}">
        <div class="form-text">{{ __('Driver must complete this many rides to earn the bonus.') }}</div>
    </div>
    <div class="col-6">
        <label class="form-label fw-semibold">{{ __('Bonus Amount') }}</label>
        <div class="input-group">
            <span class="input-group-text">
                <span data-feather="dollar-sign" style="width:13px;height:13px;"></span>
            </span>
            <input type="number" name="bonus_amount" class="form-control" min="0.01" step="0.01" required
                   placeholder="0.00"
                   value="{{ old('bonus_amount') }}">
        </div>
    </div>
</div>

<div class="mt-3">
    <label class="form-label fw-semibold">{{ __('Period Type') }}</label>
    <select name="period_type" class="form-select" required>
        <option value="weekly"   {{ old('period_type') === 'weekly'   ? 'selected' : '' }}>{{ __('Weekly (resets every week)') }}</option>
        <option value="monthly"  {{ old('period_type', 'monthly') === 'monthly'  ? 'selected' : '' }}>{{ __('Monthly (resets every month)') }}</option>
        <option value="all_time" {{ old('period_type') === 'all_time' ? 'selected' : '' }}>{{ __('All Time (one-time milestone)') }}</option>
    </select>
    <div class="form-text">{{ __('Bonus is granted once per period when the driver crosses this threshold.') }}</div>
</div>
