<div class="form-floating mb-3 {{ $required ? 'required' : '' }}">
    <select
        name="{{ $name }}"
        id="{{ $name }}"
        class="form-select @error($name) is-invalid @enderror"
        {{ $required ? 'required' : '' }}
        {{ $disabled ? 'disabled' : '' }}
        @foreach($attrs ?? [] as $attribute => $v)
            @if(is_numeric($attribute))
                {{ $v }}
            @else
                {{ $attribute }}="{{ $v }}"
            @endif
        @endforeach
        {{-- wire:model.change="{{ $name }}" --}}
    >
        <option value="" {{ !in_array(old($name, $selected), array_keys($options)) ? 'selected' : '' }}>--</option>
        @foreach ($options as $key => $value)
            <option value="{{ $key }}"
                {{ (old($name, $selected) == $key) ? 'selected' : '' }}
                {{ in_array($key, $disabledOptions) ? 'disabled' : '' }}
            >
                {{ $value }}
            </option>
        @endforeach
    </select>
    <label for="{{ $name }}">{{ $label }}</label>
    @error($name)
        <span class="text-danger">{{ $message }}</span>
    @enderror
</div>
