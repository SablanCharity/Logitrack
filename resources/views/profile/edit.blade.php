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
                <nav class="lt-nav__links lt-nav__links--app" aria-label="Main">
                    <a href="{{ route('dashboard') }}" class="lt-nav__link {{ request()->routeIs('dashboard') ? 'is-active' : '' }}">Dashboard</a>

                    @if($user->role === 'instructor')
                        <a href="{{ route('instructor.classrooms') }}" class="lt-nav__link {{ request()->routeIs('instructor.classrooms*', 'instructor.classroom.*') ? 'is-active' : '' }}">Classroom</a>
                        <a href="{{ route('instructor.capstones') }}" class="lt-nav__link {{ request()->routeIs('instructor.capstones*', 'instructor.capstone.*') ? 'is-active' : '' }}">Capstone Submissions</a>
                        <a href="{{ route('instructor.students') }}" class="lt-nav__link {{ request()->routeIs('instructor.students*') ? 'is-active' : '' }}">Pending Students</a>
                    @elseif($user->role === 'student')
                        <a href="{{ $classroomId ? route('student.group-rooms.index', $classroomId) : route('student.pending') }}" class="lt-nav__link {{ request()->routeIs('student.group-rooms.*', 'student.group-room.*', 'student.capstone.*', 'student.pending') ? 'is-active' : '' }}">Classroom</a>
                        <a href="{{ route('similarity.index') }}" class="lt-nav__link {{ request()->routeIs('similarity.*') ? 'is-active' : '' }}">Similarity Checker</a>
                    @endif
                </nav>

                <div class="lt-nav__actions">
                    <a href="{{ route('profile.edit') }}" class="lt-user-chip lt-user-chip--link" aria-label="Edit profile" aria-current="page">
                        <span class="lt-avatar">{{ strtoupper(substr($user->first_name, 0, 1)) }}</span>
                        {{ $user->first_name }}
                    </a>

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