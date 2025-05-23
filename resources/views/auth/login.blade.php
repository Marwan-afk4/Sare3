<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Login') }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            background: linear-gradient(to right, #f5f7fa, #c3cfe2);
        }

        .login-card {
            width: 100%;
            max-width: 400px;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.05);
            background-color: #fff;
        }

        .login-logo {
            width: 100%;
            height: auto;
            display: block;
            margin: 0 auto 1.5rem auto;
        }

        .form-control {
            border-radius: 8px;
        }

        h3 {
            font-weight: 600;
        }
    </style>
</head>
<body class="d-flex justify-content-center align-items-center vh-100">

    <div class="login-card text-center">
        <img src="{{ asset('logo/Layer_2_Image.png') }}" alt="Logo" class="login-logo">

        <h3 class="mb-3">{{ __('Login') }}</h3>

        @error('error')
            <div class="alert alert-danger">
                {{ $message }}
            </div>
        @enderror

        <form action="{{ route('do.login') }}" method="POST" class="text-start">
            @csrf
            <div class="mb-3">
                <label for="phone" class="form-label">{{ __('Phone') }}</label>
                <input type="text" name="phone" class="form-control" value="{{ old('phone') }}" autocomplete="off" required>
                @error('phone')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">{{ __('Password') }}</label>
                <input type="password" name="password" class="form-control" autocomplete="off" required>
                @error('password')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit" class="btn btn-primary w-100">{{ __('Login') }}</button>
        </form>
    </div>

</body>
</html>
