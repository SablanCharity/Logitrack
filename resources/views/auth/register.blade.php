<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
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

                    <div class="lt-field">
                        <label for="first_name" class="lt-label">{{ __('First Name') }}</label>
                        <input id="first_name" class="lt-input {{ $errors->has('first_name') ? 'is-invalid' : '' }}" type="text" name="first_name" value="{{ old('first_name') }}" required autofocus autocomplete="given-name">
                        @foreach($errors->get('first_name') as $message)
                            <p class="lt-field-error">{{ $message }}</p>
                        @endforeach
                    </div>

                    <div class="lt-field">
                        <label for="last_name" class="lt-label">{{ __('Last Name') }}</label>
                        <input id="last_name" class="lt-input {{ $errors->has('last_name') ? 'is-invalid' : '' }}" type="text" name="last_name" value="{{ old('last_name') }}" required autocomplete="family-name">
                        @foreach($errors->get('last_name') as $message)
                            <p class="lt-field-error">{{ $message }}</p>
                        @endforeach
                    </div>

                    <div class="lt-field">
                        <label for="email" class="lt-label">{{ __('School Email') }}</label>
                        <input id="email" class="lt-input {{ $errors->has('email') ? 'is-invalid' : '' }}" type="email" name="email" value="{{ old('email') }}" required autocomplete="username">
                        @foreach($errors->get('email') as $message)
                            <p class="lt-field-error">{{ $message }}</p>
                        @endforeach
                    </div>

                    <div class="lt-field">
                        <label for="program" class="lt-label">{{ __('Program') }}</label>
                        <select id="program" name="program" class="lt-select {{ $errors->has('program') ? 'is-invalid' : '' }}" required>
                            <option value="">Select Program</option>
                            <option value="BSIT" {{ old('program') === 'BSIT' ? 'selected' : '' }}>BSIT</option>
                            <option value="BSCS" {{ old('program') === 'BSCS' ? 'selected' : '' }}>BSCS</option>
                        </select>
                        @foreach($errors->get('program') as $message)
                            <p class="lt-field-error">{{ $message }}</p>
                        @endforeach
                    </div>

                    <div class="lt-field">
                        <label for="year_section" class="lt-label">{{ __('Year & Section') }}</label>
                        <input id="year_section" class="lt-input {{ $errors->has('year_section') ? 'is-invalid' : '' }}" type="text" name="year_section" value="{{ old('year_section') }}" placeholder="Example: 4-D" required>
                        @foreach($errors->get('year_section') as $message)
                            <p class="lt-field-error">{{ $message }}</p>
                        @endforeach
                    </div>

                    <div class="lt-field">
                        <label for="instructor_id" class="lt-label">{{ __('Instructor / Adviser') }}</label>
                        <select id="instructor_id" name="instructor_id" class="lt-select {{ $errors->has('instructor_id') ? 'is-invalid' : '' }}" required>
                            <option value="">-- Select Instructor / Adviser --</option>

                            @foreach($instructors as $instructor)
                                <option value="{{ $instructor->id }}" {{ old('instructor_id') == $instructor->id ? 'selected' : '' }}>
                                    {{ $instructor->first_name }} {{ $instructor->last_name }}
                                </option>
                            @endforeach
                        </select>
                        @foreach($errors->get('instructor_id') as $message)
                            <p class="lt-field-error">{{ $message }}</p>
                        @endforeach
                    </div>

                    <div class="lt-field">
                        <label for="class_code" class="lt-label">{{ __('Class Code') }}</label>
                        <input id="class_code" class="lt-input {{ $errors->has('class_code') ? 'is-invalid' : '' }}" type="text" name="class_code" value="{{ old('class_code') }}" required>
                        @foreach($errors->get('class_code') as $message)
                            <p class="lt-field-error">{{ $message }}</p>
                        @endforeach
                    </div>

                    <div class="lt-field">
                        <label for="password" class="lt-label">{{ __('Password') }}</label>
                        <div class="lt-input-wrap">
                            <input id="password" class="lt-input {{ $errors->has('password') ? 'is-invalid' : '' }}" type="password" name="password" required autocomplete="new-password" data-strength="#strength">
                            <button type="button" class="lt-eye" data-toggle-password="#password" aria-pressed="false" aria-label="Show password">
                                <svg class="lt-eye__on" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
                                <svg class="lt-eye__off" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3l18 18"/><path d="M10.6 5.1A10.7 10.7 0 0 1 12 5c6.4 0 10 7 10 7a17 17 0 0 1-3.2 4.2M6.6 6.6A17 17 0 0 0 2 12s3.6 7 10 7a10 10 0 0 0 4.4-1"/><path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/></svg>
                            </button>
                        </div>
                        @foreach($errors->get('password') as $message)
                            <p class="lt-field-error">{{ $message }}</p>
                        @endforeach
                    </div>

                    <div class="lt-field">
                        <label for="password_confirmation" class="lt-label">{{ __('Confirm Password') }}</label>
                        <div class="lt-input-wrap">
                            <input id="password_confirmation" class="lt-input {{ $errors->has('password_confirmation') ? 'is-invalid' : '' }}" type="password" name="password_confirmation" required autocomplete="new-password" data-match="#password" data-match-msg="#match-msg">
                            <button type="button" class="lt-eye" data-toggle-password="#password_confirmation" aria-pressed="false" aria-label="Show password">
                                <svg class="lt-eye__on" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
                                <svg class="lt-eye__off" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3l18 18"/><path d="M10.6 5.1A10.7 10.7 0 0 1 12 5c6.4 0 10 7 10 7a17 17 0 0 1-3.2 4.2M6.6 6.6A17 17 0 0 0 2 12s3.6 7 10 7a10 10 0 0 0 4.4-1"/><path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/></svg>
                            </button>
                        </div>
                        <div id="match-msg" class="lt-match" aria-live="polite"></div>
                        @foreach($errors->get('password_confirmation') as $message)
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
