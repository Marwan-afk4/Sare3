<div class="position-relative">
    <div class="form-floating mb-3">
        <input type="text"
               wire:model.live.debounce.300ms="query"
               wire:blur="validateSupplierOnBlur"
               id="supplier"
               class="form-control @if($showError) is-invalid @endif"
               autocomplete="off"
        />
        <label for="supplier">{{ __('Supplier') }}</label>
        @if ($showError)
            <div class="invalid-feedback">
                Please select a valid supplier from the list.
            </div>
        @endif
    </div>

    @if (!empty($suppliers))
        <ul class="list-group position-absolute w-100 shadow" style="z-index: 1000; max-height: 200px; overflow-y: auto;">
            @foreach($suppliers as $supplier)
                <li class="list-group-item list-group-item-action"
                    wire:click="selectSupplier({{ $supplier['id'] }})"
                    style="cursor: pointer;">
                    {{ $supplier['name'] }}
                </li>
            @endforeach
        </ul>
    @endif

    {{-- @if ($selectedSupplierId) --}}
        <input type="hidden" name="supplier_id" value="{{ $selectedSupplierId??'' }}">
    {{-- @endif --}}
</div>
