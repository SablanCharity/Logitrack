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
                    <a href="{{ route('repository.index') }}" class="lt-nav__link {{ request()->routeIs('repository.*') ? 'is-active' : '' }}">Repository</a>
                    <a href="{{ route('instructor.classrooms') }}" class="lt-nav__link {{ request()->routeIs('instructor.classrooms*', 'instructor.classroom.*') ? 'is-active' : '' }}">Classroom</a>
                    <a href="{{ route('instructor.capstones') }}" class="lt-nav__link {{ request()->routeIs('instructor.capstones*', 'instructor.capstone.*') ? 'is-active' : '' }}">Capstone Submissions</a>
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
                    <a href="{{ route('profile.edit') }}" class="lt-btn lt-btn--outline-light lt-btn--sm">Profile</a>
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

            <div class="lt-card__head lt-card__head--search">
                <div class="lt-search-filter" data-year-filter-menu>
                    <input
                        type="search"
                        class="lt-input"
                        placeholder="Search title, group, student..."
                        aria-label="Search capstone submissions"
                        data-table-filter="#capstone-table"
                        data-filter-year="#capstone-year"
                        data-filter-empty="#capstone-filter-empty"
                    >
                    <button type="button" class="lt-search-filter__toggle" data-year-filter-toggle aria-label="Choose study year" aria-expanded="false" aria-controls="capstone-year-filter">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 5h16l-6.5 7.5V19l-3 1v-7.5L4 5z"/></svg>
                    </button>
                    <div class="lt-search-filter__menu" id="capstone-year-filter" role="group" aria-label="Study year" hidden>
                        <button type="button" class="lt-search-filter__option" data-year-filter-option="" aria-pressed="true">All years</button>
                        @foreach($years as $studyYear)
                            <button type="button" class="lt-search-filter__option" data-year-filter-option="{{ $studyYear }}" aria-pressed="false">{{ $studyYear }}</button>
                        @endforeach
                    </div>
                    <input type="hidden" id="capstone-year" value="" data-year-filter-select>
                </div>
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
                            <th>Action</th>
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

                            <tr data-study-year="{{ $capstone->publication_date?->format('Y') }}">
                                <td style="min-width: 220px;">
                                    <a href="{{ route('instructor.capstone.review', $capstone->id) }}" class="lt-table__title">
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

                                <td>
                                    <a href="{{ route('instructor.capstone.review', $capstone->id) }}" class="lt-btn lt-btn--primary lt-btn--sm">
                                        Review Study
                                    </a>
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