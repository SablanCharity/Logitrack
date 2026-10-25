<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $classroom->classroom_name }} - Group Rooms · LogiTrack</title>
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

    <main class="lt-container lt-animate">

        <div class="lt-page-head">
            <div class="lt-page-head__row">
                <div>
                    <span class="lt-eyebrow">{{ $classroom->classroom_name }}</span>
                    <h1>Group Rooms</h1>
                    <p class="lt-page-head__sub">Open your group to manage members and submit your capstone.</p>
                </div>

                <a href="{{ route('student.group-rooms.create', $classroom->id) }}" class="lt-btn lt-btn--gold">
                    + Create Group Room
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="lt-alert lt-alert--success" role="status" data-autohide="6000">
                <span class="lt-alert__icon">✓</span>
                <div>{{ session('success') }}</div>
            </div>
        @endif

        @if($classroom->groupRooms->count() > 0)

            <div class="lt-table-wrap">

                <table class="lt-table" id="group-table">

                    <thead>
                        <tr>
                            <th>Group Name</th>
                            <th>Created</th>
                        </tr>
                    </thead>

                    <tbody>

                        @foreach($classroom->groupRooms as $groupRoom)

                            <tr>
                                <td>
                                    <a href="{{ route('student.group-room.show', $groupRoom->id) }}" class="lt-table__link">
                                        {{ $groupRoom->group_name }}
                                        <span aria-hidden="true">→</span>
                                    </a>
                                </td>

                                <td class="lt-muted">
                                    {{ $groupRoom->created_at->format('M d, Y') }}
                                </td>
                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

        @else

            <div class="lt-empty">
                <div class="lt-empty__icon">+</div>
                <div class="lt-empty__title">No group rooms yet</div>
                <p>No group rooms have been created yet.</p>
            </div>

        @endif

    </main>

</body>
</html>