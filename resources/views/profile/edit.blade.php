<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('Profile') }} · LogiTrack</title>
    @vite(['resources/css/logitrack.css', 'resources/js/logitrack.js'])
</head>
<body class="lt-body">

    <header class="lt-nav lt-nav--landing">
        <div class="lt-nav__inner">
            <a href="{{ route('dashboard') }}" class="lt-brand">
                <span class="lt-brand__mark">L</span>
                LogiTrack
            </a>

            <button type="button" class="lt-nav__toggle" data-nav-toggle="#nav-menu" aria-label="Toggle menu" aria-expanded="false">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
            </button>

            <div class="lt-nav__menu" id="nav-menu">
                <nav class="lt-nav__links lt-nav__links--app" aria-label="Account">
                    <a href="{{ route('dashboard') }}" class="lt-nav__link">Dashboard</a>
                    <a href="{{ route('profile.edit') }}" class="lt-nav__link is-active">Profile</a>
                </nav>

                <div class="lt-nav__actions">
                    <span class="lt-user-chip">
                        <span class="lt-avatar">{{ strtoupper(substr(auth()->user()->first_name, 0, 1)) }}</span>
                        {{ auth()->user()->first_name }}
                    </span>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="lt-btn lt-btn--outline-light lt-btn--sm">Log Out</button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <main class="lt-container lt-container--narrow lt-animate">

        <a href="{{ route('dashboard') }}" class="lt-back">← Back to dashboard</a>

        <div class="lt-page-head">
            <span class="lt-eyebrow">Account</span>
            <h1>{{ __('Profile') }}</h1>
            <p class="lt-page-head__sub">Manage your account details, password and security.</p>
        </div>

        <div class="lt-profile-id">
            <span class="lt-avatar">{{ strtoupper(substr(auth()->user()->first_name, 0, 1)) }}</span>
            <div>
                <div class="lt-profile-id__name">{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</div>
                <div class="lt-profile-id__mail">{{ auth()->user()->email }}</div>
            </div>
        </div>

        <div class="lt-card">
            @include('profile.partials.update-profile-information-form')
        </div>

        <div class="lt-card">
            @include('profile.partials.update-password-form')
        </div>

        <div class="lt-card lt-card--danger">
            @include('profile.partials.delete-user-form')
        </div>

    </main>

</body>
</html>