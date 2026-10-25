<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Create Group Room · LogiTrack</title>
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
                <nav class="lt-nav__links lt-nav__links--app" aria-label="Student">
                    <a href="{{ route('dashboard') }}" class="lt-nav__link {{ request()->routeIs('dashboard') ? 'is-active' : '' }}">Dashboard</a>
                    <a href="{{ route('student.group-rooms.index', $classroom->id) }}" class="lt-nav__link is-active" aria-current="page">Classroom</a>
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

    <main class="lt-container lt-container--narrow lt-animate">

        <a href="{{ route('student.group-rooms.index', $classroom->id) }}" class="lt-back">⟵ Back to group rooms</a>

        <div class="lt-page-head">
            <span class="lt-eyebrow">New Group</span>
            <h1>Create Group Room</h1>
            <p class="lt-page-head__sub">You'll be the group leader and can add up to 2 more members afterwards, or stay solo.</p>
        </div>

        <div class="lt-chips">
            <div class="lt-chip">
                <span class="lt-chip__label">Classroom</span>
                <span class="lt-chip__value">{{ $classroom->classroom_name }}</span>
            </div>
        </div>

        @if($errors->any())

            <div class="lt-alert lt-alert--error" role="alert">
                <span class="lt-alert__icon">!</span>
                <div>
                    <strong>Please fix the following:</strong>

                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>

        @endif

        @if(session('error'))
            <div class="lt-alert lt-alert--error" role="alert">
                <span class="lt-alert__icon">!</span>
                <div>{{ session('error') }}</div>
            </div>
        @endif

        <form
            method="POST"
            action="{{ route('student.group-rooms.store', $classroom->id) }}"
            class="lt-card lt-form"
            data-submit-guard
        >

            @csrf

            <div class="lt-field">

                <label for="group_name" class="lt-label">
                    Group Name
                </label>

                <input
                    type="text"
                    id="group_name"
                    name="group_name"
                    class="lt-input"
                    value="{{ old('group_name') }}"
                    placeholder="Example: Group 1"
                    required
                >

                <span class="lt-hint">Pick a name your adviser and groupmates will recognize.</span>

            </div>

            <div class="lt-form__actions">
                <button type="submit" class="lt-btn lt-btn--primary" data-loading-text="Creating...">
                    Create Group Room
                </button>

                <a href="{{ route('student.group-rooms.index', $classroom->id) }}" class="lt-btn lt-btn--ghost">
                    Cancel
                </a>
            </div>

        </form>

    </main>

</body>
</html>