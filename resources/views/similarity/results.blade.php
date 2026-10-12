<x-app-layout>
<div class="min-h-screen bg-slate-50">
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">

        {{-- Header --}}
        <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <div class="mb-2 flex items-center gap-2 text-sm font-medium text-indigo-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 5H5a2 2 0 00-2 2v10a2 2 0 002 2h4m6-14h4a2 2 0 012 2v10a2 2 0 01-2 2h-4m-6-4h6m-6-4h6m-6-4h3"/>
                    </svg>
                    Similarity Analysis
                </div>

                <h1 class="text-3xl font-bold tracking-tight text-slate-900">
                    Similarity Checker Results
                </h1>

                <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600">
                    The proposed study has been compared against approved repository
                    studies using lexical similarity, semantic similarity, and the
                    configured hybrid scoring method.
                </p>
            </div>

            <a href="{{ route('similarity.index') }}"
               class="inline-flex items-center justify-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M15 19l-7-7 7-7"/>
                </svg>
                New Analysis
            </a>
        </div>

        {{-- Proposed Study --}}
        <section class="mb-8 overflow-hidden rounded-2xl border border-indigo-100 bg-white shadow-sm">
            <div class="border-b border-indigo-100 bg-indigo-50/60 px-6 py-5">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-600 text-white">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 20h9M16.5 3.5a2.121 2.121 0 013 3L7 19l-4 1 1-4L16.5 3.5z"/>
                        </svg>
                    </div>

                    <div>
                        <h2 class="text-lg font-bold text-slate-900">Proposed Study</h2>
                        <p class="text-sm text-slate-500">Study submitted for similarity analysis</p>
                    </div>
                </div>
            </div>

            <div class="space-y-5 px-6 py-6">
                <div>
                    <p class="mb-1 text-xs font-semibold uppercase tracking-wider text-slate-500">
                        Title
                    </p>
                    <h3 class="text-xl font-bold text-slate-900">
                        {{ $proposedTitle ?? $title ?? 'Untitled Study' }}
                    </h3>
                </div>

                <div>
                    <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-slate-500">
                        Abstract
                    </p>
                    <p class="whitespace-pre-line text-sm leading-7 text-slate-700">{{ $proposedAbstract ?? $abstract ?? 'No abstract provided.' }}</p>
                </div>
            </div>
        </section>

        {{-- Repository Summary --}}
        <div class="mb-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">
                            Studies Compared
                        </p>
                        <p class="mt-2 text-3xl font-bold text-slate-900">
                            {{ $studyCount ?? count($results ?? []) }}
                        </p>
                    </div>
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-slate-100 text-slate-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h7l2 2h5a2 2 0 012 2v10a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">
                            Scoring Method
                        </p>
                        <p class="mt-2 text-lg font-bold text-slate-900">Hybrid Similarity</p>
                        <p class="mt-1 text-xs text-slate-500">Lexical + semantic analysis</p>
                    </div>
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622C17.176 19.29 21 14.591 21 9c0-1.017-.127-2.004-.364-2.944z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:col-span-2 lg:col-span-1">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">
                            Result Limit
                        </p>
                        <p class="mt-2 text-lg font-bold text-slate-900">
                            {{ !empty($results) ? 'Top 1' : 'No results' }}
                        </p>
                        <p class="mt-1 text-xs text-slate-500">Ranked by hybrid similarity</p>
                    </div>
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- Method Explanation --}}
        <section class="mb-8 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="mb-5">
                <h2 class="text-lg font-bold text-slate-900">How to read the results</h2>
                <p class="mt-1 text-sm text-slate-500">
                    Each score measures a different aspect of similarity.
                </p>
            </div>

            <div class="grid gap-4 md:grid-cols-3">
                <div class="rounded-xl bg-slate-50 p-4">
                    <div class="mb-2 flex items-center gap-2">
                        <span class="h-2.5 w-2.5 rounded-full bg-blue-500"></span>
                        <h3 class="font-semibold text-slate-900">Lexical</h3>
                    </div>
                    <p class="text-sm leading-6 text-slate-600">
                        Measures overlap in important terms using TF-IDF and cosine similarity.
                    </p>
                </div>

                <div class="rounded-xl bg-slate-50 p-4">
                    <div class="mb-2 flex items-center gap-2">
                        <span class="h-2.5 w-2.5 rounded-full bg-violet-500"></span>
                        <h3 class="font-semibold text-slate-900">Semantic</h3>
                    </div>
                    <p class="text-sm leading-6 text-slate-600">
                        Measures similarity in meaning using text embeddings.
                    </p>
                </div>

                <div class="rounded-xl bg-slate-50 p-4">
                    <div class="mb-2 flex items-center gap-2">
                        <span class="h-2.5 w-2.5 rounded-full bg-indigo-500"></span>
                        <h3 class="font-semibold text-slate-900">Hybrid</h3>
                    </div>
                    <p class="text-sm leading-6 text-slate-600">
                        Combines lexical and semantic similarity into the ranking score.
                    </p>
                </div>
            </div>
        </section>

        {{-- Most Similar Repository Result --}}
        <section>
            <div class="mb-5 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-slate-900">Most Similar Capstone Project</h2>
                    <p class="mt-1 text-sm text-slate-500">
                        The highest-ranked study from the approved repository is shown below.
                    </p>
                </div>
                <div class="text-sm text-slate-500">
                    {{ !empty($results) ? '1 result' : '0 results' }}
                </div>
            </div>

            @if(empty($results) || count($results) === 0)
                <div class="rounded-2xl border border-slate-200 bg-white px-6 py-12 text-center shadow-sm">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-slate-100 text-slate-500">
                        <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 13h6m-6 4h6m2 4H7a2 2 0 01-2-2V5a2 2 0 012-2h6l5 5v11a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <h3 class="mt-4 text-lg font-semibold text-slate-900">No repository studies found</h3>
                    <p class="mx-auto mt-2 max-w-lg text-sm leading-6 text-slate-500">
                        There are currently no approved studies available for comparison.
                    </p>
                    <a href="{{ route('similarity.index') }}"
                       class="mt-5 inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700">
                        Return to Similarity Checker
                    </a>
                </div>
            @else
                <div class="space-y-6">
                    @foreach(array_slice($results ?? [], 0, 1) as $index => $result)
                        @php
                            $similarity = $result['similarity'] ?? [];
                            $hybrid = (float) ($similarity['hybrid_percentage'] ?? 0);
                            $lexical = (float) ($similarity['lexical_percentage'] ?? 0);
                            $semantic = (float) ($similarity['semantic_percentage'] ?? 0);
                            $sharedTerms = $similarity['shared_terms'] ?? [];
                            $dss = $similarity['dss'] ?? [];
                            $level = strtolower((string) ($dss['level'] ?? 'low'));

                            if ($level === 'high') {
                                $levelClasses = 'bg-red-50 text-red-700 ring-red-200';
                                $scoreClasses = 'text-red-600';
                                $progressClasses = 'bg-red-500';
                            } elseif ($level === 'moderate') {
                                $levelClasses = 'bg-amber-50 text-amber-700 ring-amber-200';
                                $scoreClasses = 'text-amber-600';
                                $progressClasses = 'bg-amber-500';
                            } else {
                                $levelClasses = 'bg-emerald-50 text-emerald-700 ring-emerald-200';
                                $scoreClasses = 'text-emerald-600';
                                $progressClasses = 'bg-emerald-500';
                            }

                            $rank = $index + 1;
                        @endphp

                        <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                            {{-- Result Header --}}
                            <div class="border-b border-slate-200 px-6 py-5">
                                <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                                    <div class="flex gap-4">
                                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-slate-900 text-sm font-bold text-white">
                                            #{{ $rank }}
                                        </div>
                                        <div>
                                            <p class="mb-1 text-xs font-semibold uppercase tracking-wider text-slate-500">
                                                Repository Study
                                            </p>
                                            <h3 class="text-xl font-bold leading-7 text-slate-900">
                                                {{ $result['title'] ?? 'Untitled Study' }}
                                            </h3>
                                        </div>
                                    </div>
                                    <div class="flex shrink-0 items-center gap-3">
                                        <span class="rounded-full px-3 py-1.5 text-xs font-semibold ring-1 {{ $levelClasses }}">
                                            {{ ucfirst($level) }} similarity
                                        </span>
                                    </div>
                                </div>
                            </div>

                            {{-- Score Overview --}}
                            <div class="grid border-b border-slate-200 sm:grid-cols-3">
                                <div class="border-b border-slate-200 px-6 py-5 sm:border-b-0 sm:border-r">
                                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">
                                        Hybrid Score
                                    </p>
                                    <span class="mt-2 block text-3xl font-bold {{ $scoreClasses }}">
                                        {{ number_format($hybrid, 2) }}%
                                    </span>
                                    <div class="mt-3 h-2 overflow-hidden rounded-full bg-slate-100">
                                        <div class="h-full rounded-full {{ $progressClasses }}"
                                             style="width: {{ min(100, max(0, $hybrid)) }}%"></div>
                                    </div>
                                </div>

                                <div class="border-b border-slate-200 px-6 py-5 sm:border-b-0 sm:border-r">
                                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">
                                        Lexical
                                    </p>
                                    <p class="mt-2 text-2xl font-bold text-blue-600">
                                        {{ number_format($lexical, 2) }}%
                                    </p>
                                    <p class="mt-1 text-xs text-slate-500">TF-IDF similarity</p>
                                </div>

                                <div class="px-6 py-5">
                                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">
                                        Semantic
                                    </p>
                                    <p class="mt-2 text-2xl font-bold text-violet-600">
                                        {{ number_format($semantic, 2) }}%
                                    </p>
                                    <p class="mt-1 text-xs text-slate-500">Embedding similarity</p>
                                </div>
                            </div>

                            {{-- Study Abstract and Shared Terms --}}
                            <div class="grid gap-6 px-6 py-6 lg:grid-cols-3">
                                <div class="lg:col-span-2">
                                    <h4 class="mb-2 text-sm font-bold text-slate-900">Study Abstract</h4>
                                    <p class="text-sm leading-7 text-slate-600">
                                        {{ $result['abstract'] ?? 'No abstract available.' }}
                                    </p>
                                </div>

                                <div>
                                    <h4 class="mb-2 text-sm font-bold text-slate-900">Shared Terms</h4>
                                    @if(!empty($sharedTerms))
                                        <div class="flex flex-wrap gap-2">
                                            @foreach($sharedTerms as $term)
                                                <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-700">
                                                    {{ $term }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="text-sm text-slate-500">
                                            No significant shared terms identified.
                                        </p>
                                    @endif
                                </div>
                            </div>

                            {{-- Decision Support Guidance --}}
                            <div class="border-t border-slate-200 bg-slate-50/70 px-6 py-6">
                                <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-start gap-3">
                                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-100 text-indigo-600">
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                          d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622C17.176 19.29 21 14.591 21 9c0-1.017-.127-2.004-.364-2.944z"/>
                                                </svg>
                                            </div>
                                            <div>
                                                <p class="text-xs font-semibold uppercase tracking-wider text-indigo-600">
                                                    Decision Support Guidance
                                                </p>
                                                <h4 class="mt-1 text-base font-bold text-slate-900">
                                                    {{ $dss['title'] ?? 'Similarity Review' }}
                                                </h4>
                                                @if(!empty($dss['message']))
                                                    <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600">
                                                        {{ $dss['message'] }}
                                                    </p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    @if(!empty($dss['areas_to_review']))
                                        <div class="w-full lg:max-w-md">
                                            <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-slate-500">
                                                Areas to Review
                                            </p>
                                            <div class="flex flex-wrap gap-2">
                                                @foreach($dss['areas_to_review'] as $area)
                                                    <span class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-medium text-slate-700">
                                                        {{ $area }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                @if(!empty($dss['recommendation']))
                                    <div class="mt-5 rounded-xl border border-indigo-100 bg-white p-4">
                                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">
                                            Recommendation
                                        </p>
                                        <p class="mt-1 text-sm leading-6 text-slate-700">
                                            {{ $dss['recommendation'] }}
                                        </p>
                                    </div>
                                @endif

                                <div class="mt-5 flex items-start gap-2 rounded-xl border border-amber-100 bg-amber-50 p-4">
                                    <svg class="mt-0.5 h-4 w-4 shrink-0 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M13 16h-1v-4h-1m1-4h.01M12 22a10 10 0 100-20 10 10 0 000 20z"/>
                                    </svg>
                                    <p class="text-xs leading-5 text-amber-800">
                                        A similarity score is a review signal, not an automatic rejection.
                                        The final academic decision should consider the study's objectives,
                                        scope, target users, problem statement, and features.
                                    </p>
                                </div>
                            </div>

                            {{-- Result Footer --}}
                            <div class="flex flex-col gap-3 border-t border-slate-200 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
                                <p class="text-xs text-slate-500">
                                    Result ranked by hybrid similarity score.
                                </p>

                                @if(isset($result['id']))
                                    <a href="{{ route('repository.show', $result['id']) }}"
                                       class="inline-flex items-center justify-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                                        View Study
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </a>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>

        {{-- Recommended Capstone Projects --}}
        <section class="mt-10">
            <div class="mb-5">
                <div class="mb-2 flex items-center gap-2 text-sm font-semibold text-indigo-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9.663 17h4.673M10 21h4m-7.5-4.5a7 7 0 1111 0c-.7.7-1.5 1.4-1.5 2.5h-8c0-1.1-.8-1.8-1.5-2.5z"/>
                    </svg>
                    Project Idea Support
                </div>

                <h2 class="text-2xl font-bold text-slate-900">
                    Recommended Capstone Projects
                </h2>

                <p class="mt-1 max-w-3xl text-sm leading-6 text-slate-600">
                    Explore three alternative project directions based on your submitted
                    study. Review their scope and features against existing repository
                    studies before deciding whether to pursue an idea.
                </p>
            </div>

            @if(!empty($recommendations))
                <div class="grid gap-5 lg:grid-cols-3">
                    @foreach($recommendations as $index => $recommendation)
                        <article class="flex h-full flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                            <div class="border-b border-slate-200 bg-slate-50 px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-indigo-100 text-sm font-bold text-indigo-700">
                                        {{ $index + 1 }}
                                    </div>
                                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">
                                        Alternative Project Idea
                                    </p>
                                </div>

                                <h3 class="mt-3 text-lg font-bold leading-6 text-slate-900">
                                    {{ $recommendation['title'] ?? 'Untitled Recommendation' }}
                                </h3>
                            </div>

                            <div class="flex flex-1 flex-col gap-4 px-5 py-5">
                                <div>
                                    <h4 class="mb-2 text-xs font-semibold uppercase tracking-wider text-slate-500">
                                        Suggested Abstract
                                    </h4>
                                    <p class="whitespace-pre-line text-sm leading-6 text-slate-600">{{ $recommendation['abstract'] ?? 'No abstract available.' }}</p>
                                </div>

                                @if(!empty($recommendation['reason']))
                                    <div class="rounded-xl border border-indigo-100 bg-indigo-50/60 p-4">
                                        <h4 class="text-xs font-semibold uppercase tracking-wider text-indigo-700">
                                            Why consider this idea?
                                        </h4>
                                        <p class="mt-1 text-sm leading-6 text-slate-700">
                                            {{ $recommendation['reason'] }}
                                        </p>
                                    </div>
                                @endif

                                @if(!empty($recommendation['design_note']))
                                    <div>
                                        <h4 class="mb-1 text-xs font-semibold uppercase tracking-wider text-slate-500">
                                            Design Note
                                        </h4>
                                        <p class="text-sm leading-6 text-slate-600">
                                            {{ $recommendation['design_note'] }}
                                        </p>
                                    </div>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="rounded-2xl border border-slate-200 bg-white p-6 text-sm text-slate-600 shadow-sm">
                    No project recommendations are available for this analysis.
                </div>
            @endif

            <p class="mt-4 text-xs leading-5 text-slate-500">
                These are rule-based suggestions, not verified claims of originality.
                Confirm novelty, feasibility, scope, target users, and repository
                similarity with your adviser before finalizing a capstone proposal.
            </p>
        </section>

        {{-- Academic Review Reminder --}}
        <div class="mt-8 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-start gap-3">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M13 16h-1v-4h-1m1-4h.01M12 22a10 10 0 100-20 10 10 0 000 20z"/>
                    </svg>
                </div>

                <div>
                    <h3 class="text-sm font-bold text-slate-900">
                        Academic Review Reminder
                    </h3>
                    <p class="mt-1 text-sm leading-6 text-slate-600">
                        Similarity analysis is intended to support academic review.
                        A high similarity score does not by itself establish that
                        two studies are duplicates or that a proposed study should
                        be rejected.
                    </p>
                </div>
            </div>
        </div>

    </div>
</div>
</x-app-layout>