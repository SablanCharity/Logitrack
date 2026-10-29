<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('Profile') }} &middot; LogiTrack</title>
    @vite(['resources/css/logitrack.css', 'resources/js/logitrack.js'])
</head>
<body class="lt-body">

    @include('components.site-navigation')

    <main class="lt-container lt-container--narrow lt-animate">

        <a href="{{ route('dashboard') }}" class="lt-back">&larr; Back to dashboard</a>

        <div class="lt-page-head">
            <span class="lt-eyebrow">Account</span>
            <h1>{{ __('Profile') }}</h1>
            <p class="lt-page-head__sub">Manage your account details, password and security.</p>
        </div>

        @if($user->role === 'instructor' && $user->must_change_password)
            <div class="lt-alert lt-alert--error" role="alert">
                <span class="lt-alert__icon">!</span>
                <div>Please change your temporary password before continuing to use LogiTrack.</div>
            </div>
        @endif

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