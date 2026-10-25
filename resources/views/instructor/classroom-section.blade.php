<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $section === 'students' ? 'Students' : 'Group Rooms' }} · {{ $classroom->classroom_name }} · LogiTrack</title>
    @vite(['resources/css/logitrack.css', 'resources/js/logitrack.js'])
</head>
<body class="lt-body">
    <header class="lt-nav lt-nav--landing">
        <div class="lt-nav__inner">
            <a href="{{ route('dashboard') }}" class="lt-brand"><span class="lt-brand__mark">L</span>LogiTrack</a>
            <button type="button" class="lt-nav__toggle" data-nav-toggle="#nav-menu" aria-label="Toggle menu" aria-expanded="false">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
            </button>
            <div class="lt-nav__menu" id="nav-menu">
                <nav class="lt-nav__links lt-nav__links--app" aria-label="Instructor">
                    <a href="{{ route('dashboard') }}" class="lt-nav__link">Dashboard</a>
                    <a href="{{ route('repository.index') }}" class="lt-nav__link">Repository</a>
                    <a href="{{ route('instructor.classrooms') }}" class="lt-nav__link is-active">Classroom</a>
                    <a href="{{ route('instructor.capstones') }}" class="lt-nav__link">Capstone Submissions</a>
                </nav>
                <div class="lt-nav__actions">
                    <a href="{{ route('profile.edit') }}" class="lt-user-chip lt-user-chip--link">
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

    <main class="lt-container lt-animate">
        <a href="{{ route('instructor.classroom.show', $classroom->id) }}" class="lt-back">⟵ Back to Classroom</a>
        <div class="lt-page-head">
            <span class="lt-eyebrow">{{ $classroom->classroom_name }}</span>
            <h1>{{ $section === 'students' ? 'Students' : 'Group Rooms' }}</h1>
        </div>

        @if($section === 'students')
            @if($classroom->members->count() > 0)
                <div class="lt-stats">
                    <div class="lt-stat"><div class="lt-stat__num">{{ $classroom->members->count() }}</div><div class="lt-stat__label">Total</div></div>
                    <div class="lt-stat"><div class="lt-stat__num">{{ $classroom->members->where('status', 'approved')->count() }}</div><div class="lt-stat__label">Approved</div></div>
                    <div class="lt-stat"><div class="lt-stat__num">{{ $classroom->members->where('status', 'pending')->count() }}</div><div class="lt-stat__label">Pending</div></div>
                </div>
                <div class="lt-table-wrap">
                    <table class="lt-table">
                        <thead><tr><th>Name</th><th>Email</th><th>Program</th><th>Year &amp; Section</th><th>Status</th></tr></thead>
                        <tbody>
                            @foreach($classroom->members as $member)
                                <tr>
                                    <td><div class="lt-cell-user"><span class="lt-avatar">{{ strtoupper(substr($member->user->first_name, 0, 1)) }}</span><span class="lt-cell-user__name">{{ $member->user->first_name }} {{ $member->user->last_name }}</span></div></td>
                                    <td class="lt-muted">{{ $member->user->email }}</td>
                                    <td>{{ $member->user->program }}</td>
                                    <td>{{ $member->user->year_section }}</td>
                                    <td>
                                        <div class="lt-actions">
                                            <span class="lt-badge {{ $member->status === 'approved' ? 'lt-badge--approved' : ($member->status === 'pending' ? 'lt-badge--pending' : 'lt-badge--draft') }}">{{ ucfirst($member->status) }}</span>
                                            @if($member->status === 'pending')
                                                <form method="POST" action="{{ route('instructor.students.approve', $member->user_id) }}" data-submit-guard>
                                                    @csrf
                                                    <button type="submit" class="lt-btn lt-btn--green lt-btn--sm" data-loading-text="Approving...">Approve</button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="lt-empty"><div class="lt-empty__title">No students yet</div><p>No students have joined this classroom yet.</p></div>
            @endif
        @else
            @if($classroom->groupRooms->count() > 0)
                <div class="lt-table-wrap">
                    <table class="lt-table">
                        <thead><tr><th>Group Name</th><th>Members</th><th>Capstone</th></tr></thead>
                        <tbody>
                            @foreach($classroom->groupRooms as $groupRoom)
                                <tr>
                                    <td>{{ $groupRoom->group_name }}</td>
                                    <td>
                                        <div class="lt-member-popover">
                                            <button type="button" class="lt-member-count" aria-label="Show members of {{ $groupRoom->group_name }}">
                                                {{ $groupRoom->members->count() }}
                                            </button>
                                            <div class="lt-member-popover__panel" role="tooltip">
                                                <strong>Members</strong>
                                                <ul>
                                                    @foreach($groupRoom->members as $member)
                                                        <li>
                                                            {{ $member->user->first_name }} {{ $member->user->last_name }}
                                                            @if($member->is_leader)
                                                                <span class="lt-member-leader">Leader</span>
                                                            @endif
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $groupRoom->capstoneProject ? 'Submitted' : 'Not submitted' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="lt-empty"><div class="lt-empty__title">No group rooms yet</div><p>Students have not created any group rooms in this classroom.</p></div>
            @endif
        @endif
    </main>
</body>
</html>
