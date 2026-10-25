<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Similarity Results') }} · LogiTrack</title>
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
                    @if(auth()->user()->role !== 'admin')
                        <a href="{{ route('dashboard') }}" class="lt-nav__link {{ request()->routeIs('dashboard') ? 'is-active' : '' }}">Dashboard</a>
                    @endif

                    @if(auth()->user()->role === 'instructor')
                    <a href="{{ route('instructor.classrooms') }}" class="lt-nav__link {{ request()->routeIs('instructor.classrooms*', 'instructor.classroom.*') ? 'is-active' : '' }}">Classroom</a>
                    <a href="{{ route('instructor.capstones') }}" class="lt-nav__link {{ request()->routeIs('instructor.capstones*', 'instructor.capstone.*') ? 'is-active' : '' }}">Capstone Submissions</a>
                    <a href="{{ route('instructor.students') }}" class="lt-nav__link {{ request()->routeIs('instructor.students*') ? 'is-active' : '' }}">Pending Students</a>
                    @elseif(auth()->user()->role === 'student')
                    <a href="{{ $classroomId ? route('student.group-rooms.index', $classroomId) : route('student.pending') }}" class="lt-nav__link {{ request()->routeIs('student.group-rooms.*', 'student.group-room.*', 'student.capstone.*', 'student.pending') ? 'is-active' : '' }}">Classroom</a>
                    <a href="{{ route('similarity.index') }}" class="lt-nav__link {{ request()->routeIs('similarity.*') ? 'is-active' : '' }}">Similarity Checker</a>
                    <a href="{{ route('repository.index') }}" class="lt-nav__link {{ request()->routeIs('repository.*') ? 'is-active' : '' }}">Repository</a>
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

        {{-- Header --}}
        <div class="lt-page-head">
            <div class="lt-page-head__row">
                <div>
                    <span class="lt-eyebrow">Similarity Analysis</span>
                    <h1>Similarity Checker Results</h1>
                    <p class="lt-page-head__sub">
                        The proposed study has been compared against approved repository
                        studies using lexical similarity, semantic similarity, and the
                        configured hybrid scoring method.
                    </p>
                </div>

                <a href="{{ route('similarity.index') }}" class="lt-btn lt-btn--ghost">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 19l-7-7 7-7"/></svg>
                    New Analysis
                </a>
            </div>
        </div>

        {{-- Proposed Study --}}
        <section class="lt-card lt-sim-study">
            <h2 class="lt-card__title">Proposed Study</h2>
            <p class="lt-card__desc">Study submitted for similarity analysis</p>

            <div class="lt-doc__block">
                <div class="lt-doc__label">Title</div>
                <div class="lt-doc__title">{{ $proposedTitle ?? $title ?? 'Untitled Study' }}</div>
            </div>

            <div class="lt-doc__block">
                <div class="lt-doc__label">Abstract</div>
                <p class="lt-doc__abstract lt-prewrap">{{ $proposedAbstract ?? $abstract ?? 'No abstract provided.' }}</p>
            </div>
        </section>

        {{-- Repository Summary --}}
        <div class="lt-sim-tiles">
            <div class="lt-sim-tile">
                <div>
                    <div class="lt-sim-tile__label">Studies Compared</div>
                    <div class="lt-sim-tile__value lt-sim-tile__value--big">{{ $studyCount ?? count($results ?? []) }}</div>
                </div>
                <span class="lt-sim-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h7l2 2h5a2 2 0 012 2v10a2 2 0 01-2 2z"/></svg></span>
            </div>

            <div class="lt-sim-tile">
                <div>
                    <div class="lt-sim-tile__label">Scoring Method</div>
                    <div class="lt-sim-tile__value">Hybrid Similarity</div>
                    <div class="lt-sim-tile__sub">Lexical + semantic analysis</div>
                </div>
                <span class="lt-sim-icon lt-sim-icon--gold"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622C17.176 19.29 21 14.591 21 9c0-1.017-.127-2.004-.364-2.944z"/></svg></span>
            </div>

            <div class="lt-sim-tile">
                <div>
                    <div class="lt-sim-tile__label">Result Limit</div>
                    <div class="lt-sim-tile__value">{{ !empty($results) ? 'Top 1' : 'No results' }}</div>
                    <div class="lt-sim-tile__sub">Ranked by hybrid similarity</div>
                </div>
                <span class="lt-sim-icon lt-sim-icon--green"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 13l4 4L19 7"/></svg></span>
            </div>
        </div>

        {{-- Method Explanation --}}
        <section class="lt-card lt-sim-read">
            <h2 class="lt-card__title">How to read the results</h2>
            <p>Each score measures a different aspect of similarity.</p>

            <div class="lt-sim-read__grid">
                <div class="lt-sim-read__item">
                    <h3><span class="lt-dot lt-dot--lex"></span>Lexical</h3>
                    <p>Measures overlap in important terms using TF-IDF and cosine similarity.</p>
                </div>

                <div class="lt-sim-read__item">
                    <h3><span class="lt-dot lt-dot--sem"></span>Semantic</h3>
                    <p>Measures similarity in meaning using text embeddings.</p>
                </div>

                <div class="lt-sim-read__item">
                    <h3><span class="lt-dot lt-dot--hyb"></span>Hybrid</h3>
                    <p>Combines lexical and semantic similarity into the ranking score.</p>
                </div>
            </div>
        </section>

        {{-- Most Similar Repository Result --}}
        <section>
            <div class="lt-sim-section-head">
                <div>
                    <h2>Most Similar Capstone Project</h2>
                    <p>The highest-ranked study from the approved repository is shown below.</p>
                </div>
                <div class="lt-sim-count">
                    {{ !empty($results) ? '1 result' : '0 results' }}
                </div>
            </div>

            @if(empty($results) || count($results) === 0)
                <div class="lt-empty">
                    <div class="lt-empty__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 13h6m-6 4h6m2 4H7a2 2 0 01-2-2V5a2 2 0 012-2h6l5 5v11a2 2 0 01-2 2z"/></svg></div>
                    <div class="lt-empty__title">No repository studies found</div>
                    <p>There are currently no approved studies available for comparison.</p>
                    <a href="{{ route('similarity.index') }}" class="lt-btn lt-btn--primary lt-mt">Return to Similarity Checker</a>
                </div>
            @else
                @foreach(array_slice($results ?? [], 0, 1) as $index => $result)
                    @php
                        $similarity = $result['similarity'] ?? [];
                        $hybrid = (float) ($similarity['hybrid_percentage'] ?? 0);
                        $lexical = (float) ($similarity['lexical_percentage'] ?? 0);
                        $semantic = (float) ($similarity['semantic_percentage'] ?? 0);
                        $sharedTerms = $similarity['shared_terms'] ?? [];
                        $dss = $similarity['dss'] ?? [];
                        $level = strtolower((string) ($dss['level'] ?? 'low'));

                        $levelKey = $level === 'high' ? 'high' : ($level === 'moderate' ? 'moderate' : 'low');
                        $rank = $index + 1;
                    @endphp

                    <article class="lt-card lt-sim-result">
                        {{-- Result Header --}}
                        <div class="lt-sim-result__head">
                            <div class="lt-sim-result__id">
                                <span class="lt-sim-rank">#{{ $rank }}</span>
                                <div>
                                    <div class="lt-doc__label">Repository Study</div>
                                    <h3 class="lt-sim-result__title">{{ $result['title'] ?? 'Untitled Study' }}</h3>
                                </div>
                            </div>

                            <span class="lt-level lt-level--{{ $levelKey }}">{{ ucfirst($level) }} similarity</span>
                        </div>

                        {{-- Score Overview --}}
                        <div class="lt-sim-scores">
                            <div class="lt-sim-score">
                                <div class="lt-sim-score__label">Hybrid Score</div>
                                <span class="lt-sim-score__num lt-score--{{ $levelKey }}">{{ number_format($hybrid, 2) }}%</span>
                                <div class="lt-bar">
                                    <div class="lt-bar__fill lt-bar__fill--{{ $levelKey }}" style="--w: {{ min(100, max(0, $hybrid)) }}%"></div>
                                </div>
                            </div>

                            <div class="lt-sim-score">
                                <div class="lt-sim-score__label">Lexical</div>
                                <span class="lt-sim-score__num lt-sim-score__num--sm lt-sim-score__num--lex">{{ number_format($lexical, 2) }}%</span>
                                <div class="lt-sim-score__sub">TF-IDF similarity</div>
                            </div>

                            <div class="lt-sim-score">
                                <div class="lt-sim-score__label">Semantic</div>
                                <span class="lt-sim-score__num lt-sim-score__num--sm lt-sim-score__num--sem">{{ number_format($semantic, 2) }}%</span>
                                <div class="lt-sim-score__sub">Embedding similarity</div>
                            </div>
                        </div>

                        {{-- Study Abstract and Shared Terms --}}
                        <div class="lt-sim-body">
                            <div>
                                <h4>Study Abstract</h4>
                                <p>{{ $result['abstract'] ?? 'No abstract available.' }}</p>
                            </div>

                            <div>
                                <h4>Shared Terms</h4>
                                @if(!empty($sharedTerms))
                                    <div class="lt-tags">
                                        @foreach($sharedTerms as $term)
                                            <span class="lt-tag">{{ $term }}</span>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="lt-muted">No significant shared terms identified.</p>
                                @endif
                            </div>
                        </div>

                        {{-- Decision Support Guidance --}}
                        <div class="lt-sim-dss">
                            <div class="lt-sim-dss__row">
                                <div class="lt-sim-dss__main">
                                    <span class="lt-sim-icon lt-sim-icon--green"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622C17.176 19.29 21 14.591 21 9c0-1.017-.127-2.004-.364-2.944z"/></svg></span>
                                    <div>
                                        <div class="lt-sim-dss__label">Decision Support Guidance</div>
                                        <h4 class="lt-sim-dss__title">{{ $dss['title'] ?? 'Similarity Review' }}</h4>
                                        @if(!empty($dss['message']))
                                            <p class="lt-sim-dss__msg">{{ $dss['message'] }}</p>
                                        @endif
                                    </div>
                                </div>

                                @if(!empty($dss['areas_to_review']))
                                    <div class="lt-sim-dss__areas">
                                        <div class="lt-doc__label">Areas to Review</div>
                                        <div class="lt-tags">
                                            @foreach($dss['areas_to_review'] as $area)
                                                <span class="lt-tag lt-tag--box">{{ $area }}</span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>

                            @if(!empty($dss['recommendation']))
                                <div class="lt-sim-reco">
                                    <div class="lt-doc__label">Recommendation</div>
                                    <p>{{ $dss['recommendation'] }}</p>
                                </div>
                            @endif

                            <div class="lt-note lt-note--warn lt-note--flat">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M13 16h-1v-4h-1m1-4h.01M12 22a10 10 0 100-20 10 10 0 000 20z"/></svg>
                                <p>
                                    A similarity score is a review signal, not an automatic rejection.
                                    The final academic decision should consider the study's objectives,
                                    scope, target users, problem statement, and features.
                                </p>
                            </div>
                        </div>

                        {{-- Result Footer --}}
                        <div class="lt-sim-result__foot">
                            <p>Result ranked by hybrid similarity score.</p>

                            @if(isset($result['id']))
                                @php
                                    $matchType = ($result['type'] ?? 'modern') === 'old' ? 'old' : 'modern';
                                @endphp
                                <a href="{{ route('repository.show', ['capstoneId' => $result['id'], 'type' => $matchType]) }}" class="lt-btn lt-btn--ghost lt-btn--sm">
                                    View Study
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 5l7 7-7 7"/></svg>
                                </a>
                            @endif
                        </div>
                    </article>
                @endforeach
            @endif
        </section>

        {{-- Recommended Capstone Projects --}}
        <section class="lt-sim-ideas">
            <div class="lt-sim-section-head">
                <div>
                    <span class="lt-eyebrow">Project Idea Support</span>
                    <h2>Recommended Capstone Projects</h2>
                    <p>
                        Explore three alternative project directions based on your submitted
                        study. Review their scope and features against existing repository
                        studies before deciding whether to pursue an idea.
                    </p>
                </div>
            </div>

            @if(!empty($recommendations))
                <div class="lt-sim-ideas__grid">
                    @foreach($recommendations as $index => $recommendation)
                        <article class="lt-card lt-rec">
                            <div class="lt-rec__head">
                                <div class="lt-rec__top">
                                    <span class="lt-rec__num">{{ $index + 1 }}</span>
                                    <span class="lt-rec__kicker">Alternative Project Idea</span>
                                </div>

                                <h3 class="lt-rec__title">{{ $recommendation['title'] ?? 'Untitled Recommendation' }}</h3>
                            </div>

                            <div class="lt-rec__body">
                                <div>
                                    <div class="lt-doc__label">Suggested Abstract</div>
                                    <p class="lt-prewrap">{{ $recommendation['abstract'] ?? 'No abstract available.' }}</p>
                                </div>

                                @if(!empty($recommendation['reason']))
                                    <div class="lt-rec__why">
                                        <div class="lt-doc__label">Why consider this idea?</div>
                                        <p>{{ $recommendation['reason'] }}</p>
                                    </div>
                                @endif

                                @if(!empty($recommendation['design_note']))
                                    <div>
                                        <div class="lt-doc__label">Design Note</div>
                                        <p>{{ $recommendation['design_note'] }}</p>
                                    </div>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="lt-card">
                    <p class="lt-muted">No project recommendations are available for this analysis.</p>
                </div>
            @endif

            <p class="lt-sim-fine">
                These are rule-based suggestions, not verified claims of originality.
                Confirm novelty, feasibility, scope, target users, and repository
                similarity with your adviser before finalizing a capstone proposal.
            </p>
        </section>

        {{-- Academic Review Reminder --}}
        <div class="lt-note lt-sim-remind">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M13 16h-1v-4h-1m1-4h.01M12 22a10 10 0 100-20 10 10 0 000 20z"/></svg>
            <div>
                <h3>Academic Review Reminder</h3>
                <p>
                    Similarity analysis is intended to support academic review.
                    A high similarity score does not by itself establish that
                    two studies are duplicates or that a proposed study should
                    be rejected.
                </p>
            </div>
        </div>

    </main>

</body>
</html>