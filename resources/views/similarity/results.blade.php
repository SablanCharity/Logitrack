
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Similarity Results') }} &middot; LogiTrack</title>
    @vite(['resources/css/logitrack.css', 'resources/js/logitrack.js'])

    <style>
        /* Similarity Summary Tiles */
        .lt-sim-tiles {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 18px;
            margin: 28px 0;
            align-items: stretch;
        }

        .lt-sim-tile {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            min-width: 0;
            min-height: 126px;
            padding: 24px;
            box-sizing: border-box;
        }

        .lt-sim-tile > div {
            min-width: 0;
        }

        .lt-sim-tile__label {
            margin-bottom: 10px;
            line-height: 1.3;
            letter-spacing: 0.08em;
        }

        .lt-sim-tile__value {
            margin: 0;
            line-height: 1.2;
            overflow-wrap: anywhere;
        }

        .lt-sim-tile__value--big {
            margin-top: 2px;
            font-size: 2rem;
            line-height: 1.1;
        }

        .lt-sim-tile__sub {
            margin-top: 6px;
            line-height: 1.4;
        }

        .lt-sim-tile .lt-sim-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 46px;
            width: 46px;
            height: 46px;
            margin: 0;
        }

        .lt-sim-tile .lt-sim-icon svg {
            width: 21px;
            height: 21px;
        }

        /* Similarity Thresholds */
        .lt-sim-thresholds {
            margin-top: 28px;
        }

        .lt-sim-thresholds__heading {
            margin-bottom: 18px;
        }

        .lt-sim-thresholds__heading h3 {
            margin: 0 0 7px;
            font-size: 1rem;
            font-weight: 700;
        }

        .lt-sim-thresholds__heading p {
            margin: 0;
        }

        .lt-sim-thresholds__grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
        }

        .lt-sim-threshold {
            min-width: 0;
            padding: 20px;
            border: 1px solid;
            border-radius: 10px;
            background: #f8f9f5;
            box-sizing: border-box;
        }

        .lt-sim-threshold__title {
            display: flex;
            align-items: center;
            gap: 9px;
            margin-bottom: 12px;
            font-weight: 700;
            color: #111b43;
        }

        .lt-sim-threshold__dot {
            width: 10px;
            height: 10px;
            flex: 0 0 10px;
            border-radius: 50%;
        }

        .lt-sim-threshold__range {
            display: block;
            margin-bottom: 10px;
            font-size: 1.1rem;
            font-weight: 700;
            line-height: 1.3;
        }

        .lt-sim-threshold p {
            margin: 0;
            line-height: 1.6;
            color: #596582;
            font-size: 0.9rem;
        }

        .lt-sim-threshold--low {
            border-color: #c9e5ce;
            background: #f2faf3;
        }

        .lt-sim-threshold--low .lt-sim-threshold__dot {
            background: #17612c;
        }

        .lt-sim-threshold--low .lt-sim-threshold__range {
            color: #287a3e;
        }

        .lt-sim-threshold--moderate {
            border-color: #eadcae;
            background: #fffaf0;
        }

        .lt-sim-threshold--moderate .lt-sim-threshold__dot {
            background: #e5ad18;
        }

        .lt-sim-threshold--moderate .lt-sim-threshold__range {
            color: #b98208;
        }

        .lt-sim-threshold--high {
            border-color: #efc5c5;
            background: #fff4f4;
        }

        .lt-sim-threshold--high .lt-sim-threshold__dot {
            background: #d83232;
        }

        .lt-sim-threshold--high .lt-sim-threshold__range {
            color: #c52d2d;
        }

        .lt-sim-thresholds__note {
            margin-top: 16px;
            font-size: 0.85rem;
            line-height: 1.6;
        }

        /* Mobile and Tablet Layout */
        @media (max-width: 900px) {
            .lt-sim-tiles {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .lt-sim-thresholds__grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 600px) {
            .lt-sim-tiles,
            .lt-sim-thresholds__grid {
                grid-template-columns: 1fr;
            }

            .lt-sim-tile {
                min-height: 112px;
                padding: 20px;
            }

            .lt-sim-threshold {
                padding: 18px;
            }
        }
    </style>
</head>

<body class="lt-body">

    @include('components.site-navigation')

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

        {{-- Most Similar Capstone Project --}}
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
                    <div class="lt-empty__icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 13h6m-6 4h6m2 4H7a2 2 0 01-2-2V5a2 2 0 012-2h6l5 5v11a2 2 0 01-2 2z"/></svg>
                    </div>

                    <div class="lt-empty__title">No repository studies found</div>
                    <p>There are currently no approved studies available for comparison.</p>

                    <a href="{{ route('similarity.index') }}" class="lt-btn lt-btn--primary lt-mt">
                        Return to Similarity Checker
                    </a>
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

                        // Hybrid Similarity Thresholds
                        if ($hybrid < 30) {
                            $level = 'low';
                        } elseif ($hybrid < 60) {
                            $level = 'moderate';
                        } else {
                            $level = 'high';
                        }

                        $levelKey = $level;
                        $rank = $index + 1;
                    @endphp

                    <article class="lt-card lt-sim-result">

                        {{-- Result Header --}}
                        <div class="lt-sim-result__head">
                            <div class="lt-sim-result__id">
                                <span class="lt-sim-rank">#{{ $rank }}</span>

                                <div>
                                    <div class="lt-doc__label">Repository Study</div>
                                    <h3 class="lt-sim-result__title">
                                        {{ $result['title'] ?? 'Untitled Study' }}
                                    </h3>
                                </div>
                            </div>

                            <span class="lt-level lt-level--{{ $levelKey }}">
                                {{ ucfirst($level) }} similarity
                            </span>
                        </div>

                        {{-- Score Overview --}}
                        <div class="lt-sim-scores">
                            <div class="lt-sim-score">
                                <div class="lt-sim-score__label">Hybrid Score</div>

                                <span class="lt-sim-score__num lt-score--{{ $levelKey }}">
                                    {{ number_format($hybrid, 2) }}%
                                </span>

                                <div class="lt-bar">
                                    <div class="lt-bar__fill lt-bar__fill--{{ $levelKey }}" style="--w: {{ min(100, max(0, $hybrid)) }}%"></div>
                                </div>
                            </div>

                            <div class="lt-sim-score">
                                <div class="lt-sim-score__label">Lexical</div>

                                <span class="lt-sim-score__num lt-sim-score__num--sm lt-sim-score__num--lex">
                                    {{ number_format($lexical, 2) }}%
                                </span>

                                <div class="lt-sim-score__sub">TF-IDF similarity</div>
                            </div>

                            <div class="lt-sim-score">
                                <div class="lt-sim-score__label">Semantic</div>

                                <span class="lt-sim-score__num lt-sim-score__num--sm lt-sim-score__num--sem">
                                    {{ number_format($semantic, 2) }}%
                                </span>

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
                                    <span class="lt-sim-icon lt-sim-icon--green">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622C17.176 19.29 21 14.591 21 9c0-1.017-.127-2.004-.364-2.944z"/></svg>
                                    </span>

                                    <div>
                                        <div class="lt-sim-dss__label">Decision Support Guidance</div>

                                        <h4 class="lt-sim-dss__title">
                                            {{ $dss['title'] ?? 'Similarity Review' }}
                                        </h4>

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

        {{-- Project Idea Support / Recommended Capstone Projects --}}
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

                                <h3 class="lt-rec__title">
                                    {{ $recommendation['title'] ?? 'Untitled Recommendation' }}
                                </h3>
                            </div>

                            <div class="lt-rec__body">
                                <div>
                                    <div class="lt-doc__label">Suggested Abstract</div>

                                    <p class="lt-prewrap">
                                        {{ $recommendation['abstract'] ?? 'No abstract available.' }}
                                    </p>
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

        {{-- Repository Summary / Studies Compared --}}
        <div class="lt-sim-tiles">
            <div class="lt-sim-tile">
                <div>
                    <div class="lt-sim-tile__label">Studies Compared</div>

                    <div class="lt-sim-tile__value lt-sim-tile__value--big">
                        {{ $studyCount ?? count($results ?? []) }}
                    </div>
                </div>

                <span class="lt-sim-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h7l2 2h5a2 2 0 012 2v10a2 2 0 01-2 2z"/></svg>
                </span>
            </div>

            <div class="lt-sim-tile">
                <div>
                    <div class="lt-sim-tile__label">Scoring Method</div>
                    <div class="lt-sim-tile__value">Hybrid Similarity</div>
                    <div class="lt-sim-tile__sub">Lexical + semantic analysis</div>
                </div>

                <span class="lt-sim-icon lt-sim-icon--gold">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622C17.176 19.29 21 14.591 21 9c0-1.017-.127-2.004-.364-2.944z"/></svg>
                </span>
            </div>

            <div class="lt-sim-tile">
                <div>
                    <div class="lt-sim-tile__label">Result Limit</div>

                    <div class="lt-sim-tile__value">
                        {{ !empty($results) ? 'Top 1' : 'No results' }}
                    </div>

                    <div class="lt-sim-tile__sub">Ranked by hybrid similarity</div>
                </div>

                <span class="lt-sim-icon lt-sim-icon--green">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 13l4 4L19 7"/></svg>
                </span>
            </div>
        </div>

        {{-- How to Read the Results --}}
        <section class="lt-card lt-sim-read">
            <h2 class="lt-card__title">How to read the results</h2>

            <p>Each score measures a different aspect of similarity.</p>

            {{-- Score Explanations --}}
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

            {{-- Similarity Classification Thresholds --}}
            <div class="lt-sim-thresholds">

                <div class="lt-sim-thresholds__heading">
                    <h3>Similarity Classification Thresholds</h3>
                    <p>Based on the Hybrid Similarity Score:</p>
                </div>

                <div class="lt-sim-thresholds__grid">

                    {{-- Low --}}
                    <div class="lt-sim-threshold lt-sim-threshold--low">
                        <div class="lt-sim-threshold__title">
                            <span class="lt-sim-threshold__dot"></span>
                            Low
                        </div>

                        <span class="lt-sim-threshold__range">
                            0% – 29.99%
                        </span>

                        <p>
                            May proceed to normal academic consideration.
                        </p>
                    </div>

                    {{-- Moderate --}}
                    <div class="lt-sim-threshold lt-sim-threshold--moderate">
                        <div class="lt-sim-threshold__title">
                            <span class="lt-sim-threshold__dot"></span>
                            Moderate
                        </div>

                        <span class="lt-sim-threshold__range">
                            30% – 59.99%
                        </span>

                        <p>
                            Requires closer review and possible revisions.
                        </p>
                    </div>

                    {{-- High --}}
                    <div class="lt-sim-threshold lt-sim-threshold--high">
                        <div class="lt-sim-threshold__title">
                            <span class="lt-sim-threshold__dot"></span>
                            High
                        </div>

                        <span class="lt-sim-threshold__range">
                            60% – 100%
                        </span>

                        <p>
                            Significant overlap; review and differentiation recommended.
                        </p>
                    </div>

                </div>

                <p class="lt-muted lt-sim-thresholds__note">
                    These thresholds are preliminary screening guidelines, not automatic
                    approval or rejection criteria. Final decisions should be made by
                    the adviser or academic panel.
                </p>

            </div>
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
