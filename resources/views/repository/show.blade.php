<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $capstone->title }} - LogiTrack Repository</title>
    @vite(['resources/css/logitrack.css', 'resources/js/logitrack.js'])
</head>

<body class="lt-body">

    @include('components.site-navigation')

    <main class="lt-container lt-animate">

        <a href="{{ $backUrl }}" class="lt-back">&larr; Back</a>

        <div class="lt-page-head">
            <div class="lt-page-head__row">
                <div>
                    <span class="lt-eyebrow">LogiTrack Repository</span>
                    <h1>{{ $capstone->title }}</h1>
                </div>
                <div class="lt-page-head__actions">
                    <span class="lt-badge lt-badge--approved">Approved</span>
                    @if(auth()->user()->role === 'admin')
                        <a href="{{ route('admin.repository.edit', ['capstoneId' => $capstone->id, 'type' => $capstone->source_type]) }}" class="lt-btn lt-btn--outline">Edit Metadata</a>
                    @endif
                </div>
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
                            <div class="lt-auto__key">Publication Date</div>
                            <div class="lt-auto__val">
                                @if(isset($capstone->source_type) && $capstone->source_type === 'old')
                                    {{ $capstone->publication_date ?? 'N/A' }}
                                @else
                                    {{ optional($capstone->publication_date)->format('F d, Y') ?? 'N/A' }}
                                @endif
                            </div>
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