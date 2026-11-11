<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Verify registration · LogiTrack</title>
    @vite(['resources/css/logitrack.css', 'resources/js/logitrack.js'])
</head>
<body class="lt-body">
    <div class="lt-auth">
        <aside class="lt-auth__aside">
            <a href="{{ url('/') }}" class="lt-brand">
                <span class="lt-brand__mark">L</span>
                LogiTrack
            </a>
            <div class="lt-auth__pitch">
                <h2>One more step, <span>then you are ready.</span></h2>
                <p>Verify your email address to finish creating your student account.</p>
            </div>
            <div class="lt-auth__legal">&copy; {{ date('Y') }} LogiTrack</div>
        </aside>

        <main class="lt-auth__main">
            <div class="lt-auth__card lt-animate">
                <div class="lt-auth__head">
                    <h1>Enter your verification code</h1>
                    <p>We sent a six-digit code to <strong>{{ $email }}</strong>. It expires in 10 minutes.</p>
                </div>

                @if (session('status') === 'otp-resent')
                    <div class="lt-alert lt-alert--success" role="status">
                        <span class="lt-alert__icon">✓</span>
                        <div>A new verification code has been sent.</div>
                    </div>
                @endif

                <form method="POST" action="{{ route('register.otp.store') }}" class="lt-form" data-submit-guard>
                    @csrf
                    <div class="lt-field">
                        <label for="otp" class="lt-label">{{ __('Verification code') }}</label>
                        <input id="otp" class="lt-input {{ $errors->has('otp') ? 'is-invalid' : '' }}" type="text" name="otp" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" maxlength="6" required autofocus>
                        @foreach($errors->get('otp') as $message)
                            <p class="lt-field-error">{{ $message }}</p>
                        @endforeach
                        @foreach($errors->get('email') as $message)
                            <p class="lt-field-error">{{ $message }}</p>
                        @endforeach
                    </div>
                    <button type="submit" class="lt-btn lt-btn--primary lt-btn--lg" style="width:100%;" data-loading-text="Verifying...">
                        {{ __('Verify and create account') }}
                    </button>
                </form>

                <div class="lt-auth__row lt-mt">
                    <form method="POST" action="{{ route('register.otp.resend') }}" data-submit-guard>
                        @csrf
                        <button type="submit" class="lt-link lt-link-btn">Resend code</button>
                    </form>
                    <a class="lt-link" href="{{ route('register') }}">Start over</a>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
