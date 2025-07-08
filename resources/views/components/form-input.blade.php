<div class="{{ $divClass }} {{ $required ? 'required' : '' }}">
    <input
        type="{{ $type }}"
        name="{{ $name }}"
        value="{{ old($name, $value) }}"
        id="{{ $name }}"
        placeholder="{{ $label }}"
        class="{{ $class }} @error($name) is-invalid @enderror"
        {{ $required ? 'required' : '' }}
        {{ $disabled ? 'disabled' : '' }}
        @foreach($attrs ?? [] as $attribute => $v)
            @if(is_numeric($attribute))
                {{ $v }}
            @else
                {{ $attribute }}="{{ $v }}"
            @endif
        @endforeach
    >
    <label for="{{ $name }}">{{ $label }}</label>

    @error($name)
        <span class="text-danger">{{ $message }}</span>
    @enderror
</div>
