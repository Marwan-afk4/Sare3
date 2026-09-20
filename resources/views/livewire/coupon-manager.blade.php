<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">{{ __('Coupon Management') }}</h3>
                    <button wire:click="openModal" class="btn btn-primary">
                        <i class="fas fa-plus"></i> {{ __('Add New Coupon') }}
                    </button>
                </div>

                <div class="card-body">
                    <!-- Filters -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <input wire:model.live="search" type="text" class="form-control"
                                placeholder="Search coupons...">
                        </div>
                        <div class="col-md-3">
                            <select wire:model.live="statusFilter" class="form-control">
                                <option value="">{{ __('All Status') }}</option>
                                <option value="active">{{ __('Active') }}</option>
                                <option value="inactive">{{ __('Inactive') }}</option>
                                <option value="expired">{{ __('Expired') }}</option>
                            </select>
                        </div>
                    </div>

                    <!-- Flash Messages -->
                    @if (session()->has('message'))
                        <div class="alert alert-success alert-dismissible fade show">
                            {{ session('message') }}
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                        </div>
                    @endif

                    @if (session()->has('error'))
                        <div class="alert alert-danger alert-dismissible fade show">
                            {{ session('error') }}
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                        </div>
                    @endif

                    <!-- Coupons Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>{{ __('Code') }}</th>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Type') }}</th>
                                    <th>{{ __('Value') }}</th>
                                    <th>{{ __('Usage') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Expires At') }}</th>
                                    <th>{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($coupons as $coupon)
                                    <tr>
                                        <td><code>{{ $coupon->code }}</code></td>
                                        <td>{{ $coupon->name }}</td>
                                        <td>
                                            <span
                                                class="badge badge-{{ $coupon->type === 'percentage' ? 'info' : 'warning' }}">
                                                {{ ucfirst($coupon->type) }}
                                            </span>
                                        </td>
                                        <td>
                                            {{ $coupon->type === 'percentage' ? $coupon->value . '%' : '$' . $coupon->value }}
                                        </td>
                                        <td>
                                            {{ $coupon->usages_count }}
                                            @if ($coupon->usage_limit)
                                                / {{ $coupon->usage_limit }}
                                            @endif
                                        </td>
                                        <td>
                                            @if ($coupon->expires_at < now())
                                                <span class="badge badge-danger">{{ __('Expired') }}</span>
                                            @elseif($coupon->is_active)
                                                <span class="badge badge-success">{{ __('Active') }}</span>
                                            @else
                                                <span class="badge badge-secondary">{{ __('Inactive') }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $coupon->expires_at->format('M d, Y H:i') }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button wire:click="editCoupon({{ $coupon->id }})"
                                                    class="btn btn-sm btn-info">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button wire:click="toggleStatus({{ $coupon->id }})"
                                                    class="btn btn-sm {{ $coupon->is_active ? 'btn-success' : 'btn-secondary' }}"
                                                    title="{{ $coupon->is_active ? __('Click to deactivate') : __('Click to activate') }}">
                                                    {{ $coupon->is_active ? __('Active') : __('Inactive') }}
                                                </button>
                                                @if ($coupon->usages_count)
                                                    <button type="button" class="btn btn-sm btn-secondary" disabled
                                                        title="{{ __('Cannot delete a coupon that has been used.') }}">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                @else
                                                    <button type="button"
                                                        wire:click="deleteCoupon({{ $coupon->id }})"
                                                        wire:confirm="{{ __('Are you sure you want to delete this coupon?') }}"
                                                        class="btn btn-sm btn-danger"
                                                        title="{{ __('Delete') }}">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">{{ __('No coupons found') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    {{ $coupons->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    @if ($showModal)
        <div class="modal fade show" style="display: block;" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $editMode ? 'Edit' : 'Add New' }} {{ __('Coupon') }}</h5>
                        <button wire:click="closeModal" type="button" class="close">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="saveCoupon">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{ __('Coupon Code') }}</label>
                                        <input wire:model="code" type="text"
                                            class="form-control @error('code') is-invalid @enderror"
                                            placeholder="e.g., SAVE20">
                                        @error('code')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{ __('Coupon Name') }}</label>
                                        <input wire:model="name" type="text"
                                            class="form-control @error('name') is-invalid @enderror"
                                            placeholder="e.g., 20% Off Rides">
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>{{ __('Description') }}</label>
                                <textarea wire:model="description" class="form-control" rows="2" placeholder="Optional description"></textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{ __('Discount Type') }}</label>
                                        <select wire:model="type"
                                            class="form-control @error('type') is-invalid @enderror">
                                            <option value="percentage">{{ __('Percentage') }}</option>
                                            <option value="fixed">{{ __('Fixed Amount') }}</option>
                                        </select>
                                        @error('type')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{ __('Discount Value') }}</label>
                                        <input wire:model="value" type="number" step="0.01"
                                            class="form-control @error('value') is-invalid @enderror"
                                            placeholder="{{ $type === 'percentage' ? 'e.g., 20' : 'e.g., 5.00' }}">
                                        @error('value')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{ __('Minimum Ride Amount') }}</label>
                                        <input wire:model="minimum_ride_amount" type="number" step="0.01"
                                            class="form-control" placeholder="Optional">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{ __('Maximum Discount') }}</label>
                                        <input wire:model="maximum_discount" type="number" step="0.01"
                                            class="form-control" placeholder="Optional">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{ __('Total Usage Limit') }}</label>
                                        <input wire:model="usage_limit" type="number" class="form-control"
                                            placeholder="Leave empty for unlimited">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{ __('Per User Limit') }}</label>
                                        <input wire:model="user_usage_limit" type="number"
                                            class="form-control @error('user_usage_limit') is-invalid @enderror"
                                            min="1" value="1">
                                        @error('user_usage_limit')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{ __('Start Date') }}</label>
                                        <input wire:model="starts_at" type="datetime-local"
                                            class="form-control @error('starts_at') is-invalid @enderror">
                                        @error('starts_at')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{ __('End Date') }}</label>
                                        <input wire:model="expires_at" type="datetime-local"
                                            class="form-control @error('expires_at') is-invalid @enderror">
                                        @error('expires_at')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="form-check">
                                    <input wire:model="is_active" type="checkbox" class="form-check-input"
                                        id="is_active">
                                    <label class="form-check-label" for="is_active">
                                        {{ __('Active') }}
                                    </label>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button wire:click="closeModal" type="button" class="btn btn-secondary">{{ __('Cancel') }}</button>
                        <button wire:click="saveCoupon" type="button" class="btn btn-primary">
                            {{ $editMode ? 'Update' : 'Create' }} {{ __('Coupon') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show"></div>
    @endif
</div>
