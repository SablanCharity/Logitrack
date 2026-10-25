<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pending Approval · LogiTrack</title>
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
                    <a href="{{ route('student.pending') }}" class="lt-nav__link is-active" aria-current="page">Classroom</a>
                    <a href="{{ route('repository.index') }}" class="lt-nav__link {{ request()->routeIs('repository.*') ? 'is-active' : '' }}">Repository</a>
                    <a href="{{ route('similarity.index') }}" class="lt-nav__link {{ request()->routeIs('similarity.*') ? 'is-active' : '' }}">Similarity Checker</a>
                </nav>

                <div class="lt-nav__actions">
                    <a href="{{ route('profile.edit') }}" class="lt-user-chip lt-user-chip--link" aria-label="Edit profile">
                        <span class="lt-avatar">{{ strtoupper(substr(auth()->user()->first_name, 0, 1)) }}</span>
                        {{ auth()->user()->first_name }}
                    </a>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="lt-btn lt-btn--outline-light lt-btn--sm">Log Out</button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <div class="lt-center-wrap lt-center-wrap--nav">

        <main class="lt-pending lt-animate">

            <div class="lt-pending__top">
                <span class="lt-brand">
                    <span class="lt-brand__mark">L</span>
                    LogiTrack
                </span>
            </div>

            <div class="lt-pending__icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="9"></circle>
                    <path d="M12 7v5l3 2"></path>
                </svg>
            </div>

            <div class="lt-pending__body">

                <h1>Account Pending Approval</h1>

                <p class="lt-pending__hello">
                    Hello, {{ auth()->user()->first_name }}!
                </p>

                <p>
                    Your account is currently waiting for your instructor/adviser
                    to approve your classroom membership.
                </p>

                <div class="lt-steps" aria-label="Approval progress">
                    <div class="lt-step is-done">
                        <span class="lt-step__dot">✓</span>
                        Account registered
                    </div>
                    <div class="lt-step is-current">
                        <span class="lt-step__dot">2</span>
                        Waiting for adviser approval
                    </div>
                    <div class="lt-step">
                        <span class="lt-step__dot">3</span>
                        Access your classroom
                    </div>
                </div>

                <p>
                    Please wait until your instructor approves your account.
                </p>

            </div>

        </main>

    </div>

</body>
</html>