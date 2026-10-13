<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Log in · LogiTrack</title>
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

                <div class="lt-auth__head">
                    <h1>Welcome back</h1>
                    <p>Log in to your LogiTrack account.</p>
                </div>

            @if (session('status'))
                <div class="lt-alert lt-alert--success" role="status">
                    <span class="lt-alert__icon">✓</span>
                    <div>{{ session('status') }}</div>
                </div>
            @endif

                <form method="POST" action="{{ route('login') }}" class="lt-form" data-submit-guard>
                    @csrf

                    <div class="lt-field">
                        <label for="email" class="lt-label">{{ __('Email') }}</label>
                        <input id="email" class="lt-input {{ $errors->has('email') ? 'is-invalid' : '' }}" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
                        @foreach($errors->get('email') as $message)
                            <p class="lt-field-error">{{ $message }}</p>
                        @endforeach
                    </div>
                    <div class="lt-field">
                        <label for="password" class="lt-label">{{ __('Password') }}</label>
                        <div class="lt-input-wrap">
                            <input id="password" class="lt-input {{ $errors->has('password') ? 'is-invalid' : '' }}" type="password" name="password" required autocomplete="current-password">
                            <button type="button" class="lt-eye" data-toggle-password="#password" aria-pressed="false" aria-label="Show password"><svg class="lt-eye__on" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg><svg class="lt-eye__off" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3l18 18"/><path d="M10.6 5.1A10.7 10.7 0 0 1 12 5c6.4 0 10 7 10 7a17 17 0 0 1-3.2 4.2M6.6 6.6A17 17 0 0 0 2 12s3.6 7 10 7a10 10 0 0 0 4.4-1"/><path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/></svg></button>
                        </div>
                        @foreach($errors->get('password') as $message)
                            <p class="lt-field-error">{{ $message }}</p>
                        @endforeach
                    </div>

                    <div class="lt-auth__row">
                        <label for="remember_me" class="lt-check">
                            <input id="remember_me" type="checkbox" name="remember">
                            <span>{{ __('Remember me') }}</span>
                        </label>

                        @if (Route::has('password.request'))
                            <a class="lt-link" href="{{ route('password.request') }}">
                                {{ __('Forgot your password?') }}
                            </a>
                        @endif
                    </div>

                    <button type="submit" class="lt-btn lt-btn--primary lt-btn--lg" style="width:100%;" data-loading-text="Logging in...">
                        {{ __('Log in') }}
                    </button>

                </form>

                @if (Route::has('register'))
                    <p class="lt-auth__foot">
                        New here?
                        <a class="lt-link" href="{{ route('register') }}">Create an account</a>
                    </p>
                @endif

            </div>

        </main>

    </div>

</body>
</html>