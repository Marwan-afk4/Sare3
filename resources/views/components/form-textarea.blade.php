<div class="form-floating mb-3 {{ $required ? 'required' : '' }}">
    <textarea
        name="{{ $name }}"
        placeholder="{{ $label }}"
        id="{{ $name }}"
        class="form-control @error($name) is-invalid @enderror"
        style="min-height: 100px"
        {{ $required ? 'required' : '' }}
        @foreach($attrs ?? [] as $attribute => $v)
            @if(is_numeric($attribute))
                {{ $v }}
            @else
                {{ $attribute }}="{{ $v }}"
            @endif
        @endforeach
    >{{ old($name, $value) }}</textarea>
    <label for="{{ $name }}">{{ $label }}</label>

    @error($name)
        <span class="text-danger">{{ $message }}</span>
    @enderror
</div>
