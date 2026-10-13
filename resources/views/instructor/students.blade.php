<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pending Students · LogiTrack</title>
    @vite(['resources/css/logitrack.css', 'resources/js/logitrack.js'])
    
</head>

<body class="lt-body">

    <header class="lt-nav lt-nav--landing">
        <div class="lt-nav__inner">
            <a href="{{ route('instructor.classrooms') }}" class="lt-brand">
                <span class="lt-brand__mark">L</span>
                LogiTrack
            </a>

            <button type="button" class="lt-nav__toggle" data-nav-toggle="#nav-menu" aria-label="Toggle menu" aria-expanded="false">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
            </button>

            <div class="lt-nav__menu" id="nav-menu">
                <nav class="lt-nav__links lt-nav__links--app" aria-label="Instructor">
                    <a href="{{ route('instructor.classrooms') }}" class="lt-nav__link {{ request()->routeIs('instructor.classrooms*') ? 'is-active' : '' }}">Classrooms</a>
                    <a href="{{ route('instructor.capstones') }}" class="lt-nav__link {{ request()->routeIs('instructor.capstones*') ? 'is-active' : '' }}">Capstone Submissions</a>
                    @if(Route::has('instructor.students'))
                        <a href="{{ route('instructor.students') }}" class="lt-nav__link {{ request()->routeIs('instructor.students*') ? 'is-active' : '' }}">Pending Students</a>
                    @endif
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
            <span class="lt-eyebrow">Instructor · {{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</span>
            <h1>Pending Students</h1>
            <p class="lt-page-head__sub">Approve students so they can access their classroom and create groups.</p>
        </div>

        @if(session('success'))
            <div class="lt-alert lt-alert--success" role="status" data-autohide="6000">
                <span class="lt-alert__icon">✓</span>
                <div>{{ session('success') }}</div>
            </div>
        @endif

        @if($students->count() > 0)

            <div class="lt-stats">
                <div class="lt-stat">
                    <div class="lt-stat__num">{{ $students->count() }}</div>
                    <div class="lt-stat__label">Waiting for approval</div>
                </div>
            </div>

            <div class="lt-card__head">
                <input
                    type="search"
                    class="lt-input"
                    style="max-width: 320px;"
                    placeholder="Search pending students..."
                    aria-label="Search pending students"
                    data-table-filter="#pending-table"
                    data-filter-empty="#pending-filter-empty"
                >
            </div>

            <div class="lt-table-wrap">

                <table class="lt-table" id="pending-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Program</th>
                            <th>Year &amp; Section</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($students as $student)
                            <tr>
                                <td>
                                    <div class="lt-cell-user">
                                        <span class="lt-avatar">{{ strtoupper(substr($student->first_name, 0, 1)) }}</span>
                                        <span class="lt-cell-user__name">
                                            {{ $student->first_name }}
                                            {{ $student->last_name }}
                                        </span>
                                    </div>
                                </td>

                                <td class="lt-muted">{{ $student->email }}</td>

                                <td>{{ $student->program }}</td>

                                <td>{{ $student->year_section }}</td>

                                <td>
                                    <div class="lt-actions">
                                        <form method="POST" action="{{ route('instructor.students.approve', $student->id) }}" data-submit-guard>
                                            @csrf
                                            <button type="submit" class="lt-btn lt-btn--green lt-btn--sm" data-loading-text="Approving...">Approve</button>
                                        </form>

                                        <button type="button" class="lt-btn lt-btn--danger-ghost lt-btn--sm">Reject</button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

            </div>

            <div id="pending-filter-empty" class="lt-empty lt-hidden lt-mt">
                <div class="lt-empty__title">No matching student</div>
                <p>Try a different name or email.</p>
            </div>

        @else

            <div class="lt-empty">
                <div class="lt-empty__icon">✓</div>
                <div class="lt-empty__title">You're all caught up</div>
                <p>No pending students.</p>
            </div>

        @endif

    </main>

</body>
</html>