<x-app-layout>
    <div class="min-h-screen bg-slate-50">
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">

            {{-- Page Header --}}
            <div class="mb-8">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <div class="mb-2 flex items-center gap-2 text-sm font-medium text-indigo-600">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a3 3 0 006 0M9 5a3 3 0 016 0"/>
                            </svg>
                            Research Similarity Analysis
                        </div>

                        <h1 class="text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">
                            Similarity Checker
                        </h1>

                        <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600 sm:text-base">
                            Compare a proposed capstone study with approved studies in the repository.
                            The system analyzes both textual overlap and semantic similarity to identify
                            related research.
                        </p>
                    </div>

                    <a
                        href="{{ route('repository.index') }}"
                        class="inline-flex items-center justify-center gap-2 rounded-lg border border-slate-300
                               bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm
                               transition hover:bg-slate-50"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                        View Repository
                    </a>
                </div>
            </div>

            {{-- How It Works --}}
            <div class="mb-8 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-6 py-5">
                    <h2 class="text-lg font-semibold text-slate-900">
                        How the analysis works
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Your proposed study passes through several analysis stages before results are ranked.
                    </p>
                </div>

                <div class="grid gap-0 md:grid-cols-5">
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

                    @foreach ($steps as $step)
                        <div class="relative border-b border-slate-200 p-5 last:border-b-0 md:border-b-0 md:border-r md:last:border-r-0">
                            <div class="mb-3 flex items-center gap-3">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-indigo-50 text-xs font-bold text-indigo-600">
                                    {{ $step['number'] }}
                                </span>

                                <h3 class="text-sm font-semibold text-slate-900">
                                    {{ $step['title'] }}
                                </h3>
                            </div>

                            <p class="text-xs leading-5 text-slate-500">
                                {{ $step['description'] }}
                            </p>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Main Form --}}
            <div class="grid gap-8 lg:grid-cols-3">

                {{-- Form --}}
                <div class="lg:col-span-2">
                    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">

                        <div class="border-b border-slate-200 px-6 py-5 sm:px-8">
                            <div class="flex items-start gap-4">
                                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round"
                                              stroke-linejoin="round"
                                              stroke-width="2"
                                              d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.5-8.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 7.5-7.5z"/>
                                    </svg>
                                </div>

                                <div>
                                    <h2 class="text-xl font-semibold text-slate-900">
                                        Proposed Study
                                    </h2>

                                    <p class="mt-1 text-sm text-slate-500">
                                        Enter the title and abstract you want to evaluate.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <form
                            method="POST"
                            action="{{ route('similarity.check') }}"
                            class="space-y-6 px-6 py-6 sm:px-8 sm:py-8"
                        >
                            @csrf

                            {{-- Validation Summary --}}
                            @if ($errors->any())
                                <div class="rounded-xl border border-red-200 bg-red-50 p-4">
                                    <div class="flex gap-3">
                                        <svg class="mt-0.5 h-5 w-5 shrink-0 text-red-600"
                                             fill="none"
                                             stroke="currentColor"
                                             viewBox="0 0 24 24">
                                            <path stroke-linecap="round"
                                                  stroke-linejoin="round"
                                                  stroke-width="2"
                                                  d="M12 8v4m0 4h.01M10.29 3.86l-7.82 13a2 2 0 001.71 3.14h15.64a2 2 0 001.71-3.14l-7.82-13a2 2 0 00-3.42 0z"/>
                                        </svg>

                                        <div>
                                            <h3 class="text-sm font-semibold text-red-800">
                                                Please check the following:
                                            </h3>

                                            <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-red-700">
                                                @foreach ($errors->all() as $error)
                                                    <li>{{ $error }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            {{-- Title --}}
                            <div>
                                <label
                                    for="title"
                                    class="mb-2 block text-sm font-semibold text-slate-700"
                                >
                                    Proposed Study Title
                                </label>

                                <input
                                    id="title"
                                    name="title"
                                    type="text"
                                    value="{{ old('title') }}"
                                    maxlength="255"
                                    required
                                    placeholder="e.g. Student Attendance Monitoring System"
                                    class="block w-full rounded-xl border-slate-300 bg-white px-4 py-3 text-sm
                                           text-slate-900 shadow-sm outline-none transition
                                           placeholder:text-slate-400
                                           focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20"
                                >

                                @error('title')
                                    <p class="mt-2 text-sm text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            {{-- Abstract --}}
                            <div>
                                <div class="mb-2 flex items-center justify-between gap-4">
                                    <label
                                        for="abstract"
                                        class="block text-sm font-semibold text-slate-700"
                                    >
                                        Proposed Study Abstract
                                    </label>

                                    <span class="text-xs text-slate-400">
                                        Minimum {{ config('similarity.minimum_text_length', 20) }} characters
                                    </span>
                                </div>

                                <textarea
                                    id="abstract"
                                    name="abstract"
                                    rows="12"
                                    required
                                    placeholder="Paste or write the abstract of the proposed study here..."
                                    class="block w-full resize-y rounded-xl border-slate-300 bg-white px-4 py-3 text-sm
                                           leading-6 text-slate-900 shadow-sm outline-none transition
                                           placeholder:text-slate-400
                                           focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20"
                                >{{ old('abstract') }}</textarea>

                                @error('abstract')
                                    <p class="mt-2 text-sm text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror

                                <div class="mt-2 flex items-start gap-2 text-xs leading-5 text-slate-500">
                                    <svg class="mt-0.5 h-4 w-4 shrink-0"
                                         fill="none"
                                         stroke="currentColor"
                                         viewBox="0 0 24 24">
                                        <path stroke-linecap="round"
                                              stroke-linejoin="round"
                                              stroke-width="2"
                                              d="M13 16h-1v-4h-1m1-4h.01M12 20a8 8 0 100-16 8 8 0 000 16z"/>
                                    </svg>

                                    <span>
                                        For better results, provide a complete abstract describing the
                                        problem, objectives, users, scope, and major features of the proposed system.
                                    </span>
                                </div>
                            </div>

                            {{-- Submit --}}
                            <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-6 sm:flex-row sm:items-center sm:justify-between">
                                <p class="text-xs leading-5 text-slate-500">
                                    The checker compares your proposal against studies available in the repository.
                                </p>

                                <button
                                    type="submit"
                                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600
                                           px-6 py-3 text-sm font-semibold text-white shadow-sm
                                           transition hover:bg-indigo-700
                                           focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2
                                           active:bg-indigo-800"
                                >
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round"
                                              stroke-linejoin="round"
                                              stroke-width="2"
                                              d="M9 19V6l12-3v13M9 19a3 3 0 11-6 0 3 3 0 016 0zm12-3a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>

                                    Analyze Similarity
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Information Sidebar --}}
                <div class="space-y-6">

                    {{-- What is checked --}}
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="mb-5 flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="2"
                                          d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 12c0 5.591 3.824 10.29 9 11.622C17.176 22.29 21 17.591 21 12c0-1.041-.133-2.052-.382-3.016z"/>
                                </svg>
                            </div>

                            <h2 class="text-base font-semibold text-slate-900">
                                What is analyzed?
                            </h2>
                        </div>

                        <div class="space-y-4">
                            <div class="flex gap-3">
                                <div class="mt-1 h-2 w-2 shrink-0 rounded-full bg-indigo-500"></div>

                                <div>
                                    <p class="text-sm font-medium text-slate-800">
                                        Lexical similarity
                                    </p>

                                    <p class="mt-1 text-xs leading-5 text-slate-500">
                                        Identifies important terms shared between the proposed and existing studies.
                                    </p>
                                </div>
                            </div>

                            <div class="flex gap-3">
                                <div class="mt-1 h-2 w-2 shrink-0 rounded-full bg-indigo-500"></div>

                                <div>
                                    <p class="text-sm font-medium text-slate-800">
                                        Semantic similarity
                                    </p>

                                    <p class="mt-1 text-xs leading-5 text-slate-500">
                                        Looks for similarities in meaning, even when different words are used.
                                    </p>
                                </div>
                            </div>

                            <div class="flex gap-3">
                                <div class="mt-1 h-2 w-2 shrink-0 rounded-full bg-indigo-500"></div>

                                <div>
                                    <p class="text-sm font-medium text-slate-800">
                                        Hybrid similarity
                                    </p>

                                    <p class="mt-1 text-xs leading-5 text-slate-500">
                                        Produces a combined score from the available lexical and semantic evidence.
                                    </p>
                                </div>
                            </div>

                            <div class="flex gap-3">
                                <div class="mt-1 h-2 w-2 shrink-0 rounded-full bg-indigo-500"></div>

                                <div>
                                    <p class="text-sm font-medium text-slate-800">
                                        Decision support
                                    </p>

                                    <p class="mt-1 text-xs leading-5 text-slate-500">
                                        Highlights research areas that should be reviewed rather than automatically rejecting a proposal.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Review areas --}}
                    <div class="rounded-2xl border border-indigo-100 bg-indigo-50 p-6">
                        <div class="flex items-start gap-3">
                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-indigo-600"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">
                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>

                            <div>
                                <h2 class="text-sm font-semibold text-indigo-900">
                                    Recommended review areas
                                </h2>

                                <p class="mt-2 text-xs leading-5 text-indigo-800">
                                    If related studies are found, pay particular attention to differences in:
                                </p>

                                <ul class="mt-3 space-y-2 text-xs text-indigo-800">
                                    <li class="flex gap-2">
                                        <span>•</span>
                                        <span>Research objectives</span>
                                    </li>

                                    <li class="flex gap-2">
                                        <span>•</span>
                                        <span>Target users</span>
                                    </li>

                                    <li class="flex gap-2">
                                        <span>•</span>
                                        <span>System scope</span>
                                    </li>

                                    <li class="flex gap-2">
                                        <span>•</span>
                                        <span>Major features</span>
                                    </li>

                                    <li class="flex gap-2">
                                        <span>•</span>
                                        <span>Problem statement</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    {{-- Important note --}}
                    <div class="rounded-2xl border border-amber-200 bg-amber-50 p-6">
                        <div class="flex items-start gap-3">
                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-amber-600"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">
                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M12 9v2m0 4h.01M10.29 3.86l-7.82 13a2 2 0 001.71 3.14h15.64a2 2 0 001.71-3.14l-7.82-13a2 2 0 00-3.42 0z"/>
                            </svg>

                            <div>
                                <h2 class="text-sm font-semibold text-amber-900">
                                    Important
                                </h2>

                                <p class="mt-2 text-xs leading-5 text-amber-800">
                                    A high similarity score does not automatically mean that a study should
                                    be rejected. Results are intended to support academic review and help
                                    identify areas that require closer comparison.
                                </p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</x-app-layout>