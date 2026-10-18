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

        @php
            $now = now();

            $opensAt = $classroom->group_creation_opens_at;
            $closesAt = $classroom->group_creation_closes_at;

            $groupCreationNotOpen = $opensAt !== null
                && $now->lt($opensAt);

            $groupCreationClosed = $closesAt !== null
                && $now->greaterThanOrEqualTo($closesAt);

            $alreadyInGroup = $alreadyInGroup ?? false;
        @endphp

        <div class="lt-page-head">
            <div class="lt-page-head__row">
                <div>
                    <span class="lt-eyebrow">{{ $classroom->classroom_name }}</span>
                    <h1>Group Rooms</h1>
                    <p class="lt-page-head__sub">Open your group to manage members and submit your capstone.</p>
                </div>

                {{-- Only hide the button if this student already belongs to a group --}}
                @if(!$alreadyInGroup)
                    @if($groupCreationNotOpen)
                        <button
                            type="button"
                            class="lt-btn lt-btn--gold"
                            disabled
                            aria-disabled="true"
                            title="Group creation has not opened yet."
                            style="opacity: 0.55; cursor: not-allowed;"
                        >
                            Opens {{ $opensAt->format('M d, Y h:i A') }}
                        </button>
                    @elseif($groupCreationClosed)
                        <button
                            type="button"
                            class="lt-btn lt-btn--gold"
                            disabled
                            aria-disabled="true"
                            title="The group creation period has ended."
                            style="opacity: 0.55; cursor: not-allowed;"
                        >
                            Group Creation Closed
                        </button>
                    @else
                        <a
                            href="{{ route('student.group-rooms.create', $classroom->id) }}"
                            class="lt-btn lt-btn--gold"
                        >
                            + Create Group Room
                        </a>
                    @endif
                @endif
            </div>
        </div>

        {{-- Group Creation Schedule --}}
        <section
            aria-labelledby="groupCreationScheduleTitle"
            style="
                margin: 0 0 24px;
                padding: clamp(16px, 3vw, 24px);
                background: #ffffff;
                border: 1px solid #e5e7eb;
                border-radius: 14px;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
                box-sizing: border-box;
                width: 100%;
            "
        >
            <h2
                id="groupCreationScheduleTitle"
                style="
                    margin: 0 0 8px;
                    font-size: clamp(18px, 3vw, 22px);
                    line-height: 1.4;
                    color: #1f2937;
                "
            >
                Group Creation Schedule
            </h2>

            <p style="
                margin: 0 0 18px;
                color: #6b7280;
                font-size: 14px;
                line-height: 1.6;
            ">
                Check the allowed period for creating a group room in this classroom.
            </p>

            <div style="
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(min(100%, 220px), 1fr));
                gap: 14px;
            ">
                {{-- Opening Schedule --}}
                <div style="
                    min-width: 0;
                    padding: 16px;
                    background: #f0fdf4;
                    border: 1px solid #bbf7d0;
                    border-radius: 10px;
                    box-sizing: border-box;
                ">
                    <div style="
                        display: flex;
                        align-items: center;
                        gap: 8px;
                        margin-bottom: 8px;
                        color: #166534;
                        font-size: 13px;
                        font-weight: 700;
                        letter-spacing: 0.04em;
                    ">
                        <span aria-hidden="true">◷</span>
                        OPENS
                    </div>

                    <div style="
                        color: #14532d;
                        font-size: 16px;
                        font-weight: 600;
                        line-height: 1.6;
                        overflow-wrap: anywhere;
                    ">
                        @if($opensAt)
                            {{ $opensAt->format('M d, Y') }}
                            <br>
                            {{ $opensAt->format('h:i A') }}
                        @else
                            No opening restriction
                        @endif
                    </div>
                </div>

                {{-- Closing Schedule --}}
                <div style="
                    min-width: 0;
                    padding: 16px;
                    background: #fff7ed;
                    border: 1px solid #fed7aa;
                    border-radius: 10px;
                    box-sizing: border-box;
                ">
                    <div style="
                        display: flex;
                        align-items: center;
                        gap: 8px;
                        margin-bottom: 8px;
                        color: #9a3412;
                        font-size: 13px;
                        font-weight: 700;
                        letter-spacing: 0.04em;
                    ">
                        <span aria-hidden="true">◷</span>
                        CLOSES
                    </div>

                    <div style="
                        color: #7c2d12;
                        font-size: 16px;
                        font-weight: 600;
                        line-height: 1.6;
                        overflow-wrap: anywhere;
                    ">
                        @if($closesAt)
                            {{ $closesAt->format('M d, Y') }}
                            <br>
                            {{ $closesAt->format('h:i A') }}
                        @else
                            No closing restriction
                        @endif
                    </div>
                </div>
            </div>

            @if(!$opensAt && !$closesAt)
                <p style="
                    margin: 16px 0 0;
                    padding-top: 14px;
                    border-top: 1px solid #e5e7eb;
                    color: #6b7280;
                    font-size: 13px;
                    line-height: 1.6;
                ">
                    Group creation has no scheduled time restrictions for this classroom.
                </p>
            @endif

            @if($alreadyInGroup)
                <p style="
                    margin: 16px 0 0;
                    padding-top: 14px;
                    border-top: 1px solid #e5e7eb;
                    color: #166534;
                    font-size: 13px;
                    line-height: 1.6;
                ">
                    You are already a member of a group in this classroom. You cannot create another group here.
                </p>
            @elseif($groupCreationNotOpen)
                <p style="margin: 16px 0 0; color: #9a3412; font-size: 13px; line-height: 1.6;">
                    Group creation will become available when the opening date and time are reached.
                </p>
            @elseif($groupCreationClosed)
                <p style="margin: 16px 0 0; color: #b42318; font-size: 13px; line-height: 1.6;">
                    The group creation period has ended.
                </p>
            @endif
        </section>

        @if(session('success'))
            <div class="lt-alert lt-alert--success" role="status" data-autohide="6000">
                <span class="lt-alert__icon">✓</span>
                <div>{{ session('success') }}</div>
            </div>
        @endif

        {{-- Responsive Group Creation Error Modal --}}
        @if(session('error'))
            <div
                id="groupCreationErrorModal"
                style="
                    position: fixed;
                    inset: 0;
                    width: 100%;
                    height: 100%;
                    height: 100dvh;
                    background: rgba(0, 0, 0, 0.65);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    z-index: 99999;
                    padding: 16px;
                    box-sizing: border-box;
                    overflow-y: auto;
                "
                role="alertdialog"
                aria-modal="true"
                aria-labelledby="groupCreationErrorTitle"
            >
                <div style="
                    background: #fff;
                    width: 100%;
                    max-width: 480px;
                    max-height: calc(100dvh - 32px);
                    overflow-y: auto;
                    padding: clamp(20px, 5vw, 32px);
                    border-radius: 18px;
                    text-align: center;
                    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
                    box-sizing: border-box;
                    margin: auto;
                ">

                    <div style="
                        width: 64px;
                        height: 64px;
                        margin: 0 auto 18px;
                        border-radius: 50%;
                        background: #fff1f0;
                        color: #b42318;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        font-size: 34px;
                    " aria-hidden="true">
                        &#9888;
                    </div>

                    <h2
                        id="groupCreationErrorTitle"
                        style="
                            margin: 0 0 14px;
                            color: #b42318;
                            font-size: clamp(20px, 4vw, 26px);
                            line-height: 1.3;
                            overflow-wrap: anywhere;
                        "
                    >
                        Group Creation Unavailable
                    </h2>

                    <p style="
                        margin: 0 0 26px;
                        color: #555;
                        font-size: 16px;
                        line-height: 1.7;
                        overflow-wrap: anywhere;
                    ">
                        {{ session('error') }}
                    </p>

                    <button
                        type="button"
                        class="lt-btn lt-btn--green"
                        style="
                            width: 100%;
                            max-width: 240px;
                            min-height: 46px;
                            justify-content: center;
                        "
                        onclick="document.getElementById('groupCreationErrorModal').remove()"
                    >
                        OK, Got It
                    </button>

                </div>
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
