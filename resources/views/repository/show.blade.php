<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $capstone->title }} - LogiTrack Repository</title>
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
                    <a href="{{ route('dashboard') }}" class="lt-nav__link">Dashboard</a>
                    <a href="{{ route('repository.index') }}" class="lt-nav__link is-active">Repository</a>
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

        <a href="{{ route('repository.index') }}" class="lt-back">← Back to Repository</a>

        <div class="lt-page-head">
            <div class="lt-page-head__row">
                <div>
                    <span class="lt-eyebrow">LogiTrack Repository</span>
                    <h1>{{ $capstone->title }}</h1>
                </div>
                <span class="lt-badge lt-badge--approved">Approved</span>
            </div>
        </div>

        <div class="lt-review">

            <div class="lt-review__stack">

                <section class="lt-card">
                    <h2 class="lt-card__title">Abstract</h2>
                    <p class="lt-doc__abstract lt-mt">{{ $capstone->abstract }}</p>
                </section>

                <section class="lt-card">
                    <h2 class="lt-card__title">Authors</h2>
                    <ul class="lt-authors lt-mt">
                        @foreach($capstone->groupRoom->members as $member)
                            <li>
                                <span class="lt-avatar">{{ strtoupper(substr($member->user->first_name, 0, 1)) }}</span>
                                {{ $member->user->first_name }}
                                {{ $member->user->last_name }}

                                @if($member->is_leader)
                                    <strong>(Leader)</strong>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </section>

            </div>

            <aside class="lt-panel-sticky">

                <section class="lt-auto">
                    <div class="lt-auto__title">Capstone Information</div>

                    <div class="lt-auto__grid lt-auto__grid--stack">
                        <div class="lt-auto__item">
                            <div class="lt-auto__key">Program</div>
                            <div class="lt-auto__val">{{ $capstone->program }}</div>
                        </div>

                        <div class="lt-auto__item">
                            <div class="lt-auto__key">Year &amp; Section</div>
                            <div class="lt-auto__val">{{ $capstone->year_section ?? 'N/A' }}</div>
                        </div>

                        <div class="lt-auto__item">
                            <div class="lt-auto__key">Publication Date</div>
                            <div class="lt-auto__val">{{ optional($capstone->publication_date)->format('F d, Y') ?? 'N/A' }}</div>
                        </div>

                        <div class="lt-auto__item">
                            <div class="lt-auto__key">Instructor / Adviser</div>
                            <div class="lt-auto__val">
                                {{ $capstone->groupRoom->classroom->instructor->first_name }}
                                {{ $capstone->groupRoom->classroom->instructor->last_name }}
                            </div>
                        </div>

                        <div class="lt-auto__item">
                            <div class="lt-auto__key">Group</div>
                            <div class="lt-auto__val">{{ $capstone->groupRoom->group_name }}</div>
                        </div>
                    </div>
                </section>

                <div class="lt-repo-status">
                    <div class="lt-repo-status__label">Repository Status: <strong>Approved</strong></div>
                    <p>
                        This capstone has been reviewed and approved for inclusion
                        in the LogiTrack repository.
                    </p>
                </div>

            </aside>

        </div>

    </main>

</body>
</html>