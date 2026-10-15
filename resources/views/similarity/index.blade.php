<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Similarity Checker') }} · LogiTrack</title>
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

    <main class="lt-container lt-animate lt-sim">

        {{-- Page Header --}}
        <div class="lt-page-head">
            <div class="lt-page-head__row">
                <div>
                    <span class="lt-eyebrow">Research Similarity Analysis</span>
                    <h1>Similarity Checker</h1>
                    <p class="lt-page-head__sub">
                        Compare a proposed capstone study with approved studies in the repository.
                        The system analyzes both textual overlap and semantic similarity to identify
                        related research.
                    </p>
                </div>

                <a href="{{ route('repository.index', ['return_to' => 'similarity']) }}" class="lt-btn lt-btn--ghost">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
                    View Repository
                </a>
            </div>
        </div>

        {{-- How It Works --}}
        @php
            $steps = [
                [
                    'number' => '01',
                    'title' => 'Preprocess',
                    'description' => 'Normalize and clean the research text.',
                ],
                [
                    'number' => '02',
                    'title' => 'TF-IDF',
                    'description' => 'Measure important shared terms.',
                ],
                [
                    'number' => '03',
                    'title' => 'Embeddings',
                    'description' => 'Compare the meaning of the studies.',
                ],
                [
                    'number' => '04',
                    'title' => 'Hybrid Score',
                    'description' => 'Combine lexical and semantic evidence.',
                ],
                [
                    'number' => '05',
                    'title' => 'DSS Guidance',
                    'description' => 'Highlight areas that may need review.',
                ],
            ];
        @endphp

        <section class="lt-card lt-sim-how">
            <div class="lt-sim-how__head">
                <h2 class="lt-card__title">How the analysis works</h2>
                <p>Your proposed study passes through several analysis stages before results are ranked.</p>
            </div>

            <ol class="lt-sim-steps">
                @foreach ($steps as $step)
                    <li class="lt-sim-step">
                        <div class="lt-sim-step__top">
                            <span class="lt-sim-step__num">{{ $step['number'] }}</span>
                            <h3>{{ $step['title'] }}</h3>
                        </div>
                        <p>{{ $step['description'] }}</p>
                    </li>
                @endforeach
            </ol>
        </section>

        <div class="lt-sim-layout">

            {{-- Form --}}
            <section class="lt-card">

                <div class="lt-sim-formhead">
                    <span class="lt-sim-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.5-8.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 7.5-7.5z"/></svg></span>
                    <div>
                        <h2 class="lt-card__title">Proposed Study</h2>
                        <p>Enter the title and abstract you want to evaluate.</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('similarity.check') }}" class="lt-form" data-submit-guard>
                    @csrf

                    {{-- Validation Summary --}}
                    @if ($errors->any())
                        <div class="lt-alert lt-alert--error" role="alert">
                            <span class="lt-alert__icon">!</span>
                            <div>
                                <strong>Please check the following:</strong>
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif

                    {{-- Title --}}
                    <div class="lt-field">
                        <label for="title" class="lt-label">Proposed Study Title</label>

                        <input
                            id="title"
                            name="title"
                            type="text"
                            value="{{ old('title') }}"
                            maxlength="255"
                            required
                            placeholder="e.g. Student Attendance Monitoring System"
                            class="lt-input"
                            data-counter="#title-count"
                        >

                        <div class="lt-counter">
                            <span></span>
                            <span id="title-count" class="lt-counter__num">0 / 255</span>
                        </div>

                        @error('title')
                            <p class="lt-field-error">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Abstract --}}
                    <div class="lt-field">
                        <div class="lt-label-row">
                            <label for="abstract" class="lt-label">Proposed Study Abstract</label>
                            <span class="lt-hint">Minimum {{ config('similarity.minimum_text_length', 20) }} characters</span>
                        </div>

                        <textarea
                            id="abstract"
                            name="abstract"
                            rows="12"
                            required
                            placeholder="Paste or write the abstract of the proposed study here..."
                            class="lt-textarea"
                            style="min-height: 260px;"
                            data-counter="#abstract-count"
                        >{{ old('abstract') }}</textarea>

                        <div class="lt-counter">
                            <span class="lt-hint">Characters</span>
                            <span id="abstract-count" class="lt-counter__num">0</span>
                        </div>

                        @error('abstract')
                            <p class="lt-field-error">{{ $message }}</p>
                        @enderror

                        <div class="lt-sim-tip">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M13 16h-1v-4h-1m1-4h.01M12 20a8 8 0 100-16 8 8 0 000 16z"/></svg>
                            <span>
                                For better results, provide a complete abstract describing the
                                problem, objectives, users, scope, and major features of the proposed system.
                            </span>
                        </div>
                    </div>

                    {{-- Submit --}}
                    <div class="lt-sim-submit">
                        <p>The checker compares your proposal against studies available in the repository.</p>

                        <button type="submit" class="lt-btn lt-btn--primary lt-btn--lg" data-loading-text="Analyzing...">
                            Analyze Similarity
                        </button>
                    </div>
                </form>
            </section>

            {{-- Information Sidebar --}}
            <aside class="lt-sim-side">

                {{-- What is checked --}}
                <section class="lt-card">
                    <div class="lt-sim-sidehead">
                        <span class="lt-sim-icon lt-sim-icon--green"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 12c0 5.591 3.824 10.29 9 11.622C17.176 22.29 21 17.591 21 12c0-1.041-.133-2.052-.382-3.016z"/></svg></span>
                        <h2>What is analyzed?</h2>
                    </div>

                    <ul class="lt-sim-points">
                        <li>
                            <div>
                                <strong>Lexical similarity</strong>
                                <span>Identifies important terms shared between the proposed and existing studies.</span>
                            </div>
                        </li>
                        <li>
                            <div>
                                <strong>Semantic similarity</strong>
                                <span>Looks for similarities in meaning, even when different words are used.</span>
                            </div>
                        </li>
                        <li>
                            <div>
                                <strong>Hybrid similarity</strong>
                                <span>Produces a combined score from the available lexical and semantic evidence.</span>
                            </div>
                        </li>
                        <li>
                            <div>
                                <strong>Decision support</strong>
                                <span>Highlights research areas that should be reviewed rather than automatically rejecting a proposal.</span>
                            </div>
                        </li>
                    </ul>
                </section>

                {{-- Review areas --}}
                <div class="lt-note">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <div>
                        <h2>Recommended review areas</h2>
                        <p>If related studies are found, pay particular attention to differences in:</p>
                        <ul>
                            <li>Research objectives</li>
                            <li>Target users</li>
                            <li>System scope</li>
                            <li>Major features</li>
                            <li>Problem statement</li>
                        </ul>
                    </div>
                </div>

                {{-- Important note --}}
                <div class="lt-note lt-note--warn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 9v2m0 4h.01M10.29 3.86l-7.82 13a2 2 0 001.71 3.14h15.64a2 2 0 001.71-3.14l-7.82-13a2 2 0 00-3.42 0z"/></svg>
                    <div>
                        <h2>Important</h2>
                        <p>
                            A high similarity score does not automatically mean that a study should
                            be rejected. Results are intended to support academic review and help
                            identify areas that require closer comparison.
                        </p>
                    </div>
                </div>

            </aside>
        </div>

    </main>

</body>
</html>