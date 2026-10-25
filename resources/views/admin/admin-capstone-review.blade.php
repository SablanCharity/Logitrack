<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Review · LogiTrack</title>
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
            <a href="{{ route('admin.capstones') }}" class="lt-brand">
                <span class="lt-brand__mark">L</span>
                LogiTrack
            </a>

            <button type="button" class="lt-nav__toggle" data-nav-toggle="#nav-menu" aria-label="Toggle menu" aria-expanded="false">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
            </button>

            <div class="lt-nav__menu" id="nav-menu">
                <nav class="lt-nav__links lt-nav__links--app lt-admin-nav" aria-label="Admin">
                    <a href="{{ route('dashboard') }}" class="lt-nav__link lt-admin-nav-link" data-tooltip="Dashboard" aria-label="Dashboard"><span class="material-symbols-outlined">dashboard</span><span class="lt-admin-nav-label">Dashboard</span></a>
                    <a href="{{ route('repository.index') }}" class="lt-nav__link lt-admin-nav-link" data-tooltip="Repository" aria-label="Repository"><span class="material-symbols-outlined">menu_book</span><span class="lt-admin-nav-label">Repository</span></a>
                    <a href="{{ route('admin.capstones') }}" class="lt-nav__link lt-admin-nav-link {{ request()->routeIs('admin.capstones*') ? 'is-active' : '' }}" data-tooltip="Capstone Review" aria-label="Capstone Review"><span class="material-symbols-outlined">fact_check</span><span class="lt-admin-nav-label">Capstone Review</span></a>
                    <a href="{{ route('ocr.upload') }}" class="lt-nav__link lt-admin-nav-link" data-tooltip="Upload Archived" aria-label="Upload Archived"><span class="material-symbols-outlined">upload_file</span><span class="lt-admin-nav-label">Upload Archived</span></a>
                    <a href="{{ route('admin.accounts.index') }}" class="lt-nav__link lt-admin-nav-link" data-tooltip="Staff Accounts" aria-label="Staff Accounts"><span class="material-symbols-outlined">manage_accounts</span><span class="lt-admin-nav-label">Staff Accounts</span></a>
                    <a href="{{ route('admin.students.index') }}" class="lt-nav__link lt-admin-nav-link" data-tooltip="Student Accounts" aria-label="Student Accounts"><span class="material-symbols-outlined">school</span><span class="lt-admin-nav-label">Student Accounts</span></a>
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

        <a href="{{ route('admin.capstones') }}" class="lt-back">
            ⟵ Back to Capstone Approval
        </a>

        <div class="lt-page-head">
            <div class="lt-page-head__row">
                <div>
                    <span class="lt-eyebrow">Final Repository Approval</span>
                    <h1>Admin Review</h1>
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
                                {{ optional(optional($capstone->groupRoom->classroom)->instructor)->first_name }}
                                {{ optional(optional($capstone->groupRoom->classroom)->instructor)->last_name }}
                            </div>
                        </div>

                    </div>
                </section>

                <section class="lt-card">
                    <h2 class="lt-card__title">Review Information</h2>
                    <p class="lt-card__desc">Who handled this capstone before it reached you.</p>

                    <dl class="lt-details">
                        <div class="lt-details__row">
                            <dt>Submitted By</dt>
                            <dd>
                                {{ optional($capstone->submitter)->first_name }}
                                {{ optional($capstone->submitter)->last_name }}
                            </dd>
                        </div>

                        <div class="lt-details__row">
                            <dt>Verified By Instructor</dt>
                            <dd>
                                {{ optional($capstone->verifier)->first_name }}
                                {{ optional($capstone->verifier)->last_name }}
                            </dd>
                        </div>

                        <div class="lt-details__row">
                            <dt>Verified At</dt>
                            <dd>{{ $capstone->verified_at?->format('F d, Y h:i A') }}</dd>
                        </div>

                        <div class="lt-details__row">
                            <dt>Status</dt>
                            <dd>
                                <span class="lt-badge {{ $badge }}">
                                    {{ ucfirst(str_replace('_', ' ', $capstone->status)) }}
                                </span>
                            </dd>
                        </div>
                    </dl>
                </section>

            </div>

            <aside class="lt-panel-sticky">

                <section class="lt-card lt-review-forms">

                    <div>
                        <h2 class="lt-card__title">Admin Action</h2>
                        <p class="lt-card__desc" style="margin-bottom:0;">
                            Review the metadata carefully before approving this capstone
                            for inclusion in the repository.
                        </p>
                    </div>

                    <div>
                        <h3 class="lt-card__title" style="margin-bottom:10px;">Approve</h3>

                        <form method="POST"
                            action="{{ route('admin.capstone.approve', $capstone->id) }}"
                            data-confirm="This capstone will be published to the repository and become searchable by students."
                            data-confirm-title="Approve for repository?"
                            data-confirm-button="Approve"
                            data-submit-guard>

                            @csrf
                            @method('PATCH')

                            <button type="submit" class="lt-btn lt-btn--green" style="width:100%;" data-loading-text="Approving...">
                                Approve for Repository
                            </button>

                        </form>
                    </div>

                    <hr class="lt-divider">

                    <form method="POST"
                        action="{{ route('admin.capstone.return', $capstone->id) }}"
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
                                maxlength="1000"
                                data-counter="#reason-count"
                                placeholder="Explain what the student needs to correct..."
                            ></textarea>

                            <div class="lt-counter">
                                <span class="lt-hint">Be specific so the student knows what to fix.</span>
                                <span id="reason-count" class="lt-counter__num">0 / 1000</span>
                            </div>
                        </div>

                        <button type="submit" class="lt-btn lt-btn--danger-ghost" data-loading-text="Returning...">
                            Return for Correction
                        </button>

                    </form>

                </section>

            </aside>

        </div>

    </main>

</body>
</html>