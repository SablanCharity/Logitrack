<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Reset Password · LogiTrack</title>
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

                <div class="lt-auth__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="15" r="4"/><path d="m11 12 9-9M16 7l3 3"/></svg></div>

                <div class="lt-auth__head">
                    <h1>Reset your password</h1>
                    <p>Choose a new password for your account.</p>
                </div>

                <form method="POST" action="{{ route('password.store') }}" class="lt-form" data-submit-guard>
                    @csrf

                    <!-- Password Reset Token -->
                    <input type="hidden" name="token" value="{{ $request->route('token') }}">

                    <div class="lt-field">
                        <label for="email" class="lt-label">{{ __('Email') }}</label>
                        <input id="email" class="lt-input {{ $errors->has('email') ? 'is-invalid' : '' }}" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username">
                        @foreach($errors->get('email') as $message)
                            <p class="lt-field-error">{{ $message }}</p>
                        @endforeach
                    </div>
                    <div class="lt-field">
                        <label for="password" class="lt-label">{{ __('Password') }}</label>
                        <div class="lt-input-wrap">
                            <input id="password" class="lt-input {{ $errors->has('password') ? 'is-invalid' : '' }}" type="password" name="password" required autocomplete="new-password" data-strength="#strength">
                            <button type="button" class="lt-eye" data-toggle-password="#password_confirmation" aria-pressed="false" aria-label="Show password"><svg class="lt-eye__on" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg><svg class="lt-eye__off" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3l18 18"/><path d="M10.6 5.1A10.7 10.7 0 0 1 12 5c6.4 0 10 7 10 7a17 17 0 0 1-3.2 4.2M6.6 6.6A17 17 0 0 0 2 12s3.6 7 10 7a10 10 0 0 0 4.4-1"/><path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/></svg></button>
                        </div>
                        <div class="lt-strength" id="strength" data-level="0">
                            <div class="lt-strength__bars"><span class="lt-strength__bar"></span><span class="lt-strength__bar"></span><span class="lt-strength__bar"></span><span class="lt-strength__bar"></span></div>
                            <span class="lt-strength__label">Use at least 8 characters</span>
                        </div>
                        @foreach($errors->get('password') as $message)
                            <p class="lt-field-error">{{ $message }}</p>
                        @endforeach
                    </div>
                    <div class="lt-field">
                        <label for="password_confirmation" class="lt-label">{{ __('Confirm Password') }}</label>
                        <div class="lt-input-wrap">
                            <input id="password_confirmation" class="lt-input {{ $errors->has('password_confirmation') ? 'is-invalid' : '' }}" type="password" name="password_confirmation" required autocomplete="new-password" data-match="#password" data-match-msg="#match-msg">
                            <button type="button" class="lt-eye" data-toggle-password="#password_confirmation" aria-pressed="false" aria-label="Show password"><svg class="lt-eye__on" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg><svg class="lt-eye__off" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3l18 18"/><path d="M10.6 5.1A10.7 10.7 0 0 1 12 5c6.4 0 10 7 10 7a17 17 0 0 1-3.2 4.2M6.6 6.6A17 17 0 0 0 2 12s3.6 7 10 7a10 10 0 0 0 4.4-1"/><path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/></svg></button>
                        </div>
                        <span id="match-msg" class="lt-match" aria-live="polite"></span>
                        @foreach($errors->get('password_confirmation') as $message)
                            <p class="lt-field-error">{{ $message }}</p>
                        @endforeach
                    </div>

                    <button type="submit" class="lt-btn lt-btn--primary lt-btn--lg" style="width:100%;" data-loading-text="Resetting...">
                        {{ __('Reset Password') }}
                    </button>

                </form>

            </div>

        </main>

    </div>

</body>
</html>