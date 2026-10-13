<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Verify Email · LogiTrack</title>
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

                <div class="lt-auth__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/><path d="m9 16 2 2 4-4" stroke-width="2.4"/></svg></div>

                <div class="lt-auth__head">
                    <h1>Check your email</h1>
                    <p>{{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}</p>
                </div>

                @if (session('status') == 'verification-link-sent')
                    <div class="lt-alert lt-alert--success" role="status">
                        <span class="lt-alert__icon">✓</span>
                        <div>{{ __('A new verification link has been sent to the email address you provided during registration.') }}</div>
                    </div>
                @endif

                <div class="lt-auth__row">
                    <form method="POST" action="{{ route('verification.send') }}" data-submit-guard>
                        @csrf

                        <div>
                            <button type="submit" class="lt-btn lt-btn--primary" data-loading-text="Sending...">
                                {{ __('Resend Verification Email') }}
                            </button>
                        </div>
                    </form>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf

                        <button type="submit" class="lt-link lt-link-btn">
                            {{ __('Log Out') }}
                        </button>
                    </form>
                </div>

            </div>

        </main>

    </div>

</body>
</html>