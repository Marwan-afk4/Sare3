<div class="form-floating mb-3 {{ $required ? 'required' : '' }}">
    <select name="{{ $name }}{{ $multiple ? '[]' : '' }}" id="{{ $name }}"
        class="form-select @error($name) is-invalid @enderror" {{ $required ? 'required' : '' }}
        {{ $disabled ? 'disabled' : '' }} {{ $multiple ? 'multiple' : '' }}
        @foreach ($attrs ?? [] as $attribute => $v)
            @if (is_numeric($attribute))
                {{ $v }}
            @else
                {{ $attribute }}="{{ $v }}"
            @endif @endforeach>
        @if (!$multiple)
            <option value="" {{ !in_array(old($name, $selected), array_keys($options)) ? 'selected' : '' }}>--
            </option>
        @endif

        @foreach ($options as $key => $value)
            <option value="{{ $key }}"
                @if ($multiple) {{ in_array($key, old($name, (array) $selected)) ? 'selected' : '' }}
                @else
                    {{ old($name, $selected) == $key ? 'selected' : '' }} @endif
                {{ in_array($key, $disabledOptions) ? 'disabled' : '' }}>
                {{ $value }}
            </option>
        @endforeach
    </select>
    <label for="{{ $name }}">{{ $label }}</label>
    @error($name)
        <span class="text-danger">{{ $message }}</span>
    @enderror
</div>
