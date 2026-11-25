<div class="col-12 col-xl-8">
    <div class="card mb-4 shadow-sm border-0">
        <div class="card-header bg-white border-bottom-0">
            <h5 class="mb-0">
                {{ $editingCategoryId ? __('Edit Car Category Prices') : __('Add Car Categories') }}
            </h5>
        </div>

        <div class="card-body pt-2">
            {{-- Form --}}
            <div class="row g-2 mb-3 align-items-start" wire:key="zone-category-form-{{ $editingCategoryId ?? 'new' }}">
                <div class="col-12 col-md-6 col-xl-3">
                    <x-form-select name="car_category_id" label="{{ __('Category') }}" :options="$carCategories" :selected="$car_category_id"
                        :disabled="$editingCategoryId !== null"
                        :attrs="['wire:model.lazy' => 'car_category_id', 'class' => 'form-select']" />
                </div>
                <div class="col-6 col-xl-2">
                    <x-form-input name="base_price" type="number" label="{{ __('Base') }}" :value="$base_price"
                        :attrs="[
                            'min' => '0',
                            'step' => '0.01',
                            'wire:model.lazy' => 'base_price',
                            'class' => 'form-control',
                        ]" />
                </div>
                <div class="col-6 col-xl-2">
                    <x-form-input name="price_per_km" type="number" label="{{ __('Per Km') }}" :value="$price_per_km"
                        :attrs="[
                            'min' => '0',
                            'step' => '0.01',
                            'wire:model.lazy' => 'price_per_km',
                            'class' => 'form-control',
                        ]" />
                </div>
                <div class="col-6 col-xl-2">
                    <x-form-input name="price_per_min" type="number" label="{{ __('Per Min') }}" :value="$price_per_min"
                        :attrs="[
                            'min' => '0',
                            'step' => '0.01',
                            'wire:model.lazy' => 'price_per_min',
                            'class' => 'form-control',
                        ]" />
                </div>
                <div class="col-6 col-xl-2">
                    <x-form-input name="min_price" type="number" label="{{ __('Min Fare') }}" :value="$min_price" :attrs="[
                        'min' => '0',
                        'step' => '0.01',
                        'wire:model.lazy' => 'min_price',
                        'class' => 'form-control',
                    ]" />
                </div>
                <div class="col-12 col-xl-auto">
                    <div class="zone-price-actions d-flex gap-2">
                        <button type="button" wire:click="saveCategory"
                            class="btn {{ $editingCategoryId ? 'btn-warning' : 'btn-success' }}"
                            title="{{ $editingCategoryId ? __('Update') : __('Save') }}">
                            <i class="fa {{ $editingCategoryId ? 'fa-check' : 'fa-save' }}"></i>
                            <span class="d-inline d-xl-none ms-1">{{ $editingCategoryId ? __('Update') : __('Save') }}</span>
                        </button>
                        @if ($editingCategoryId)
                            <button type="button" wire:click="cancelEdit" class="btn btn-secondary" title="{{ __('Cancel') }}">
                                <i class="fa fa-times"></i>
                                <span class="d-inline d-xl-none ms-1">{{ __('Cancel') }}</span>
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Prices --}}
            @if ($zoneCategories->isEmpty())
                <p class="text-muted text-center">{{ __('No categories assigned.') }}</p>
            @else
                <div class="d-md-none">
                    @foreach ($zoneCategories as $cat)
                        <div @class([
                            'zone-price-card border rounded p-3 mb-2',
                            'border-warning bg-warning-subtle' => (int) $editingCategoryId === (int) $cat->id,
                        ])>
                            <div class="d-flex justify-content-between align-items-start gap-2">
                                <a href="{{ route('car-categories.show', $cat) }}" class="fw-semibold text-break">
                                    {{ $cat->name }}
                                </a>
                                <div class="d-flex gap-2 flex-shrink-0">
                                    <button type="button" wire:click="editCategory({{ $cat->id }})"
                                        class="btn btn-warning" title="{{ __('Edit') }}">
                                        <i class="fa fa-edit"></i>
                                    </button>
                                    <button type="button" wire:click="deleteCategory({{ $cat->id }})"
                                        class="btn btn-danger"
                                        title="{{ __('Delete') }}"
                                        wire:confirm="{{ __('Are you sure to remove this category from the zone?') }}">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="row g-2 mt-2 small">
                                <div class="col-6">
                                    <div class="text-muted">{{ __('Base Price') }}</div>
                                    <div>{{ $cat->pivot->base_price ?? '-' }}</div>
                                </div>
                                <div class="col-6">
                                    <div class="text-muted">{{ __('Per Km') }}</div>
                                    <div>{{ $cat->pivot->price_per_km ?? '-' }}</div>
                                </div>
                                <div class="col-6">
                                    <div class="text-muted">{{ __('Per Min') }}</div>
                                    <div>{{ $cat->pivot->price_per_min ?? '-' }}</div>
                                </div>
                                <div class="col-6">
                                    <div class="text-muted">{{ __('Min Price') }}</div>
                                    <div>{{ $cat->pivot->min_price ?? '-' }}</div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="d-none d-md-block table-responsive">
                    <table class="table table-sm table-hover align-middle text-center mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>{{ __('Category') }}</th>
                                <th>{{ __('Base Price') }}</th>
                                <th>{{ __('Per Km') }}</th>
                                <th>{{ __('Per Min') }}</th>
                                <th>{{ __('Min Price') }}</th>
                                <th>{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($zoneCategories as $index => $cat)
                                <tr @class(['table-warning' => (int) $editingCategoryId === (int) $cat->id])>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <a href="{{ route('car-categories.show', $cat) }}">
                                            {{ $cat->name }}
                                        </a>
                                    </td>
                                    <td>{{ $cat->pivot->base_price ?? '-' }}</td>
                                    <td>{{ $cat->pivot->price_per_km ?? '-' }}</td>
                                    <td>{{ $cat->pivot->price_per_min ?? '-' }}</td>
                                    <td>{{ $cat->pivot->min_price ?? '-' }}</td>
                                    <td class="text-nowrap">
                                        <button type="button" wire:click="editCategory({{ $cat->id }})"
                                            class="btn btn-sm btn-warning me-1" title="{{ __('Edit') }}">
                                            <i class="fa fa-edit"></i>
                                        </button>
                                        <button type="button" wire:click="deleteCategory({{ $cat->id }})"
                                            class="btn btn-sm btn-danger"
                                            title="{{ __('Delete') }}"
                                            wire:confirm="{{ __('Are you sure to remove this category from the zone?') }}">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <style>
        .zone-price-actions .btn {
            min-height: 44px;
            min-width: 44px;
        }

        .zone-price-card .btn {
            min-height: 44px;
            min-width: 44px;
        }

        @media (min-width: 1200px) {
            .zone-price-actions {
                padding-top: 0.15rem;
            }

            .zone-price-actions .btn {
                min-height: 38px;
                min-width: 38px;
            }
        }
    </style>

    <script>
        document.addEventListener('categoryExists', function(event) {
            alert(event.detail.name + ' {{ __('is already assigned to this zone.') }}');
        });
    </script>
</div>
