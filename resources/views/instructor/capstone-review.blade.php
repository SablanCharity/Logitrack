<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Review Capstone · LogiTrack</title>
    @vite(['resources/css/logitrack.css', 'resources/js/logitrack.js'])
    
</head>

<body class="lt-body">

    @php
        $badge = match(true) {
            in_array($capstone->status, ['returned_by_instructor', 'returned_by_admin']) => 'lt-badge--returned',
            $capstone->status === 'admin_approved' => 'lt-badge--approved',
            $capstone->status === 'instructor_verified' => 'lt-badge--verified',
            $capstone->status === 'submitted' => 'lt-badge--submitted',
            default => 'lt-badge--draft',
        };
    @endphp

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

        <a href="{{ route('instructor.capstones') }}" class="lt-back">
            ⟵ Back to Capstone Submissions
        </a>

        <div class="lt-page-head">
            <div class="lt-page-head__row">
                <div>
                    <span class="lt-eyebrow">Review</span>
                    <h1>{{ $capstone->title }}</h1>
                    <p class="lt-page-head__sub">Review the submitted study details, then verify the metadata or return it for correction.</p>
                </div>

                <span class="lt-badge {{ $badge }}">
                    {{ ucfirst(str_replace('_', ' ', $capstone->status)) }}
                </span>
            </div>
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

        <div class="lt-review">

            {{-- ===== Left: the capstone details ===== --}}
            <div class="lt-review__stack">

                <section class="lt-card">
                    <h2 class="lt-card__title">Capstone Information</h2>

                    <div class="lt-doc__block">
                        <div class="lt-doc__label">Title</div>
                        <div class="lt-doc__title">{{ $capstone->title }}</div>
                    </div>

                    <div class="lt-doc__block">
                        <div class="lt-doc__label">Abstract</div>
                        <p class="lt-doc__abstract">{{ $capstone->abstract }}</p>
                    </div>

                    <div class="lt-doc__block">
                        <div class="lt-doc__label">Publication Date</div>
                        <div class="lt-auto__val">{{ $capstone->publication_date?->format('F d, Y') }}</div>
                    </div>
                </section>

                <section class="lt-auto">
                    <div class="lt-auto__title">Authors &amp; Details</div>
                    <p class="lt-auto__desc">Retrieved automatically from the group room and classroom.</p>

                    <div class="lt-auto__grid">

                        <div class="lt-auto__item lt-auto__item--full">
                            <div class="lt-auto__key">Authors</div>
                            <ul class="lt-authors">
                                @foreach($capstone->groupRoom->members as $member)
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
                            <div class="lt-auto__val">{{ $capstone->program }}</div>
                        </div>

                        <div class="lt-auto__item">
                            <div class="lt-auto__key">Year &amp; Section</div>
                            <div class="lt-auto__val">{{ $capstone->year_section }}</div>
                        </div>

                        <div class="lt-auto__item">
                            <div class="lt-auto__key">Instructor / Adviser</div>
                            <div class="lt-auto__val">
                                {{ $capstone->groupRoom->classroom->instructor->first_name }}
                                {{ $capstone->groupRoom->classroom->instructor->last_name }}
                            </div>
                        </div>

                        <div class="lt-auto__item">
                            <div class="lt-auto__key">Submitted By</div>
                            <div class="lt-auto__val">
                                {{ $capstone->submitter->first_name }}
                                {{ $capstone->submitter->last_name }}
                            </div>
                        </div>

                    </div>
                </section>

            </div>

            {{-- ===== Right: instructor actions ===== --}}
            <aside class="lt-panel-sticky">

                @if($capstone->status === 'submitted')

                    <section class="lt-card lt-review-forms">

                        <div>
                            <h2 class="lt-card__title">Instructor Review</h2>
                            <p class="lt-card__desc" style="margin-bottom:0;">Check the details on the left, then verify them or send them back.</p>
                        </div>

                        <form method="POST"
                            action="{{ route('instructor.capstone.verify', $capstone->id) }}"
                            data-confirm="This will mark the metadata as verified and send it on for admin approval."
                            data-confirm-title="Verify metadata?"
                            data-confirm-button="Verify"
                            data-submit-guard>
                            @csrf
                            @method('PATCH')

                            <button type="submit" class="lt-btn lt-btn--green" style="width:100%;" data-loading-text="Verifying...">
                                Verify Metadata
                            </button>
                        </form>

                        <hr class="lt-divider">

                        <form method="POST"
                            action="{{ route('instructor.capstone.return', $capstone->id) }}"
                            class="lt-form"
                            data-confirm="The student will see your reason and will need to correct and resubmit."
                            data-confirm-title="Return for correction?"
                            data-confirm-button="Return"
                            data-confirm-danger
                            data-submit-guard>

                            @csrf
                            @method('PATCH')

                            <h3 class="lt-card__title" style="margin:0;">Return for Correction</h3>

                            <div class="lt-field">
                                <label for="return_reason" class="lt-label">
                                    Reason for returning:
                                </label>

                                <textarea
                                    id="return_reason"
                                    name="return_reason"
                                    class="lt-textarea"
                                    style="min-height: 130px;"
                                    rows="5"
                                    required
                                    data-counter="#reason-count"
                                    placeholder="Explain what the student needs to correct..."
                                ></textarea>

                                <div class="lt-counter">
                                    <span class="lt-hint">Be specific so the student knows what to fix.</span>
                                    <span id="reason-count" class="lt-counter__num">0</span>
                                </div>
                            </div>

                            <button type="submit" class="lt-btn lt-btn--danger-ghost" data-loading-text="Returning...">
                                Return for Correction
                            </button>

                        </form>

                    </section>

                @else

                    <section class="lt-card">
                        <h2 class="lt-card__title">Instructor Review</h2>
                        <div class="lt-final lt-mt">
                            This capstone is currently <strong>{{ strtolower(str_replace('_', ' ', $capstone->status)) }}</strong>, so there is nothing to review right now.
                        </div>
                    </section>

                @endif

            </aside>

        </div>

    </main>

</body>
</html>