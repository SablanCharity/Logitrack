<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $classroom->classroom_name }} - Group Rooms · LogiTrack</title>
    @vite(['resources/css/logitrack.css', 'resources/js/logitrack.js'])
    
</head>

<body class="lt-body">

    <header class="lt-nav">
        <div class="lt-nav__inner">
            <a href="{{ route('student.group-rooms', $classroom->id) }}" class="lt-brand">
                <span class="lt-brand__mark">L</span>
                LogiTrack
            </a>
            <span class="lt-nav__tag">Student</span>
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

            <div class="lt-card__head">
                <input
                    type="search"
                    class="lt-input"
                    style="max-width: 320px;"
                    placeholder="Search group name..."
                    aria-label="Search group rooms"
                    data-table-filter="#group-table"
                    data-filter-empty="#group-filter-empty"
                >
            </div>

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

            <div id="group-filter-empty" class="lt-empty lt-hidden lt-mt">
                <div class="lt-empty__title">No matching group</div>
                <p>Try a different name.</p>
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