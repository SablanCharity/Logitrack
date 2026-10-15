<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>LogiTrack Repository</title>
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
                <nav class="lt-nav__links lt-nav__links--app" aria-label="Main">
                    <a href="{{ route('dashboard') }}" class="lt-nav__link {{ request()->routeIs('dashboard') ? 'is-active' : '' }}">Dashboard</a>

                    @if(auth()->user()->role === 'instructor')
                        <a href="{{ route('instructor.classrooms') }}" class="lt-nav__link {{ request()->routeIs('instructor.classrooms*', 'instructor.classroom.*') ? 'is-active' : '' }}">Classroom</a>
                        <a href="{{ route('instructor.capstones') }}" class="lt-nav__link {{ request()->routeIs('instructor.capstones*', 'instructor.capstone.*') ? 'is-active' : '' }}">Capstone Submissions</a>
                        <a href="{{ route('instructor.students') }}" class="lt-nav__link {{ request()->routeIs('instructor.students*') ? 'is-active' : '' }}">Pending Students</a>
                    @elseif(auth()->user()->role === 'student')
                        <a href="{{ $classroomId ? route('student.group-rooms.index', $classroomId) : route('student.pending') }}" class="lt-nav__link {{ request()->routeIs('student.group-rooms.*', 'student.group-room.*', 'student.capstone.*', 'student.pending') ? 'is-active' : '' }}">Classroom</a>
                        <a href="{{ route('similarity.index') }}" class="lt-nav__link {{ request()->routeIs('similarity.*') ? 'is-active' : '' }}">Similarity Checker</a>
                    @endif
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

        @if(request()->query('return_to') === 'similarity')
            <a href="{{ route('similarity.index') }}" class="lt-back">⟵ Back</a>
        @endif

        <div class="lt-page-head {{ $isDashboard ? 'lt-page-head--dashboard' : '' }}">
            @unless($isDashboard)
                <span class="lt-eyebrow">Repository</span>
            @endunless
            <h1>{{ $isDashboard ? 'LogiTrack Capstone Repository' : 'LogiTrack Repository' }}</h1>
            <p class="lt-page-head__sub">Browse approved capstone projects in the repository.</p>
        </div>

        <form method="GET" action="{{ route(request()->routeIs('dashboard') ? 'dashboard' : 'repository.index') }}" class="lt-repo-search" data-submit-guard data-year-filter-submit>
            <label for="search" class="lt-label">Search Capstone Projects</label>

            <div class="lt-repo-search__row">
                <div class="lt-repo-search__box">
                    <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M15.5 14h-.79l-.28-.27a6.5 6.5 0 1 0-.71.71l.27.28v.78l5 5L20.49 20l-4.99-5zm-5 0A4.5 4.5 0 1 1 10.5 5a4.5 4.5 0 0 1 0 9z"/></svg>
                    <div class="lt-search-filter" data-year-filter-menu>
                        <input
                            type="text"
                            id="search"
                            name="search"
                            class="lt-input"
                            value="{{ $search }}"
                            placeholder="Search by title, abstract, program..."
                        >
                        <button type="button" class="lt-search-filter__toggle" data-year-filter-toggle aria-label="Choose study year" aria-expanded="false" aria-controls="repository-year-filter">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 5h16l-6.5 7.5V19l-3 1v-7.5L4 5z"/></svg>
                        </button>
                        <div class="lt-search-filter__menu" id="repository-year-filter" role="group" aria-label="Study year" hidden>
                            <button type="button" class="lt-search-filter__option" data-year-filter-option="" aria-pressed="{{ $year ? 'false' : 'true' }}">All years</button>
                            @foreach($years as $studyYear)
                                <button type="button" class="lt-search-filter__option" data-year-filter-option="{{ $studyYear }}" aria-pressed="{{ (string) $year === (string) $studyYear ? 'true' : 'false' }}">{{ $studyYear }}</button>
                            @endforeach
                        </div>
                        <input type="hidden" name="year" value="{{ $year }}" data-year-filter-select>
                    </div>
                </div>

                <button type="submit" class="lt-btn lt-btn--primary" data-loading-text="Searching...">Search</button>

                @if($search || $year)
                    <a href="{{ route(request()->routeIs('dashboard') ? 'dashboard' : 'repository.index') }}" class="lt-btn lt-btn--ghost">Clear</a>
                @endif
            </div>
        </form>

        @if(session('success'))
            <div class="lt-alert lt-alert--success" role="status" data-autohide="6000">
                <span class="lt-alert__icon">✓</span>
                <div>{{ session('success') }}</div>
            </div>
        @endif

        @if($showDashboardOverview)
            <section class="lt-dashboard-stats" aria-label="Repository metrics">
                <article class="lt-card lt-dashboard-stat">
                    <div class="lt-dashboard-stat__head">
                        <span class="lt-dashboard-stat__label">Best Capstone Study</span>
                        <span class="lt-dashboard-stat__index" aria-hidden="true">01</span>
                    </div>
                    <strong class="lt-dashboard-stat__value">{{ $dashboardStudies['best']?->title ?? 'Not selected' }}</strong>
                </article>
                <article class="lt-card lt-dashboard-stat">
                    <div class="lt-dashboard-stat__head">
                        <span class="lt-dashboard-stat__label">Total Number of Capstone Studies</span>
                        <span class="lt-dashboard-stat__index" aria-hidden="true">02</span>
                    </div>
                    <strong class="lt-dashboard-stat__value">{{ number_format($totalCapstoneStudies) }}</strong>
                </article>
                <article class="lt-card lt-dashboard-stat">
                    <div class="lt-dashboard-stat__head">
                        <span class="lt-dashboard-stat__label">Most Viewed Capstone Study</span>
                        <span class="lt-dashboard-stat__index" aria-hidden="true">03</span>
                    </div>
                    <div class="lt-dashboard-stat__content">
                        <strong class="lt-dashboard-stat__value">{{ $dashboardStudies['most_viewed']?->title ?? 'No studies yet' }}</strong>
                        @if($dashboardStudies['most_viewed'])
                            <span class="lt-dashboard-stat__views">{{ number_format($dashboardStudies['most_viewed']->views_count) }} views</span>
                        @endif
                    </div>
                </article>
            </section>

            @if(auth()->user()->role === 'admin')
                <div class="lt-dashboard-admin-action">
                    <a href="{{ route('repository.index') }}" class="lt-btn lt-btn--outline">Manage repository studies</a>
                </div>
            @endif

            <section class="lt-dashboard-featured" aria-label="Featured capstone studies" data-dashboard-featured>
                <div class="lt-dashboard-featured__track" data-dashboard-featured-track>
                    @foreach([
                        ['label' => 'Best Capstone Study', 'study' => $dashboardStudies['best'], 'key' => 'best'],
                        ['label' => 'Most Viewed Capstone Study', 'study' => $dashboardStudies['most_viewed'], 'key' => 'most-viewed'],
                    ] as $index => $feature)
                        <article
                            class="lt-card lt-dashboard-feature"
                            id="dashboard-feature-{{ $feature['key'] }}"
                            data-dashboard-feature-panel
                            aria-labelledby="dashboard-feature-title-{{ $feature['key'] }}"
                            aria-hidden="{{ $index === 0 ? 'false' : 'true' }}"
                            @if($index > 0) inert @endif
                        >
                            <div class="lt-dashboard-feature__heading">
                                <div>
                                    <span class="lt-eyebrow">Featured study 0{{ $index + 1 }} / 02</span>
                                    <h2 class="lt-dashboard-feature__label" id="dashboard-feature-title-{{ $feature['key'] }}">{{ $feature['label'] }}</h2>
                                </div>
                                <button
                                    type="button"
                                    class="lt-dashboard-next"
                                    data-dashboard-feature-next
                                    aria-label="Show {{ $index === 0 ? 'most viewed capstone study' : 'best capstone study' }}"
                                >
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        @if($index === 0)
                                            <path d="M5 12h14m-6-6 6 6-6 6"/>
                                        @else
                                            <path d="M19 12H5m6 6-6-6 6-6"/>
                                        @endif
                                    </svg>
                                </button>
                            </div>
                            @if($feature['study'])
                                <h3 class="lt-dashboard-feature__title">{{ $feature['study']->title }}</h3>
                                <p class="lt-repo-abstract">{{ \Illuminate\Support\Str::limit($feature['study']->abstract, 320) }}</p>
                                <div class="lt-dashboard-feature__meta">
                                    <span>{{ $feature['study']->program ?? 'Program not specified' }}</span>
                                    @if($feature['key'] === 'most-viewed')
                                        <span>{{ number_format($feature['study']->views_count) }} views</span>
                                    @endif
                                </div>
                                <div class="lt-repo-card__foot">
                                    <a href="{{ route('repository.show', ['capstoneId' => $feature['study']->id, 'return_to' => 'dashboard']) }}" class="lt-btn lt-btn--primary lt-btn--sm">View Details</a>
                                </div>
                            @else
                                <p class="lt-dashboard-feature__empty">
                                    {{ $feature['key'] === 'best' ? 'An admin has not selected a best study yet.' : 'There are no approved capstone studies yet.' }}
                                </p>
                            @endif
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        @if(!$isDashboard || !$showDashboardOverview)
            @if($isDashboard)
                <div class="lt-page-head lt-dashboard-results-head">
                    <a href="{{ route('dashboard') }}" class="lt-back">⟵ Back to Dashboard</a>
                    <h2>Search Results</h2>
                </div>
            @endif

        @if($capstones->count() > 0)

            <div class="lt-repo-list">
                @foreach($capstones as $capstone)

                    @php
                        $isOld = isset($capstone->source_type) && $capstone->source_type === 'old';
                        $showRoute = route('repository.show', array_filter([
                            'capstoneId' => $capstone->id,
                            'type' => $isOld ? 'old' : 'modern',
                            'return_to' => $isDashboard ? 'dashboard' : 'repository',
                            'search' => $search,
                            'year' => $year,
                            'page' => request()->query('page'),
                        ], fn ($value) => $value !== null && $value !== ''));
                    @endphp

                    <article class="lt-card lt-repo-card">

                        <div class="lt-repo-card__head">
                            <h2 class="lt-repo-card__title">
                                <a href="{{ $showRoute }}">{{ $capstone->title }}</a>
                            </h2>
                            <div class="flex items-center gap-2">
                                @if($isOld)
                                    <span class="lt-badge lt-badge--secondary" title="Legacy Scanned Record">OCR Scanned</span>
                                @endif
                                @if(!$isOld && $capstone->is_best)
                                    <span class="lt-badge lt-badge--approved">Best Study</span>
                                @endif
                                <span class="lt-badge lt-badge--approved" title="Repository Status">Approved</span>
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
                                        {{ optional($capstone->groupRoom?->classroom?->instructor)->first_name }}
                                        {{ optional($capstone->groupRoom?->classroom?->instructor)->last_name }}
                                    @endif
                                </dd>
                            </div>
                            <div>
                                <dt>Publication Date</dt>
                                <dd>
                                    @if($isOld)
                                        {{ $capstone->publication_date ?? 'N/A' }}
                                    @else
                                        {{ optional($capstone->publication_date)->format('F d, Y') ?? 'N/A' }}
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
                                            <span class="lt-avatar">{{ strtoupper(substr($authorName, 0, 1)) }}</span>
                                            {{ $authorName }}
                                        </li>
                                    @endforeach
                                @else
                                    @foreach($capstone->groupRoom?->members ?? [] as $member)
                                        <li>
                                            <span class="lt-avatar">{{ strtoupper(substr($member->user->first_name, 0, 1)) }}</span>
                                            {{ $member->user->first_name }}
                                            {{ $member->user->last_name }}
                                        </li>
                                    @endforeach
                                @endif
                            </ul>
                        </div>

                        <div class="lt-repo-block">
                            <div class="lt-doc__label">Abstract</div>
                            <p class="lt-repo-abstract">{{ \Illuminate\Support\Str::limit($capstone->abstract, 250) }}</p>
                        </div>

                        <div class="lt-repo-card__foot">
                            @if(!$isOld && auth()->user()->role === 'admin')
                                <form method="POST" action="{{ route('admin.capstone.set-best', $capstone->id) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="lt-btn {{ $capstone->is_best ? 'lt-btn--ghost' : 'lt-btn--outline' }} lt-btn--sm" @disabled($capstone->is_best)>
                                        {{ $capstone->is_best ? 'Selected as Best' : 'Set as Best' }}
                                    </button>
                                </form>
                            @endif
                            <a href="{{ $showRoute }}" class="lt-btn lt-btn--primary lt-btn--sm">View Details</a>
                        </div>

                    </article>

                @endforeach
            </div>

            <div class="lt-pager">
                {{ $capstones->withQueryString()->links() }}
            </div>

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
        @endif

    </main>

</body>
</html>