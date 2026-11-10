<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $submission->title }} &middot; LogiTrack</title>
    @vite(['resources/css/logitrack.css', 'resources/js/logitrack.js'])
</head>
<body class="lt-body">
    @include('components.site-navigation')

    <main class="lt-container lt-container--narrow lt-animate">
        <a href="{{ url()->previous() }}" class="lt-back">&larr; Back to submissions</a>
        <div class="lt-page-head">
            <span class="lt-eyebrow">Submitted Capstone</span>
            <h1>{{ $submission->title }}</h1>
            <p class="lt-page-head__sub">
                Submitted by {{ $submission->submitter->first_name }} {{ $submission->submitter->last_name }}
                on {{ $submission->submitted_date->format('F d, Y') }}.
            </p>
        </div>

        <section class="lt-card">
            <h2 class="lt-card__title">Abstract</h2>
            <p>{{ $submission->abstract }}</p>
        </section>

        @php
            $isAdmin = auth()->user()->role === 'admin';
            $fileRoute = $isAdmin
                ? 'admin.student-submissions.download'
                : 'instructor.student-submissions.preview';
        @endphp
        <section class="lt-card lt-mt-lg">
            <h2 class="lt-card__title">Submitted Files</h2>
            <div class="lt-form">
                @foreach([
                    'study' => 'Full Study (PDF)',
                    'manual' => 'System Manual (PDF)',
                    'system' => 'System Files (ZIP)',
                    'mentee-form' => 'Mentee Form',
                ] as $file => $label)
                    <div class="lt-form__actions">
                        <span>{{ $label }}</span>
                        <a class="lt-btn lt-btn--primary lt-btn--sm" href="{{ route($fileRoute, [$submission, 'file' => $file]) }}" target="_blank" rel="noopener">
                            {{ $isAdmin ? 'Download' : 'Preview' }}
                        </a>
                    </div>
                @endforeach
            </div>
        </section>
    </main>
</body>
</html>
