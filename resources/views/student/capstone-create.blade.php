<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Submit Capstone · LogiTrack</title>
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

    <main class="lt-container lt-container--narrow lt-animate">

        <a href="{{ route('student.group-room.show', $groupRoom->id) }}" class="lt-back">← Back to group room</a>

        <div class="lt-page-head">
            <span class="lt-eyebrow">Capstone Submission</span>
            <h1>Submit Capstone</h1>
            <p class="lt-page-head__sub">Enter your capstone details. Your adviser and the admin will review them before they appear in the repository.</p>
        </div>

        <div class="lt-chips">
            <div class="lt-chip">
                <span class="lt-chip__label">Classroom</span>
                <span class="lt-chip__value">{{ $groupRoom->classroom->classroom_name }}</span>
            </div>
            <div class="lt-chip">
                <span class="lt-chip__label">Group</span>
                <span class="lt-chip__value">{{ $groupRoom->group_name }}</span>
            </div>
        </div>

        @if($errors->any())
            <div class="lt-alert lt-alert--error" role="alert">
                <span class="lt-alert__icon">!</span>
                <div>
                    <strong>Please fix the following:</strong>

                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="lt-alert lt-alert--error" role="alert">
                <span class="lt-alert__icon">!</span>
                <div>{{ session('error') }}</div>
            </div>
        @endif

        <form method="POST"
              action="{{ route('student.capstone.store', $groupRoom->id) }}"
              class="lt-form"
              data-submit-guard
              data-warn-unsaved>

            @csrf

            <section class="lt-card">
                <h2 class="lt-card__title">Capstone Information</h2>
                <p class="lt-card__desc">Fill in the details exactly as written in your approved hardbound study.</p>

                <div class="lt-form">

                    <div class="lt-field">
                        <label for="title" class="lt-label">Capstone Title</label>

                        <input
                            type="text"
                            id="title"
                            name="title"
                            class="lt-input"
                            value="{{ old('title') }}"
                            maxlength="255"
                            data-counter="#title-count"
                            required
                        >

                        <div class="lt-counter">
                            <span class="lt-hint">Use the full official title.</span>
                            <span id="title-count" class="lt-counter__num">0 / 255</span>
                        </div>
                    </div>

                    <div class="lt-field">
                        <label for="abstract" class="lt-label">Abstract</label>

                        <textarea
                            id="abstract"
                            name="abstract"
                            class="lt-textarea"
                            rows="10"
                            data-counter="#abstract-count"
                            required
                        >{{ old('abstract') }}</textarea>

                        <div class="lt-counter">
                            <span class="lt-hint">Paste or type the abstract from your study.</span>
                            <span id="abstract-count" class="lt-counter__num">0</span>
                        </div>
                    </div>

                    <div class="lt-field">
                        <label for="publication_date" class="lt-label">Publication Date</label>

                        <input
                            type="date"
                            id="publication_date"
                            name="publication_date"
                            class="lt-input"
                            value="{{ old('publication_date') }}"
                            required
                        >
                    </div>

                </div>
            </section>

            <section class="lt-auto">
                <div class="lt-auto__title">Automatically Retrieved Information</div>
                <p class="lt-auto__desc">These details come from your account and classroom, so you don't need to enter them.</p>

                <div class="lt-auto__grid">

                    <div class="lt-auto__item lt-auto__item--full">
                        <div class="lt-auto__key">Authors</div>
                        <ul class="lt-authors">
                            @foreach($groupRoom->members as $member)
                                <li>
                                    <span class="lt-avatar">{{ strtoupper(substr($member->user->first_name, 0, 1)) }}</span>
                                    {{ $member->user->first_name }}
                                    {{ $member->user->last_name }}
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="lt-auto__item">
                        <div class="lt-auto__key">Program</div>
                        <div class="lt-auto__val">{{ auth()->user()->program }}</div>
                    </div>

                    <div class="lt-auto__item">
                        <div class="lt-auto__key">Year &amp; Section</div>
                        <div class="lt-auto__val">{{ auth()->user()->year_section }}</div>
                    </div>

                    <div class="lt-auto__item">
                        <div class="lt-auto__key">Instructor / Adviser</div>
                        <div class="lt-auto__val">
                            {{ $groupRoom->classroom->instructor->first_name }}
                            {{ $groupRoom->classroom->instructor->last_name }}
                        </div>
                    </div>

                </div>
            </section>

            <div class="lt-form__actions">
                <button type="submit" class="lt-btn lt-btn--primary" data-loading-text="Submitting...">
                    Submit Capstone
                </button>

                <a href="{{ route('student.group-room.show', $groupRoom->id) }}" class="lt-btn lt-btn--ghost">
                    Cancel
                </a>
            </div>

        </form>

    </main>

</body>
</html>