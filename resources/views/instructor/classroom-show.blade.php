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

        {{-- Group Creation Schedule --}}
        <section class="lt-card" style="margin-bottom: 24px;">
            <div class="lt-card__head">
                <h2 class="lt-card__title" style="margin:0;">
                    Group Creation Period
                </h2>
            </div>

            <p class="lt-muted">
                Set when students can create groups in this classroom.
                Students will not be able to create new groups outside this period.
            </p>

            @if($errors->any())
                <div class="lt-alert lt-alert--error" role="alert">
                    <ul style="margin:0; padding-left:20px;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form
                method="POST"
                action="{{ route('instructor.classrooms.group-creation-period.update', $classroom->id) }}"
            >
                @csrf
                @method('PATCH')

                <div class="lt-form-grid" style="display:grid; grid-template-columns:repeat(auto-fit, minmax(240px, 1fr)); gap:16px;">
                    <div>
                        <label for="group_creation_opens_at">
                            Opening date and time
                        </label>
                        <input
                            type="datetime-local"
                            id="group_creation_opens_at"
                            name="group_creation_opens_at"
                            class="lt-input"
                            value="{{ old('group_creation_opens_at', $classroom->group_creation_opens_at?->format('Y-m-d\TH:i')) }}"
                        >
                        @error('group_creation_opens_at')
                            <p class="lt-text-danger">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="group_creation_closes_at">
                            Closing date and time
                        </label>
                        <input
                            type="datetime-local"
                            id="group_creation_closes_at"
                            name="group_creation_closes_at"
                            class="lt-input"
                            value="{{ old('group_creation_closes_at', $classroom->group_creation_closes_at?->format('Y-m-d\TH:i')) }}"
                        >
                        @error('group_creation_closes_at')
                            <p class="lt-text-danger">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <p class="lt-hint" style="margin-top:12px;">
                    Both fields are optional. If either field is blank, that side
                    of the schedule will have no time restriction.
                </p>

                <button type="submit" class="lt-btn lt-btn--green">
                    Save Group Creation Period
                </button>
            </form>
        </section>

        <div class="lt-card__head">
            <h2 class="lt-card__title" style="margin:0;">Students</h2>
        </div>

        @if($classroom->members->count() > 0)

            <div class="lt-stats">
                <div class="lt-stat">
                    <div class="lt-stat__num">{{ $classroom->members->count() }}</div>
                    <div class="lt-stat__label">Total</div>
                </div>
                <div class="lt-stat">
                    <div class="lt-stat__num">{{ $classroom->members->where('status', 'approved')->count() }}</div>
                    <div class="lt-stat__label">Approved</div>
                </div>
                <div class="lt-stat">
                    <div class="lt-stat__num">{{ $classroom->members->where('status', 'pending')->count() }}</div>
                    <div class="lt-stat__label">Pending</div>
                </div>
            </div>

            <div class="lt-card__head">
                <input
                    type="search"
                    class="lt-input"
                    style="max-width: 320px;"
                    placeholder="Search students..."
                    aria-label="Search students"
                    data-table-filter="#student-table"
                    data-filter-empty="#student-filter-empty"
                >
            </div>

            <div class="lt-table-wrap">

                <table class="lt-table" id="student-table">

                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Program</th>
                            <th>Year &amp; Section</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>

                        @foreach($classroom->members as $member)

                            <tr>

                                <td>
                                    <div class="lt-cell-user">
                                        <span class="lt-avatar">{{ strtoupper(substr($member->user->first_name, 0, 1)) }}</span>
                                        <span class="lt-cell-user__name">
                                            {{ $member->user->first_name }}
                                            {{ $member->user->last_name }}
                                        </span>
                                    </div>
                                </td>

                                <td class="lt-muted">
                                    {{ $member->user->email }}
                                </td>

                                <td>
                                    {{ $member->user->program }}
                                </td>

                                <td>
                                    {{ $member->user->year_section }}
                                </td>

                                <td>

                                    <div class="lt-actions">

                                        <span class="lt-badge {{ $member->status === 'approved' ? 'lt-badge--approved' : ($member->status === 'pending' ? 'lt-badge--pending' : 'lt-badge--draft') }}">
                                            {{ ucfirst($member->status) }}
                                        </span>

                                        @if($member->status === 'pending')

                                            <form
                                                method="POST"
                                                action="{{ route('instructor.students.approve', $member->user_id) }}"
                                                data-submit-guard
                                            >

                                                @csrf

                                                <button type="submit" class="lt-btn lt-btn--green lt-btn--sm" data-loading-text="Approving...">
                                                    Approve
                                                </button>

                                            </form>

                                        @endif

                                    </div>

                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

            <div id="student-filter-empty" class="lt-empty lt-hidden lt-mt">
                <div class="lt-empty__title">No matching student</div>
                <p>Try a different name or email.</p>
            </div>

        @else

            <div class="lt-empty">
                <div class="lt-empty__icon">+</div>
                <div class="lt-empty__title">No students yet</div>
                <p>No students have joined this classroom yet.</p>
                <p class="lt-hint lt-mt">Share the class code above so they can join.</p>
            </div>

        @endif

    </main>

</body>
</html>
