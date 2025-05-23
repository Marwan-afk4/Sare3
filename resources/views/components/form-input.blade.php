<div class="form-floating mb-3 {{ ($required ?? false) ? 'required' : '' }}">
    <input
        type="{{ $type ?? 'text' }}"
        name="{{ $name }}"
        id="{{ $name }}"
        placeholder="{{ $label }}"
        class="form-control @error($name) is-invalid @enderror"
        value="{{ old($name, $value ?? '') }}"
        {{ ($required ?? false) ? 'required' : '' }}
        {{ ($disabled ?? false) ? 'disabled' : '' }}
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
