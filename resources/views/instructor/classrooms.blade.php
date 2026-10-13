<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Classrooms · LogiTrack</title>
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
                    <a href="{{ route('instructor.classrooms') }}" class="lt-nav__link {{ request()->routeIs('instructor.classrooms*', 'instructor.classroom.*') ? 'is-active' : '' }}">Classroom</a>
                    <a href="{{ route('instructor.capstones') }}" class="lt-nav__link {{ request()->routeIs('instructor.capstones*', 'instructor.capstone.*') ? 'is-active' : '' }}">Capstone Submissions</a>
                    <a href="{{ route('instructor.students') }}" class="lt-nav__link {{ request()->routeIs('instructor.students*') ? 'is-active' : '' }}">Pending Students</a>
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

    <main class="lt-container lt-animate">

        <div class="lt-page-head">
            <div class="lt-page-head__row">
                <div>
                    <span class="lt-eyebrow">Instructor · {{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</span>
                    <h1>My Classrooms</h1>
                    <p class="lt-page-head__sub">Share the class code with your students so they can join.</p>
                </div>

                <a href="{{ route('instructor.classrooms.create') }}" class="lt-btn lt-btn--gold">
                    + Create Classroom
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="lt-alert lt-alert--success" role="status" data-autohide="6000">
                <span class="lt-alert__icon">✓</span>
                <div>{{ session('success') }}</div>
            </div>
        @endif

        @if($classrooms->count() > 0)

            <div class="lt-stats">
                <div class="lt-stat">
                    <div class="lt-stat__num">{{ $classrooms->count() }}</div>
                    <div class="lt-stat__label">{{ $classrooms->count() === 1 ? 'Classroom' : 'Classrooms' }}</div>
                </div>
            </div>

            <div class="lt-table-wrap">

                <table class="lt-table">

                    <thead>
                        <tr>
                            <th>Classroom Name</th>
                            <th>Class Code</th>
                            <th>Created</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>

                        @foreach($classrooms as $classroom)

                            <tr>
                                <td>
                                    <a href="{{ route('instructor.classroom.show', $classroom->id) }}" class="lt-table__title">
                                        {{ $classroom->classroom_name }}
                                    </a>
                                </td>

                                <td>
                                    <span class="lt-code">
                                        {{ $classroom->class_code }}
                                        <button type="button" class="lt-copy" data-copy="{{ $classroom->class_code }}">Copy</button>
                                    </span>
                                </td>

                                <td class="lt-muted lt-nowrap">
                                    {{ $classroom->created_at->format('M d, Y') }}
                                </td>

                                <td>
                                    <a href="{{ route('instructor.classroom.show', $classroom->id) }}" class="lt-btn lt-btn--ghost lt-btn--sm">
                                        View Classroom
                                    </a>
                                </td>
                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

        @else

            <div class="lt-empty">
                <div class="lt-empty__icon">+</div>
                <div class="lt-empty__title">No classrooms yet</div>
                <p>You don't have any classrooms yet.</p>
                <p class="lt-mt">
                    <a href="{{ route('instructor.classrooms.create') }}" class="lt-btn lt-btn--primary">+ Create Classroom</a>
                </p>
            </div>

        @endif

    </main>

</body>
</html>