{{-- <form method="POST" action="{{ route('post.password.confirm') }}">
    @csrf

    <div>
        <label>Password</label>
        <input type="password" name="password" required>
    </div>

    <button type="submit">Confirm</button>
</form> --}}
<!DOCTYPE html>
<html lang="en-US" dir="rtl" data-navigation-type="default" data-navbar-horizontal-shape="default">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- ===============================================-->
    <!--    Document Title-->
    <!-- ===============================================-->
    <title>{{ config('app.name') }} - @yield('title')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- ===============================================-->
    <!--    Favicons-->
    <!-- ===============================================-->
    <link rel="apple-touch-icon" sizes="180x180" href="/phoenix/assets/img/favicons/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/phoenix/assets/img/favicons/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/phoenix/assets/img/favicons/favicon-16x16.png">
    <link rel="shortcut icon" type="image/x-icon" href="/phoenix/assets/img/favicons/favicon.ico">
    <link rel="manifest" href="/phoenix/assets/img/favicons/manifest.json">
    <meta name="msapplication-TileImage" content="assets/img/favicons/mstile-150x150.png">
    <meta name="theme-color" content="#ffffff">
    <script src="/phoenix/vendors/simplebar/simplebar.min.js"></script>
    <script src="/phoenix/assets/js/config.js"></script>

    <!-- ===============================================-->
    <!--    Stylesheets-->
    <!-- ===============================================-->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="">
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@300;400;600;700;800;900&amp;display=swap"
        rel="stylesheet">
    <link href="/phoenix/vendors/simplebar/simplebar.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.8/css/line.css">
    <link href="/phoenix/assets/css/theme-rtl.min.css" type="text/css" rel="stylesheet" id="style-rtl">
    <link href="/phoenix/assets/css/theme.min.css" type="text/css" rel="stylesheet" id="style-default" disabled>
    <link href="/phoenix/assets/css/user-rtl.min.css" type="text/css" rel="stylesheet" id="user-style-rtl">
    <link href="/phoenix/assets/css/user.min.css" type="text/css" rel="stylesheet" id="user-style-default" disabled>
    {{-- <script>
      var phoenixIsRTL = window.config.config.phoenixIsRTL;
      if (phoenixIsRTL) {
        var linkDefault = document.getElementById('style-default');
        var userLinkDefault = document.getElementById('user-style-default');
        linkDefault.setAttribute('disabled', true);
        userLinkDefault.setAttribute('disabled', true);
        document.querySelector('html').setAttribute('dir', 'rtl');
      } else {
        var linkRTL = document.getElementById('style-rtl');
        var userLinkRTL = document.getElementById('user-style-rtl');
        linkRTL.setAttribute('disabled', true);
        userLinkRTL.setAttribute('disabled', true);
      }
    </script> --}}
    <style>
      @font-face {
          font-family: "CairoRegular";
          src: url("{{ asset('fonts/Cairo-Regular.ttf') }}");
      }

      body, h1, h2, h3, h4, h5, h6, .h1, .h2, .h3, .h4, .h5, .h6, input, select,label, textarea,* {
          font-family: "Tajawal"!important;
      }

      .btn {
        font-weight: 500;
      }

      .required > label:after {
            content: "*"!important;
            color: red;
            font-size: 1.1em;
        }
    </style>
</head>

<body>
    <!-- ===============================================-->
    <!--    Main Content-->
    <!-- ===============================================-->
    <main class="main" id="top">
        <div class="container-fluid bg-body-tertiary dark__bg-gray-1200">
            <div class="bg-holder bg-auth-card-overlay" style="background-image:url(/phoenix/assets/img/bg/37.png);">
            </div>
            <!--/.bg-holder-->
            <div class="row flex-center position-relative min-vh-100 g-0 py-5">
                <div class="col-11 col-sm-10 col-xl-8">
                    <div class="card border border-translucent auth-card">
                        <div class="card-body pe-md-0">
                            <div class="row align-items-center gx-0 gy-7">
                                <div
                                    class="col-auto bg-body-highlight dark__bg-gray-1100 rounded-3 position-relative overflow-hidden auth-title-box">
                                    <div class="bg-holder" style="background-image:url(/phoenix/assets/img/bg/38.png);">
                                    </div>
                                    <!--/.bg-holder-->
                                    <div
                                        class="position-relative px-4 px-lg-7 pt-7 pb-7 pb-sm-5 text-center text-md-start pb-lg-7">
                                        <h3 class="mb-3 text-body-emphasis fs-7">{{ config('app.name') }}</h3>
                                        <p class="text-body-tertiary">{{__('You try to access a page that requires authentication')}}</p>
                                        {{-- <ul class="list-unstyled mb-0 w-max-content w-md-auto">
                                            <li class="d-flex align-items-center"><span
                                                    class="uil uil-check-circle text-success me-2"></span><span
                                                    class="text-body-tertiary fw-semibold">Fast</span></li>
                                        </ul> --}}
                                    </div>
                                    <div class="position-relative z-n1 mb-6 d-none d-md-block text-center mt-md-15"><img
                                            class="auth-title-box-img d-dark-none"
                                            src="/phoenix/assets/img/spot-illustrations/auth.png" alt="" /><img
                                            class="auth-title-box-img d-light-none"
                                            src="/phoenix/assets/img/spot-illustrations/auth-dark.png" alt="" />
                                    </div>
                                </div>
                                <div class="col mx-auto">
                                    <div class="auth-form-box">
                                        <div class="text-center mb-5">
                                            <div class="avatar avatar-4xl mb-4"><img class="rounded-circle"
                                                    src="/phoenix/assets/img/team/30.webp"
                                                    alt="" /></div>
                                            <h2 class="text-body-highlight"> <span class="fw-normal">{{ __('Welcome') }} </span>{{ Auth::user()->name }}</h2>
                                            <p class="text-body-tertiary">{{ __('Please confirm your password before continuing.') }}</p>
                                        </div>
                                        <form method="POST" action="{{ route('post.password.confirm') }}" class="needs-validation" novalidate>
                                            @csrf
                                            <div class="position-relative" data-password="data-password">
                                                <input class="form-control mb-3" id="password" type="password"
                                                    name="password"
                                                    placeholder="{{ __('Password') }}"
                                                    required
                                                    data-password-input="data-password-input"
                                                />
                                                @error('password')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                                    <button
                                                        class="btn px-3 py-0 h-100 position-absolute top-0 end-0 fs-7 text-body-tertiary"
                                                        data-password-toggle="data-password-toggle"><span
                                                        class="uil uil-eye show"></span><span
                                                        class="uil uil-eye-slash hide"></span>
                                                    </button>
                                            </div>
                                            <button type="submit" class="btn btn-primary w-100">{{ __('Confirm') }}</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </main><!-- ===============================================-->


    <script src="/phoenix/vendors/popper/popper.min.js"></script>
    <script src="/phoenix/vendors/bootstrap/bootstrap.min.js"></script>
    <script src="/phoenix/vendors/anchorjs/anchor.min.js"></script>
    <script src="/phoenix/vendors/is/is.min.js"></script>
    <script src="/phoenix/vendors/fontawesome/all.min.js"></script>
    <script src="/phoenix/vendors/lodash/lodash.min.js"></script>
    <script src="/phoenix/vendors/list.js/list.min.js"></script>
    <script src="/phoenix/vendors/feather-icons/feather.min.js"></script>
    <script src="/phoenix/vendors/dayjs/dayjs.min.js"></script>
    <script src="/phoenix/assets/js/phoenix.js"></script>
</body>

</html>
