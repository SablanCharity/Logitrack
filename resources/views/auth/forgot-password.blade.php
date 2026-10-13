<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Forgot Password · LogiTrack</title>
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
                <h2>Every capstone, <span>one searchable home.</span></h2>
                <p>The capstone repository and decision support system for your department.</p>

                <ul class="lt-auth__points">
                    <li>Search approved capstone projects</li>
                    <li>Check a proposal against existing studies</li>
                    <li>Reviewed by your adviser and the admin</li>
                </ul>
            </div>

            <div class="lt-auth__legal">&copy; {{ date('Y') }} LogiTrack</div>
        </aside>

        <main class="lt-auth__main">
            <div class="lt-auth__card lt-animate">

                <div class="lt-auth__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg></div>

                <div class="lt-auth__head">
                    <h1>Forgot your password?</h1>
                    <p>{{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}</p>
                </div>

            @if (session('status'))
                <div class="lt-alert lt-alert--success" role="status">
                    <span class="lt-alert__icon">✓</span>
                    <div>{{ session('status') }}</div>
                </div>
            @endif

                <form method="POST" action="{{ route('password.email') }}" class="lt-form" data-submit-guard>
                    @csrf

                    <div class="lt-field">
                        <label for="email" class="lt-label">{{ __('Email') }}</label>
                        <input id="email" class="lt-input {{ $errors->has('email') ? 'is-invalid' : '' }}" type="email" name="email" value="{{ old('email') }}" required autofocus>
                        @foreach($errors->get('email') as $message)
                            <p class="lt-field-error">{{ $message }}</p>
                        @endforeach
                    </div>

                    <button type="submit" class="lt-btn lt-btn--primary lt-btn--lg" style="width:100%;" data-loading-text="Sending...">
                        {{ __('Email Password Reset Link') }}
                    </button>

                </form>

                <p class="lt-auth__foot">
                    <a class="lt-link" href="{{ route('login') }}">← Back to log in</a>
                </p>

            </div>

        </main>

    </div>

</body>
</html>