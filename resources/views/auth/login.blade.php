<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Login') }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        .form-control {
            border-radius: 0;
        }
    </style>
</head>
<body class="d-flex justify-content-center align-items-center vh-100">
    <div class="card p-4 shadow-sm" style="width: 350px;">
        <h3 class="text-center">{{ __('Login') }}</h3>
        @error('error')
            <div class="alert alert-danger">
                {{ $message }}
            </div>
        @enderror

        <form action="{{ route('do.login') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="phone" class="form-label">{{ __('phone') }}</label>
                <input type="text" name="phone" class="form-control" value="{{ old('phone') }}"  autocomplete="off" required>
                @error('phone')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">{{ __('Password') }}</label>
                <input type="password" name="password" class="form-control" value="{{ old('password') }}"  autocomplete="off" required>
                @error('password')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>
            <button type="submit" class="btn btn-primary w-100">{{ __('Login') }}</button>
        </form>
    </div>

</body>
</html>
