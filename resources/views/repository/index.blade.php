<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>LogiTrack Repository</title>
    @vite(['resources/css/logitrack.css', 'resources/js/logitrack.js'])
</head>

<body class="lt-body">

    @php
        $user = auth()->user();

        $search = $search ?? request('search', '');
        $year = $year ?? request('year', '');
        $years = $years ?? collect();
        $capstones = $capstones ?? collect();

        // Use the correct repository routes for the logged-in role.
        $repositoryIndexRoute = $user && $user->role === 'instructor'
            ? 'instructor.repository.index'
            : 'repository.index';

        $repositoryShowRoute = $user && $user->role === 'instructor'
            ? 'instructor.repository.show'
            : 'repository.show';
    @endphp

    <header class="lt-nav lt-nav--landing">
        <div class="lt-nav__inner">
            <a href="{{ route('dashboard') }}" class="lt-brand">
                <span class="lt-brand__mark">L</span>
                LogiTrack
            </a>

            <button
                type="button"
                class="lt-nav__toggle"
                data-nav-toggle="#nav-menu"
                aria-label="Toggle menu"
                aria-expanded="false"
            >
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                    <path d="M4 7h16M4 12h16M4 17h16"/>
                </svg>
            </button>

            <div class="lt-nav__menu" id="nav-menu">
                <nav class="lt-nav__links lt-nav__links--app" aria-label="Main">
                    <a
                        href="{{ route('dashboard') }}"
                        class="lt-nav__link {{ request()->routeIs('dashboard') ? 'is-active' : '' }}"
                    >
                        Dashboard
                    </a>

                    @if($user && $user->role === 'instructor')
                        <a
                            href="{{ route('instructor.classrooms') }}"
                            class="lt-nav__link {{ request()->routeIs('instructor.classrooms*', 'instructor.classroom.*') ? 'is-active' : '' }}"
                        >
                            Classroom
                        </a>

                        <a
                            href="{{ route('instructor.capstones') }}"
                            class="lt-nav__link {{ request()->routeIs('instructor.capstones*', 'instructor.capstone.*') ? 'is-active' : '' }}"
                        >
                            Capstone Submissions
                        </a>

                        <a
                            href="{{ route('instructor.students') }}"
                            class="lt-nav__link {{ request()->routeIs('instructor.students*') ? 'is-active' : '' }}"
                        >
                            Pending Students
                        </a>

                    @elseif($user && $user->role === 'student')
                        <a
                            href="{{ route('student.classroom.redirect') }}"
                            class="lt-nav__link {{ request()->routeIs('student.group-rooms.*', 'student.group-room.*', 'student.capstone.*', 'student.pending') ? 'is-active' : '' }}"
                        >
                            Classroom
                        </a>

                        <a
                            href="{{ route('similarity.index') }}"
                            class="lt-nav__link {{ request()->routeIs('similarity.*') ? 'is-active' : '' }}"
                        >
                            Similarity Checker
                        </a>

                    @elseif($user && $user->role === 'admin')
                        <a
                            href="{{ route('admin.capstones') }}"
                            class="lt-nav__link {{ request()->routeIs('admin.capstones*', 'admin.capstone.*') ? 'is-active' : '' }}"
                        >
                            Capstone Review
                        </a>
                    @endif
                </nav>

                <div class="lt-nav__actions">
                    <a
                        href="{{ route('profile.edit') }}"
                        class="lt-user-chip lt-user-chip--link"
                        aria-label="Edit profile"
                    >
                        <span class="lt-avatar">
                            {{ strtoupper(substr($user->first_name ?? 'U', 0, 1)) }}
                        </span>
                        {{ $user->first_name ?? 'User' }}
                    </a>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="lt-btn lt-btn--outline-light lt-btn--sm">
                            Log Out
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <main class="lt-container lt-animate">

        <div class="lt-page-head">
            <span class="lt-eyebrow">Repository</span>
            <h1>LogiTrack Repository</h1>
            <p class="lt-page-head__sub">
                Browse approved capstone projects in the repository.
            </p>
        </div>

        <form
            method="GET"
            action="{{ route($repositoryIndexRoute) }}"
            class="lt-repo-search"
            data-submit-guard
            data-year-filter-submit
        >
            <label for="search" class="lt-label">Search Capstone Projects</label>

            <div class="lt-repo-search__row">
                <div class="lt-repo-search__box">
                    <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M15.5 14h-.79l-.28-.27a6.5 6.5 0 1 0-.71.71l.27.28v.78l5 5L20.49 20l-4.99-5zm-5 0A4.5 4.5 0 1 1 10.5 5a4.5 4.5 0 0 1 0 9z"/>
                    </svg>

                    <div class="lt-search-filter" data-year-filter-menu>
                        <input
                            type="text"
                            id="search"
                            name="search"
                            class="lt-input"
                            value="{{ $search }}"
                            placeholder="Search by title, abstract, program..."
                        >

                        <button
                            type="button"
                            class="lt-search-filter__toggle"
                            data-year-filter-toggle
                            aria-label="Choose study year"
                            aria-expanded="false"
                            aria-controls="repository-year-filter"
                        >
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M4 5h16l-6.5 7.5V19l-3 1v-7.5L4 5z"/>
                            </svg>
                        </button>

                        <div
                            class="lt-search-filter__menu"
                            id="repository-year-filter"
                            role="group"
                            aria-label="Study year"
                            hidden
                        >
                            <button
                                type="button"
                                class="lt-search-filter__option"
                                data-year-filter-option=""
                                aria-pressed="{{ $year ? 'false' : 'true' }}"
                            >
                                All years
                            </button>

                            @foreach($years as $studyYear)
                                <button
                                    type="button"
                                    class="lt-search-filter__option"
                                    data-year-filter-option="{{ $studyYear }}"
                                    aria-pressed="{{ (string) $year === (string) $studyYear ? 'true' : 'false' }}"
                                >
                                    {{ $studyYear }}
                                </button>
                            @endforeach
                        </div>

                        <input
                            type="hidden"
                            name="year"
                            value="{{ $year }}"
                            data-year-filter-select
                        >
                    </div>
                </div>

                <button
                    type="submit"
                    class="lt-btn lt-btn--primary"
                    data-loading-text="Searching..."
                >
                    Search
                </button>

                @if($search || $year)
                    <a
                        href="{{ route($repositoryIndexRoute) }}"
                        class="lt-btn lt-btn--ghost"
                    >
                        Clear
                    </a>
                @endif
            </div>
        </form>

        @if($capstones->count() > 0)

            <div class="lt-repo-list">
                @foreach($capstones as $capstone)

                    @php
                        $isOld = isset($capstone->source_type)
                            && $capstone->source_type === 'old';

                        $showRoute = route($repositoryShowRoute, [
                            'capstoneId' => $capstone->id,
                            'type' => $isOld ? 'old' : 'modern',
                        ]);
                    @endphp

                    <article class="lt-card lt-repo-card">

                        <div class="lt-repo-card__head">
                            <h2 class="lt-repo-card__title">
                                <a href="{{ $showRoute }}">
                                    {{ $capstone->title }}
                                </a>
                            </h2>

                            <div class="flex items-center gap-2">
                                @if($isOld)
                                    <span
                                        class="lt-badge lt-badge--secondary"
                                        title="Legacy Scanned Record"
                                    >
                                        OCR Scanned
                                    </span>
                                @endif

                                <span
                                    class="lt-badge lt-badge--approved"
                                    title="Repository Status"
                                >
                                    Approved
                                </span>
                            </div>
                        </div>

                        <dl class="lt-repo-meta">
                            <div>
                                <dt>Program</dt>
                                <dd>{{ $capstone->program ?? 'N/A' }}</dd>
                            </div>

                            <div>
                                <dt>Instructor / Adviser</dt>
                                <dd>
                                    @if($isOld)
                                        {{ $capstone->adviser ?? 'N/A' }}
                                    @else
                                        {{ $capstone->groupRoom?->classroom?->instructor?->first_name ?? '' }}
                                        {{ $capstone->groupRoom?->classroom?->instructor?->last_name ?? '' }}
                                    @endif
                                </dd>
                            </div>

                            <div>
                                <dt>Publication Date</dt>
                                <dd>
                                    @if($isOld)
                                        {{ $capstone->publication_date ?? 'N/A' }}
                                    @else
                                        {{ $capstone->publication_date?->format('F d, Y') ?? 'N/A' }}
                                    @endif
                                </dd>
                            </div>
                        </dl>

                        <div class="lt-repo-block">
                            <div class="lt-doc__label">Authors</div>

                            <ul class="lt-authors">
                                @if($isOld)
                                    @foreach($capstone->authors ?? [] as $authorName)
                                        <li>
                                            <span class="lt-avatar">
                                                {{ strtoupper(substr($authorName, 0, 1)) }}
                                            </span>
                                            {{ $authorName }}
                                        </li>
                                    @endforeach
                                @else
                                    @foreach($capstone->groupRoom?->members ?? [] as $member)
                                        <li>
                                            <span class="lt-avatar">
                                                {{ strtoupper(substr($member->user?->first_name ?? 'U', 0, 1)) }}
                                            </span>
                                            {{ $member->user?->first_name ?? '' }}
                                            {{ $member->user?->last_name ?? '' }}
                                        </li>
                                    @endforeach
                                @endif
                            </ul>
                        </div>

                        <div class="lt-repo-block">
                            <div class="lt-doc__label">Abstract</div>
                            <p class="lt-repo-abstract">
                                {{ \Illuminate\Support\Str::limit($capstone->abstract ?? '', 250) }}
                            </p>
                        </div>

                        <div class="lt-repo-card__foot">
                            <a
                                href="{{ $showRoute }}"
                                class="lt-btn lt-btn--primary lt-btn--sm"
                            >
                                View Details
                            </a>
                        </div>

                    </article>

                @endforeach
            </div>

            @if(method_exists($capstones, 'links'))
                <div class="lt-pager">
                    {{ $capstones->withQueryString()->links() }}
                </div>
            @endif

        @else

            <div class="lt-empty">
                <div class="lt-empty__icon">&#9776;</div>
                <div class="lt-empty__title">Nothing to show</div>
                <p>
                    No approved capstone projects are currently available
                    in the repository.
                </p>
            </div>

        @endif

    </main>

</body>
</html>
