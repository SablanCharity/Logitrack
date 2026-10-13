<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('Dashboard') }} · LogiTrack</title>
    @vite(['resources/css/logitrack.css', 'resources/js/logitrack.js'])
    
</head>
<body class="lt-body">

    @php
        $user = auth()->user();

        $roleLabel = match($user->role) {
            'admin' => 'Admin',
            'instructor' => 'Instructor / Adviser',
            'ojt' => 'OJT',
            'student' => 'Student',
            default => ucfirst((string) $user->role),
        };
    @endphp

    <header class="lt-nav">
        <div class="lt-nav__inner">
            <a href="{{ url('/dashboard') }}" class="lt-brand">
                <span class="lt-brand__mark">L</span>
                LogiTrack
            </a>

            <div class="lt-nav__actions">
                <span class="lt-user-chip">
                    <span class="lt-avatar">{{ strtoupper(substr($user->first_name, 0, 1)) }}</span>
                    {{ $user->first_name }}
                </span>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="lt-btn lt-btn--ghost lt-btn--sm">Log Out</button>
                </form>
            </div>
        </div>
    </header>

    <main class="lt-container lt-animate">

        {{-- Welcome banner --}}
        <section class="lt-welcome">
            <span class="lt-eyebrow">{{ __('Dashboard') }}</span>
            <h1>Welcome back, {{ $user->first_name }}!</h1>
            <p>{{ __("You're logged in!") }}</p>

            <div class="lt-welcome__meta">
                <span class="lt-pill lt-pill--gold">{{ $roleLabel }}</span>

                @if($user->role === 'student')
                    @if($user->is_approved)
                        <span class="lt-pill lt-pill--ok">✓ Approved</span>
                    @else
                        <span class="lt-pill lt-pill--wait">Pending approval</span>
                    @endif
                @endif
            </div>
        </section>

        <div class="lt-dash-grid">

            {{-- Account details --}}
            <section class="lt-card">
                <div class="lt-card__head">
                    <h2 class="lt-card__title" style="margin:0;">Account Details</h2>
                </div>

                <dl class="lt-details">
                    <div class="lt-details__row">
                        <dt>Full name</dt>
                        <dd>{{ $user->first_name }} {{ $user->last_name }}</dd>
                    </div>

                    <div class="lt-details__row">
                        <dt>Email</dt>
                        <dd>{{ $user->email }}</dd>
                    </div>

                    <div class="lt-details__row">
                        <dt>Role</dt>
                        <dd>{{ $roleLabel }}</dd>
                    </div>

                    @if($user->program)
                        <div class="lt-details__row">
                            <dt>Program</dt>
                            <dd>{{ $user->program }}</dd>
                        </div>
                    @endif

                    @if($user->year_section)
                        <div class="lt-details__row">
                            <dt>Year &amp; Section</dt>
                            <dd>{{ $user->year_section }}</dd>
                        </div>
                    @endif
                </dl>
            </section>

            {{-- Quick links (only shown when the route exists) --}}
            <section class="lt-card">
                <h2 class="lt-card__title">Quick Links</h2>
                <p class="lt-card__desc">Jump to common places.</p>

                @if(Route::has('profile.edit'))
                    <a href="{{ route('profile.edit') }}" class="lt-action">
                        Edit profile <span aria-hidden="true">→</span>
                    </a>
                @endif

                <a href="{{ url('/') }}" class="lt-action">
                    Home page <span aria-hidden="true">→</span>
                </a>
            </section>

        </div>

    </main>

</body>
</html>