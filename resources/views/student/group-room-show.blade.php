<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $groupRoom->group_name }} · LogiTrack</title>
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
                    <a href="{{ route('student.group-rooms.index', $groupRoom->classroom_id) }}" class="lt-nav__link is-active" aria-current="page">Classroom</a>
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

    <main class="lt-container lt-animate">

        <a href="{{ route('student.group-rooms.index', $groupRoom->classroom_id) }}" class="lt-back">
            ⟵ Back to Group Rooms
        </a>

        <div class="lt-page-head">
            <span class="lt-eyebrow">{{ $groupRoom->classroom->classroom_name }}</span>
            <h1>{{ $groupRoom->group_name }}</h1>
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

        {{-- ===== Capstone status ===== --}}
        <section class="lt-card">

            @if($groupRoom->capstoneProject)

                @php
                    $capstoneStatus = $groupRoom->capstoneProject->status;
                    $badgeClass = match(true) {
                        in_array($capstoneStatus, ['returned_by_instructor', 'returned_by_admin']) => 'lt-badge--returned',
                        $capstoneStatus === 'admin_approved' => 'lt-badge--approved',
                        $capstoneStatus === 'instructor_verified' => 'lt-badge--verified',
                        $capstoneStatus === 'submitted' => 'lt-badge--submitted',
                        default => 'lt-badge--draft',
                    };
                @endphp

                <div class="lt-status-panel">
                    <div>
                        <div class="lt-status-panel__label">Capstone Status</div>
                        <span class="lt-badge {{ $badgeClass }}">
                            {{ ucfirst(str_replace('_', ' ', $groupRoom->capstoneProject->status)) }}
                        </span>
                    </div>

                    @if(in_array($groupRoom->capstoneProject->status, [
                            'returned_by_instructor',
                            'returned_by_admin'
                        ]))
                        <a href="{{ route('student.capstone.edit', $groupRoom->id) }}" class="lt-btn lt-btn--gold">
                            Edit &amp; Resubmit Capstone
                        </a>
                    @endif
                </div>

                @if(in_array($groupRoom->capstoneProject->status, [
                        'returned_by_instructor',
                        'returned_by_admin'
                    ]))

                    @if($groupRoom->capstoneProject->return_reason)
                        <div class="lt-callout">
                            <div class="lt-callout__label">Reason for Return</div>
                            <p class="lt-callout__text">{{ $groupRoom->capstoneProject->return_reason }}</p>
                        </div>
                    @endif

                @endif

            @else

                <div class="lt-status-panel">
                    <div>
                        <div class="lt-status-panel__label">Capstone Status</div>
                        <p class="lt-muted">Your group hasn't submitted a capstone yet.</p>
                    </div>

                    <a href="{{ route('student.capstone.create', $groupRoom->id) }}" class="lt-btn lt-btn--primary">
                        Submit Capstone
                    </a>
                </div>

            @endif

        </section>

        {{-- ===== Group members ===== --}}
        <section class="lt-card">

            <div class="lt-card__head">
                <h2 class="lt-card__title" style="margin:0;">Group Members</h2>

                <div class="lt-meter" title="{{ $groupRoom->members->count() }} of 3 members">
                    <div class="lt-meter__track">
                        @for($i = 1; $i <= 3; $i++)
                            <span class="lt-meter__slot {{ $i <= $groupRoom->members->count() ? 'is-filled' : '' }}"></span>
                        @endfor
                    </div>
                    <span class="lt-meter__text">{{ $groupRoom->members->count() }} / 3</span>
                </div>
            </div>

            @if($groupRoom->members->count() < 3)
                @php
                    $isLeader = $groupRoom->members
                        ->where('user_id', auth()->id())
                        ->where('is_leader', true)
                        ->isNotEmpty();
                @endphp

                @if($isLeader)
                    <div class="lt-auto" style="margin-bottom: 22px;">
                        <div class="lt-auto__title">Add Member</div>

                        <form method="POST"
                              action="{{ route('student.group-room.add-member', $groupRoom->id) }}"
                              class="lt-field"
                              data-submit-guard>
                            @csrf

                            <label for="user_id" class="lt-label">Select Student</label>

                            <div class="lt-inline-form">
                                <select id="user_id" name="user_id" class="lt-select" required>
                                    <option value="">-- Select Student --</option>

                                    @foreach($groupRoom->classroom->members as $classroomMember)
                                        @if(
                                            $classroomMember->status === 'approved' &&
                                            $classroomMember->user_id !== auth()->id() &&
                                            !$groupRoom->members->contains('user_id', $classroomMember->user_id)
                                        )
                                            <option value="{{ $classroomMember->user_id }}">
                                                {{ $classroomMember->user->first_name }}
                                                {{ $classroomMember->user->last_name }}
                                                — {{ $classroomMember->user->email }}
                                            </option>
                                        @endif
                                    @endforeach
                                </select>

                                <button type="submit" class="lt-btn lt-btn--green" data-loading-text="Adding...">
                                    Add Member
                                </button>
                            </div>
                        </form>
                    </div>
                @endif
            @else
                <div class="lt-alert lt-alert--warning" style="margin-bottom: 22px;">
                    <span class="lt-alert__icon">i</span>
                    <div>This group has reached the maximum of 3 members.</div>
                </div>
            @endif

            @if($groupRoom->members->count() > 0)

                <div class="lt-table-wrap">

                    <table class="lt-table">

                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Program</th>
                                <th>Year &amp; Section</th>
                                <th>Role</th>
                            </tr>
                        </thead>

                        <tbody>

                            @foreach($groupRoom->members as $member)

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
                                        @if($member->is_leader)
                                            <span class="lt-badge lt-badge--leader">Leader</span>
                                        @else
                                            <span class="lt-badge lt-badge--member">Member</span>
                                        @endif
                                    </td>

                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>

            @else

                <div class="lt-empty">
                    <div class="lt-empty__title">No members yet</div>
                    <p>No members in this group.</p>
                </div>

            @endif

        </section>

    </main>

</body>
</html>