<div class="col-md-8">
    <div class="card mb-4 shadow-sm border-0">
        <div class="card-header bg-white border-bottom-0">
            <h5 class="mb-0">{{ __('Add Car Categories') }}</h5>
        </div>

        <div class="card-body pt-2">
            {{-- Form --}}
            <div class="row g-2 mb-3">
                <div class="col-3">
                    <x-form-select name="car_category_id" label="{{ __('Category') }}"
                        :options="$carCategories" :selected="$car_category_id"
                        :attrs="['wire:model.lazy' => 'car_category_id','class'=>'form-select form-select-sm']"/>
                </div>
                <div class="col-2">
                    <x-form-input name="base_price" type="number" label="{{ __('Base') }}"
                        :value="$base_price" :attrs="['min'=>'0','step'=>'0.01','wire:model.lazy'=>'base_price','class'=>'form-control form-control-sm']"/>
                </div>
                <div class="col-2">
                    <x-form-input name="price_per_km" type="number" label="{{ __('Per Km') }}"
                        :value="$price_per_km" :attrs="['min'=>'0','step'=>'0.01','wire:model.lazy'=>'price_per_km','class'=>'form-control form-control-sm']"/>
                </div>
                <div class="col-2">
                    <x-form-input name="price_per_min" type="number" label="{{ __('Per Min') }}"
                        :value="$price_per_min" :attrs="['min'=>'0','step'=>'0.01','wire:model.lazy'=>'price_per_min','class'=>'form-control form-control-sm']"/>
                </div>
                <div class="col-auto">
                    <button wire:click="saveCategory" class="btn btn-sm btn-success">
                        <i class="fa fa-save"></i>
                    </button>
                </div>
            </div>

            {{-- Table --}}
            @if ($zoneCategories->isEmpty())
                <p class="text-muted text-center">{{ __('No categories assigned.') }}</p>
            @else
                <table class="table table-sm table-hover align-middle text-center mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>{{ __('Category') }}</th>
                            <th>{{ __('Base Price') }}</th>
                            <th>{{ __('Per Km') }}</th>
                            <th>{{ __('Per Min') }}</th>
                            <th>{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($zoneCategories as $index => $cat)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <a href="{{ route('car-categories.show', $cat) }}">
                                        {{ $cat->name }}
                                    </a>
                                </td>
                                <td>{{ $cat->pivot->base_price ?? '-' }}</td>
                                <td>{{ $cat->pivot->price_per_km ?? '-' }}</td>
                                <td>{{ $cat->pivot->price_per_min ?? '-' }}</td>

                                <td>
                                    <button wire:click="deleteCategory({{ $cat->id }})"
                                        class="btn btn-sm btn-danger"
                                        wire:confirm="{{ __('Are you sure to remove this category from the zone?') }}">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    <script>
        document.addEventListener('categoryExists', function(event) {
            alert(event.detail.name + ' {{ __("is already assigned to this zone.") }}');
        });
    </script>
</div>
