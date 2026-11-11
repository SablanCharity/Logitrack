
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

        $isDashboard = $isDashboard ?? false;
        $showDashboardOverview = $showDashboardOverview ?? false;

        $search = $search ?? request('search', '');
        $year = $year ?? request('year', '');
        $program = $program ?? request('program', '');
        $programs = $programs ?? collect();
        $adviser = $adviser ?? request('adviser', '');
        $advisers = $advisers ?? collect();
        $sort = $sort ?? request('sort', 'newest');
        $perPage = $perPage ?? (int) request('per_page', 5);
        $years = $years ?? collect();
        $capstones = $capstones ?? collect();

        // Dashboard metric fallbacks.
        // These values should be calculated and passed by RepositoryController.
        $totalCapstoneStudies = $totalCapstoneStudies ?? 0;
        $totalBsitCapstones = $totalBsitCapstones ?? 0;
        $totalCsTheses = $totalCsTheses ?? 0;

        $dashboardStudies = $dashboardStudies ?? [
            'best' => null,
            'most_viewed' => null,
        ];

        $repositoryIndexRoute = $user && $user->role === 'instructor'
            ? 'instructor.repository.index'
            : 'repository.index';

        $repositoryShowRoute = $user && $user->role === 'instructor'
            ? 'instructor.repository.show'
            : 'repository.show';
    @endphp

    @include('components.site-navigation')

    <main class="lt-container lt-animate">

        @if(request()->query('return_to') === 'similarity')
            <a href="{{ route('similarity.index') }}" class="lt-back">&larr; Back</a>
        @endif

        <div class="lt-page-head {{ $isDashboard ? 'lt-page-head--dashboard' : '' }}">
            @unless($isDashboard)
                <span class="lt-eyebrow">Repository</span>
            @endunless

            <h1>{{ $isDashboard ? 'LogiTrack Capstone Repository' : 'LogiTrack Repository' }}</h1>
            <p class="lt-page-head__sub">Browse approved capstone projects in the repository.</p>
        </div>

        <form
            method="GET"
            action="{{ route($repositoryIndexRoute) }}"
            class="lt-repo-search"
            data-submit-guard
            id="repository-search-form"
        >
            <label for="search" class="lt-label">Search Capstone Projects</label>

            <div class="lt-repo-search__row">
                <div class="lt-repo-search__box">

                    <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M15.5 14h-.79l-.28-.27a6.5 6.5 0 1 0-.71.71l.27.28v.78l5 5L20.49 20l-4.99-5zm-5 0A4.5 4.5 0 1 1 10.5 5a4.5 4.5 0 0 1 0 9z"/>
                    </svg>

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
                        id="open-repository-filters"
                        aria-label="Open search filters"
                        aria-haspopup="true"
                        aria-expanded="false"
                        aria-controls="repository-filter-dropdown"
                    >
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M4 5h16l-6.5 7.5V19l-3 1v-7.5L4 5z"/>
                        </svg>
                    </button>

                    <div
                        class="lt-filter-dropdown"
                        id="repository-filter-dropdown"
                        aria-label="Search filters"
                        hidden
                    >
                        <div class="lt-filter-dropdown__heading">Filter studies</div>

                        <label class="lt-filter-field">
                            <span>Program</span>
                            <select name="program" class="lt-input">
                                <option value="">All programs</option>

                                @foreach($programs as $programOption)
                                    @if(!in_array(strtoupper(trim((string) $programOption)), ['BSIT', 'IT', 'CS'], true))
                                        <option
                                            value="{{ $programOption }}"
                                            @selected((string) $program === (string) $programOption)
                                        >
                                            {{ $programOption }}
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                        </label>

                        <label class="lt-filter-field">
                            <span>Adviser</span>
                            <select name="adviser" class="lt-input">
                                <option value="">All advisers</option>

                                @foreach($advisers as $adviserOption)
                                    <option
                                        value="{{ $adviserOption }}"
                                        @selected((string) $adviser === (string) $adviserOption)
                                    >
                                        {{ $adviserOption }}
                                    </option>
                                @endforeach
                            </select>
                        </label>

                        <label class="lt-filter-field">
                            <span>Publication Year</span>
                            <select name="year" class="lt-input">
                                <option value="">All years</option>

                                @foreach($years as $studyYear)
                                    <option
                                        value="{{ $studyYear }}"
                                        @selected((string) $year === (string) $studyYear)
                                    >
                                        {{ $studyYear }}
                                    </option>
                                @endforeach
                            </select>
                        </label>

                        <label class="lt-filter-field">
                            <span>Sort By</span>
                            <select name="sort" class="lt-input">
                                <option value="newest" @selected($sort === 'newest')>Newest first</option>
                                <option value="oldest" @selected($sort === 'oldest')>Oldest first</option>
                                <option value="title_asc" @selected($sort === 'title_asc')>Title: A to Z</option>
                                <option value="title_desc" @selected($sort === 'title_desc')>Title: Z to A</option>
                                <option value="most_viewed" @selected($sort === 'most_viewed')>Most viewed</option>
                            </select>
                        </label>

                        <label class="lt-filter-field">
                            <span>Results Per Page</span>
                            <select name="per_page" class="lt-input">
                                @foreach([5, 10, 20, 50] as $pageSize)
                                    <option
                                        value="{{ $pageSize }}"
                                        @selected((int) $perPage === $pageSize)
                                    >
                                        {{ $pageSize }} results
                                    </option>
                                @endforeach
                            </select>
                        </label>

                        <div class="lt-filter-dropdown__actions">
                            <a href="{{ route($repositoryIndexRoute) }}" class="lt-filter-clear">
                                Clear filters
                            </a>

                            <button
                                type="submit"
                                class="lt-btn lt-btn--primary lt-btn--sm"
                                data-loading-text="Applying..."
                            >
                                Apply
                            </button>
                        </div>
                    </div>
                </div>

                <button
                    type="submit"
                    class="lt-btn lt-btn--primary"
                    data-loading-text="Searching..."
                >
                    Search
                </button>
            </div>
        </form>

        <style>
            .lt-repo-search__row {
                display: flex;
                align-items: stretch;
                gap: 0 !important;
                width: 100%;
            }

            .lt-repo-search__box {
                position: relative;
                display: flex;
                align-items: center;
                flex: 1 1 auto;
                min-width: 0;
                min-height: 48px;
                padding: 0 8px 0 14px;
                border: 1px solid #d9ddcf;
                border-right: 0;
                border-radius: 12px 0 0 12px;
                background: #f7f8f2;
                gap: 10px;
            }

            .lt-repo-search__box > svg {
                flex: 0 0 18px;
                width: 18px;
                height: 18px;
                color: #7a8498;
            }

            .lt-repo-search__box > .lt-input {
                flex: 1 1 auto;
                width: 100%;
                min-width: 0;
                height: 44px;
                padding: 0 4px;
                border: 0 !important;
                outline: 0;
                box-shadow: none !important;
                background: transparent !important;
            }

            .lt-search-filter__toggle {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                flex: 0 0 38px;
                width: 38px;
                height: 38px;
                border: 0;
                border-radius: 9px;
                background: #ecefe5;
                color: #18254c;
                cursor: pointer;
                transition: background .18s ease, color .18s ease;
            }

            .lt-search-filter__toggle:hover,
            .lt-search-filter__toggle[aria-expanded="true"] {
                background: #dfe5d5;
            }

            .lt-search-filter__toggle svg {
                width: 18px;
                height: 18px;
            }

            .lt-repo-search__row > .lt-btn {
                flex: 0 0 auto;
                min-height: 48px;
                margin: 0;
                border-radius: 0 12px 12px 0;
                padding: 0 25px;
                white-space: nowrap;
            }

            .lt-filter-dropdown[hidden] {
                display: none !important;
            }

            .lt-filter-dropdown {
                position: absolute;
                top: calc(100% + 10px);
                right: 0;
                z-index: 1000;
                width: min(300px, calc(100vw - 32px));
                max-height: min(70vh, 500px);
                overflow-y: auto;
                padding: 16px;
                border: 1px solid #e3e7dc;
                border-radius: 14px;
                background: #fff;
                box-shadow: 0 16px 38px rgba(18, 30, 61, .14);
            }

            .lt-filter-dropdown__heading {
                margin: 0 0 14px;
                color: #111b3c;
                font-size: .94rem;
                font-weight: 750;
            }

            .lt-filter-dropdown .lt-filter-field {
                display: grid;
                gap: 6px;
                margin-bottom: 12px;
                color: #39435b;
                font-size: .78rem;
                font-weight: 650;
            }

            .lt-filter-dropdown .lt-filter-field .lt-input {
                width: 100%;
                min-height: 40px;
                padding: 8px 11px;
                border: 1px solid #e1e5eb;
                border-radius: 9px;
                background: #fafbf8;
                color: #202943;
                font-size: .87rem;
            }

            .lt-filter-dropdown .lt-filter-field .lt-input:focus {
                border-color: #9ba88a;
                outline: 3px solid rgba(155, 168, 138, .16);
            }

            .lt-filter-dropdown__actions {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 10px;
                margin-top: 15px;
                padding-top: 13px;
                border-top: 1px solid #edf0f4;
            }

            .lt-filter-clear {
                color: #59647a;
                font-size: .82rem;
                font-weight: 650;
                text-decoration: none;
            }

            .lt-filter-clear:hover {
                text-decoration: underline;
            }

            /* Dashboard Number 02 breakdown */
            .lt-dashboard-stat__breakdown {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 16px;
                margin-top: 16px;
                padding-top: 14px;
                border-top: 1px solid #e8ebdf;
            }

            .lt-dashboard-stat__breakdown-item {
                min-width: 0;
            }

            .lt-dashboard-stat__breakdown-value {
                display: block;
                color: #18254c;
                font-size: 1.5rem;
                font-weight: 800;
                line-height: 1.2;
            }

            .lt-dashboard-stat__breakdown-label {
                display: block;
                margin-top: 5px;
                color: #68738a;
                font-size: .78rem;
                line-height: 1.4;
            }

            @media (max-width: 520px) {
                .lt-repo-search__row > .lt-btn {
                    padding: 0 16px;
                }

                .lt-repo-search__box {
                    padding-left: 10px;
                    gap: 6px;
                }

                .lt-dashboard-stat__breakdown {
                    gap: 10px;
                }
            }
        </style>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const dropdown = document.getElementById('repository-filter-dropdown');
                const toggle = document.getElementById('open-repository-filters');

                if (!dropdown || !toggle) return;

                function closeFilters() {
                    dropdown.hidden = true;
                    toggle.setAttribute('aria-expanded', 'false');
                }

                toggle.addEventListener('click', function (event) {
                    event.stopPropagation();
                    dropdown.hidden = !dropdown.hidden;
                    toggle.setAttribute('aria-expanded', String(!dropdown.hidden));
                });

                dropdown.addEventListener('click', function (event) {
                    event.stopPropagation();
                });

                document.addEventListener('click', function (event) {
                    if (!dropdown.hidden && !dropdown.contains(event.target) && !toggle.contains(event.target)) {
                        closeFilters();
                    }
                });

                document.addEventListener('keydown', function (event) {
                    if (event.key === 'Escape' && !dropdown.hidden) {
                        closeFilters();
                        toggle.focus();
                    }
                });
            });
        </script>

        @if(session('success'))
            <div class="lt-alert lt-alert--success" role="status" data-autohide="6000">
                <span class="lt-alert__icon">&#10003;</span>
                <div>{{ session('success') }}</div>
            </div>
        @endif

        @if($showDashboardOverview)
            <section class="lt-dashboard-stats" aria-label="Repository metrics">

                {{-- NUMBER 01: BEST CAPSTONE STUDY --}}
                <article class="lt-card lt-dashboard-stat">
                    <div class="lt-dashboard-stat__head">
                        <span class="lt-dashboard-stat__label">Best Capstone Study</span>
                        <span class="lt-dashboard-stat__index" aria-hidden="true">01</span>
                    </div>

                    <strong class="lt-dashboard-stat__value">
                        {{ $dashboardStudies['best']?->title ?? 'Not selected' }}
                    </strong>
                </article>

                {{-- NUMBER 02: TOTAL APPROVED STUDIES --}}
                <article class="lt-card lt-dashboard-stat">
                    <div class="lt-dashboard-stat__head">
                        <span class="lt-dashboard-stat__label">
                            Total Number of Capstone Studies
                        </span>
                        <span class="lt-dashboard-stat__index" aria-hidden="true">02</span>
                    </div>

                    <div class="lt-dashboard-stat__content">

                        <div>
                            <strong class="lt-dashboard-stat__value">
                                {{ number_format($totalCapstoneStudies) }}
                            </strong>

                            <span style="display: block; font-size: .82rem; color: #68738a;">
                                Combined Total Studies
                            </span>
                        </div>

                        <div class="lt-dashboard-stat__breakdown">

                            <div class="lt-dashboard-stat__breakdown-item">
                                <strong class="lt-dashboard-stat__breakdown-value">
                                    {{ number_format($totalBsitCapstones) }}
                                </strong>

                                <span class="lt-dashboard-stat__breakdown-label">
                                    Total Capstone (BSIT)
                                </span>
                            </div>

                            <div class="lt-dashboard-stat__breakdown-item">
                                <strong class="lt-dashboard-stat__breakdown-value">
                                    {{ number_format($totalCsTheses) }}
                                </strong>

                                <span class="lt-dashboard-stat__breakdown-label">
                                    Total Thesis (CS)
                                </span>
                            </div>

                        </div>
                    </div>
                </article>

                {{-- NUMBER 03: MOST VIEWED CAPSTONE STUDY --}}
                <article class="lt-card lt-dashboard-stat">
                    <div class="lt-dashboard-stat__head">
                        <span class="lt-dashboard-stat__label">Most Viewed Capstone Study</span>
                        <span class="lt-dashboard-stat__index" aria-hidden="true">03</span>
                    </div>

                    <div class="lt-dashboard-stat__content">
                        <strong class="lt-dashboard-stat__value">
                            {{ $dashboardStudies['most_viewed']?->title ?? 'No studies yet' }}
                        </strong>

                        @if($dashboardStudies['most_viewed'])
                            <span class="lt-dashboard-stat__views">
                                {{ number_format($dashboardStudies['most_viewed']->views_count) }} views
                            </span>
                        @endif
                    </div>
                </article>

            </section>

            @if(auth()->user()->role === 'admin')
                <div class="lt-dashboard-admin-action">
                    <a href="{{ route('repository.index') }}" class="lt-btn lt-btn--outline">
                        Manage repository studies
                    </a>
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

                                    <h2 class="lt-dashboard-feature__label" id="dashboard-feature-title-{{ $feature['key'] }}">
                                        {{ $feature['label'] }}
                                    </h2>
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
                                <h3 class="lt-dashboard-feature__title">
                                    {{ $feature['study']->title }}
                                </h3>

                                <p class="lt-repo-abstract">
                                    {{ \Illuminate\Support\Str::limit($feature['study']->abstract, 320) }}
                                </p>

                                <div class="lt-dashboard-feature__meta">
                                    <span>{{ $feature['study']->program ?? 'Program not specified' }}</span>

                                    @if($feature['key'] === 'most-viewed')
                                        <span>{{ number_format($feature['study']->views_count) }} views</span>
                                    @endif
                                </div>

                                <div class="lt-repo-card__foot">
                                    <a
                                        href="{{ route('repository.show', [
                                            'capstoneId' => $feature['study']->id,
                                            'type' => $feature['study']->source_type,
                                            'return_to' => 'dashboard'
                                        ]) }}"
                                        class="lt-btn lt-btn--primary lt-btn--sm"
                                    >
                                        View Details
                                    </a>
                                </div>
                            @else
                                <p class="lt-dashboard-feature__empty">
                                    {{ $feature['key'] === 'best'
                                        ? 'An admin has not selected a best study yet.'
                                        : 'There are no approved capstone studies yet.' }}
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
                    <a href="{{ route('dashboard') }}" class="lt-back">&larr; Back to Dashboard</a>
                    <h2>Search Results</h2>
                </div>
            @endif

            @if($capstones->count() > 0)

                <div class="lt-repo-list">

                    @foreach($capstones as $capstone)

                        @php
                            $isOld = isset($capstone->source_type) && $capstone->source_type === 'old';

                            $showRoute = route($repositoryShowRoute, array_filter([
                                'capstoneId' => $capstone->id,
                                'type' => $isOld ? 'old' : 'modern',
                                'return_to' => $isDashboard
                                    ? 'dashboard'
                                    : ($user?->role === 'instructor' ? 'instructor_repository' : 'repository'),
                                'search' => $search,
                                'year' => $year,
                                'program' => $program,
                                'adviser' => $adviser,
                                'sort' => $sort,
                                'per_page' => $perPage,
                                'page' => request()->query('page'),
                            ], fn ($value) => $value !== null && $value !== ''));
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
                                        <span class="lt-badge lt-badge--secondary" title="Legacy Scanned Record">
                                            Archived Capstone
                                        </span>
                                    @endif

                                    @if($capstone->is_best)
                                        <span class="lt-badge lt-badge--approved">Best Study</span>
                                    @endif

                                    <span class="lt-badge lt-badge--approved" title="Repository Status">
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

                                @if(auth()->user()?->role === 'admin')
                                    <form
                                        method="POST"
                                        action="{{ route('admin.capstone.set-best', [
                                            'capstoneId' => $capstone->id,
                                            'type' => $capstone->source_type
                                        ]) }}"
                                    >
                                        @csrf
                                        @method('PATCH')

                                        <button
                                            type="submit"
                                            class="lt-btn {{ $capstone->is_best ? 'lt-btn--ghost' : 'lt-btn--outline' }} lt-btn--sm"
                                            @disabled($capstone->is_best)
                                        >
                                            {{ $capstone->is_best ? 'Selected as Best' : 'Set as Best' }}
                                        </button>
                                    </form>
                                @endif

                                <a href="{{ $showRoute }}" class="lt-btn lt-btn--primary lt-btn--sm">
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

        @endif

    </main>

</body>
</html>

