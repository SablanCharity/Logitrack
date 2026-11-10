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
            <a href="{{ url('/') }}" class="lt-brand"><span class="lt-brand__mark">L</span>LogiTrack</a>
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
                        <label for="student_number" class="lt-label">Student Number</label>
                        <input id="student_number" class="lt-input" type="text" name="student_number" value="{{ old('student_number') }}" required autofocus autocomplete="off">
                        @error('student_number')<p class="lt-field-error">{{ $message }}</p>@enderror
                    </div>
                    <div class="lt-field">
                        <label for="program" class="lt-label">Program</label>
                        <select id="program" class="lt-select" name="program" required>
                            <option value="">Select program</option>
                            <option value="BSCS" @selected(old('program') === 'BSCS')>Computer Science (CS)</option>
                            <option value="BSIT" @selected(old('program') === 'BSIT')>Information Technology (IT)</option>
                        </select>
                        @error('program')<p class="lt-field-error">{{ $message }}</p>@enderror
                    </div>
                    <div class="lt-field">
                        <label for="first_name" class="lt-label">First Name</label>
                        <input id="first_name" class="lt-input" type="text" name="first_name" value="{{ old('first_name') }}" required autocomplete="given-name">
                        @error('first_name')<p class="lt-field-error">{{ $message }}</p>@enderror
                    </div>
                    <div class="lt-field">
                        <label for="last_name" class="lt-label">Last Name</label>
                        <input id="last_name" class="lt-input" type="text" name="last_name" value="{{ old('last_name') }}" required autocomplete="family-name">
                        @error('last_name')<p class="lt-field-error">{{ $message }}</p>@enderror
                    </div>
                    <div class="lt-field">
                        <label for="email" class="lt-label">School Email</label>
                        <input id="email" class="lt-input" type="email" name="email" value="{{ old('email') }}" required autocomplete="username">
                        @error('email')<p class="lt-field-error">{{ $message }}</p>@enderror
                    </div>
                    <div class="lt-field">
                        <label for="password" class="lt-label">Password</label>
                        <input id="password" class="lt-input" type="password" name="password" required autocomplete="new-password" data-strength="#strength">
                        @error('password')<p class="lt-field-error">{{ $message }}</p>@enderror
                    </div>
                    <div class="lt-field">
                        <label for="password_confirmation" class="lt-label">Confirm Password</label>
                        <input id="password_confirmation" class="lt-input" type="password" name="password_confirmation" required autocomplete="new-password" data-match="#password" data-match-msg="#match-msg">
                        <div id="match-msg" class="lt-match" aria-live="polite"></div>
                    </div>
                    <div class="lt-auth__row"><a class="lt-link" href="{{ route('login') }}">Already registered?</a></div>
                    <button type="submit" class="lt-btn lt-btn--primary lt-btn--lg" style="width:100%;" data-loading-text="Creating account...">Register</button>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
