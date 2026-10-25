<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $classroom->classroom_name }} · LogiTrack</title>
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
                <nav class="lt-nav__links lt-nav__links--app" aria-label="Instructor">
                    <a href="{{ route('dashboard') }}" class="lt-nav__link {{ request()->routeIs('dashboard') ? 'is-active' : '' }}">Dashboard</a>
                    <a href="{{ route('repository.index') }}" class="lt-nav__link {{ request()->routeIs('repository.*') ? 'is-active' : '' }}">Repository</a>
                    <a href="{{ route('instructor.classrooms') }}" class="lt-nav__link {{ request()->routeIs('instructor.classrooms*', 'instructor.classroom.*') ? 'is-active' : '' }}">Classroom</a>
                    <a href="{{ route('instructor.capstones') }}" class="lt-nav__link {{ request()->routeIs('instructor.capstones*', 'instructor.capstone.*') ? 'is-active' : '' }}">Capstone Submissions</a>
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
                    <a href="{{ route('profile.edit') }}" class="lt-btn lt-btn--outline-light lt-btn--sm">Profile</a>
                </div>
            </div>
        </div>
    </header>

    <main class="lt-container lt-animate">

        <a href="{{ route('instructor.classrooms') }}" class="lt-back">
            ⟵ Back to Classrooms
        </a>

        <div class="lt-page-head">
            <span class="lt-eyebrow">Classroom</span>
            <h1>{{ $classroom->classroom_name }}</h1>
        </div>

        <div class="lt-chips">
            <div class="lt-chip">
                <span class="lt-chip__label">Class Code</span>
                <span class="lt-chip__value">
                    <span class="lt-code">
                        {{ $classroom->class_code }}
                        <button type="button" class="lt-copy" data-copy="{{ $classroom->class_code }}">Copy</button>
                    </span>
                </span>
            </div>

            <div class="lt-chip">
                <span class="lt-chip__label">Instructor</span>
                <span class="lt-chip__value">
                    {{ auth()->user()->first_name }}
                    {{ auth()->user()->last_name }}
                </span>
            </div>
        </div>

        @if(session('success'))
            <div class="lt-alert lt-alert--success" role="status" data-autohide="6000">
                <span class="lt-alert__icon">✓</span>
                <div>{{ session('success') }}</div>
            </div>
        @endif

        @if(session('error'))
            <div class="lt-alert lt-alert--error" role="alert">
                <span class="lt-alert__icon">!</span>
                <div>{{ session('error') }}</div>
            </div>
        @endif

        <div class="lt-grid-2">
            <section class="lt-card lt-classroom-option">
                <div class="lt-card__head">
                    <h2 class="lt-card__title">Students</h2>
                </div>
                <p>View and manage the students enrolled in this classroom.</p>
                <a href="{{ route('instructor.classroom.students', $classroom->id) }}" class="lt-btn lt-btn--ghost lt-btn--sm">View Students</a>
            </section>

            <section class="lt-card lt-classroom-option">
                <div class="lt-card__head">
                    <h2 class="lt-card__title">Group Rooms</h2>
                </div>
                <p>View the groups and capstone submission status for this classroom.</p>
                <a href="{{ route('instructor.classroom.group-rooms', $classroom->id) }}" class="lt-btn lt-btn--ghost lt-btn--sm">View Group Rooms</a>
            </section>
        </div>

    </main>

</body>
</html>