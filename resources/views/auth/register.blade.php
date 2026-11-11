<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Register · LogiTrack</title>
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
                <h2>Build your capstone presence, <span>from day one.</span></h2>
                <p>Create your department account and start managing projects, approvals, and reviews in one place.</p>
                <ul class="lt-auth__points">
                    <li>Track student submissions and requirements</li>
                    <li>Review adviser and admin feedback</li>
                    <li>Keep capstone data searchable and organized</li>
                </ul>
            </div>
            <div class="lt-auth__legal">&copy; {{ date('Y') }} LogiTrack</div>
        </aside>

        <main class="lt-auth__main">
            <div class="lt-auth__card lt-auth__card--wide lt-animate">
                <div class="lt-auth__head">
                    <h1>Create your account</h1>
                    <p>Join LogiTrack to access your capstone workspace.</p>
                </div>

                <form method="POST" action="{{ route('register') }}" class="lt-form" data-submit-guard>
                    @csrf

                    @foreach([
                        ['student_number', 'Student Number', 'text', 'off'],
                        ['first_name', 'First Name', 'text', 'given-name'],
                        ['last_name', 'Last Name', 'text', 'family-name'],
                        ['email', 'School Email', 'email', 'username'],
                    ] as [$field, $label, $type, $autocomplete])
                        <div class="lt-field">
                            <label for="{{ $field }}" class="lt-label">{{ __($label) }}</label>
                            <input id="{{ $field }}" class="lt-input {{ $errors->has($field) ? 'is-invalid' : '' }}" type="{{ $type }}" name="{{ $field }}" value="{{ old($field) }}" required autocomplete="{{ $autocomplete }}">
                            @foreach($errors->get($field) as $message)
                                <p class="lt-field-error">{{ $message }}</p>
                            @endforeach
                        </div>
                    @endforeach

                    <div class="lt-field">
                        <label for="password" class="lt-label">{{ __('Create Password') }}</label>
                        <input id="password" class="lt-input {{ $errors->has('password') ? 'is-invalid' : '' }}" type="password" name="password" required autocomplete="new-password">
                        @foreach($errors->get('password') as $message)
                            <p class="lt-field-error">{{ $message }}</p>
                        @endforeach
                    </div>

                    <div class="lt-auth__row">
                        <a class="lt-link" href="{{ route('login') }}">{{ __('Already registered?') }}</a>
                    </div>

                    <button type="submit" class="lt-btn lt-btn--primary lt-btn--lg" style="width:100%;" data-loading-text="Creating account...">
                        {{ __('Register') }}
                    </button>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
