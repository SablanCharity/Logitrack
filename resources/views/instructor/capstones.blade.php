<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Capstone Submissions · LogiTrack</title>
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
            <span class="lt-eyebrow">
                Instructor · {{ auth()->user()->first_name }} {{ auth()->user()->last_name }}
            </span>
            <h1>Capstone Submissions</h1>
            <p class="lt-page-head__sub">Open a submission to verify its details or return it for correction.</p>
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

        @if($capstones->count() > 0)

            <div class="lt-stats">
                <div class="lt-stat">
                    <div class="lt-stat__num">{{ $capstones->count() }}</div>
                    <div class="lt-stat__label">Awaiting review</div>
                </div>
            </div>

            <div class="lt-card__head">
                <input
                    type="search"
                    class="lt-input"
                    style="max-width: 320px;"
                    placeholder="Search title, group, student..."
                    aria-label="Search capstone submissions"
                    data-table-filter="#capstone-table"
                    data-filter-empty="#capstone-filter-empty"
                >
            </div>

            <div class="lt-table-wrap">

                <table class="lt-table" id="capstone-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Group</th>
                            <th>Classroom</th>
                            <th>Submitted By</th>
                            <th>Submitted At</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($capstones as $capstone)
                            @php
                                $badge = match(true) {
                                    in_array($capstone->status, ['returned_by_instructor', 'returned_by_admin']) => 'lt-badge--returned',
                                    $capstone->status === 'admin_approved' => 'lt-badge--approved',
                                    $capstone->status === 'instructor_verified' => 'lt-badge--verified',
                                    $capstone->status === 'submitted' => 'lt-badge--submitted',
                                    default => 'lt-badge--draft',
                                };
                            @endphp

                            <tr>
                                <td style="min-width: 220px;">
                                    <a href="{{ route('instructor.capstones.review', $capstone->id) }}" class="lt-table__title">
                                        {{ $capstone->title }}
                                    </a>
                                </td>

                                <td>
                                    {{ $capstone->groupRoom->group_name }}
                                </td>

                                <td>
                                    {{ $capstone->groupRoom->classroom->classroom_name }}
                                </td>

                                <td>
                                    {{ $capstone->submitter->first_name }}
                                    {{ $capstone->submitter->last_name }}
                                </td>

                                <td class="lt-muted lt-nowrap">
                                    {{ $capstone->submitted_at?->format('M d, Y h:i A') }}
                                </td>

                                <td>
                                    <span class="lt-badge {{ $badge }}">
                                        {{ ucfirst(str_replace('_', ' ', $capstone->status)) }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

            </div>

            <div id="capstone-filter-empty" class="lt-empty lt-hidden lt-mt">
                <div class="lt-empty__title">No matching submission</div>
                <p>Try a different search.</p>
            </div>

        @else

            <div class="lt-empty">
                <div class="lt-empty__icon">✓</div>
                <div class="lt-empty__title">Nothing to review</div>
                <p>There are no capstone submissions waiting for review.</p>
            </div>

        @endif

    </main>

</body>
</html>